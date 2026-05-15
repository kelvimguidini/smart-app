<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Domains\Halls\Repositories\EventHallRepositoryInterface;
use App\Domains\Halls\Repositories\EventHallOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;

class HallController extends Controller
{
    protected $eventHallRepository;
    protected $eventHallOptRepository;
    protected $userRepository;
    protected $statusHistoryRepository;

    public function __construct(
        EventHallRepositoryInterface $eventHallRepository,
        EventHallOptRepositoryInterface $eventHallOptRepository,
        UserRepositoryInterface $userRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository
    ) {
        $this->eventHallRepository = $eventHallRepository;
        $this->eventHallOptRepository = $eventHallOptRepository;
        $this->userRepository = $userRepository;
        $this->statusHistoryRepository = $statusHistoryRepository;
    }

    /**
     * Handle an incoming registration request.
     */
    public function storeOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }

        $request->validate([
            'broker' => 'required|integer',
            'service_id' => 'required|integer',
            'purpose_id' => 'required|integer',
            'in' => 'required|date',
            'out' => 'required|date|after_or_equal:in',
            'received_proposal' => 'nullable|numeric',
            'received_proposal_percent' => 'nullable|numeric',
            'kickback' => 'nullable|numeric',
            'count' => 'nullable|numeric',
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_halls', $request->event_hall_id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                }

                $eventHall = $this->eventHallRepository->find($request->event_hall_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventHall->event_id, $eventHall->hall_id, 'hall')) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            $data = [
                'event_hall_id' => $request->event_hall_id,
                'broker_id' => $request->broker,
                'service_id' => $request->service_id,
                'purpose_id' => $request->purpose_id,
                'in' => $request->in,
                'out' => $request->out,
                'received_proposal_percent' => $request->received_proposal_percent,
                'received_proposal' => $request->received_proposal,
                'kickback' => $request->kickback,
                'count' => $request->count,
                'order' => $request->order,
            ];

            if ($request->id > 0) {
                $this->eventHallOptRepository->update($request->id, $data);
            } else {
                $this->eventHallOptRepository->create($data);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Delete event hall.
     */
    public function eventHallDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }
        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && $this->statusHistoryRepository->isBlockedTableRecord('event_halls', $request->id)) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
            }
            $r = $this->eventHallRepository->find($request->id);
            $r->delete();
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 3])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    /**
     * Delete opt.
     */
    public function optDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }
        try {
            $opt = $this->eventHallOptRepository->findWithDetails($request->id);
            $eventHall = $opt->event_hall;

            if (!$eventHall) {
                return redirect()->back()->with('flash', ['message' => 'Erro: Registro não associado a um fornecedor válido!', 'type' => 'danger']);
            }

            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_halls', $eventHall->id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventHall->event_id, $eventHall->hall_id, 'hall')) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            $opt->delete();
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
