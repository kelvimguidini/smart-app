<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Domains\FoodBeverage\Repositories\EventABRepositoryInterface;
use App\Domains\FoodBeverage\Repositories\EventABOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;

class ABController extends Controller
{
    protected $eventABRepository;
    protected $eventABOptRepository;
    protected $userRepository;
    protected $statusHistoryRepository;

    public function __construct(
        EventABRepositoryInterface $eventABRepository,
        EventABOptRepositoryInterface $eventABOptRepository,
        UserRepositoryInterface $userRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository
    ) {
        $this->eventABRepository = $eventABRepository;
        $this->eventABOptRepository = $eventABOptRepository;
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
            'local_id' => 'required|integer',
            'service_id' => 'required|integer',
            'service_type_id' => 'required|integer',
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
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_abs', $request->event_ab_id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                }

                $eventAB = $this->eventABRepository->find($request->event_ab_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAB->event_id, $eventAB->ab_id, 'ab')) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            $data = [
                'event_ab_id' => $request->event_ab_id,
                'broker_id' => $request->broker,
                'local_id' => $request->local_id,
                'service_id' => $request->service_id,
                'service_type_id' => $request->service_type_id,
                'in' => $request->in,
                'out' => $request->out,
                'received_proposal_percent' => $request->received_proposal_percent,
                'received_proposal' => $request->received_proposal,
                'kickback' => $request->kickback,
                'count' => $request->count,
                'order' => $request->order,
            ];

            if ($request->id > 0) {
                $this->eventABOptRepository->update($request->id, $data);
            } else {
                $this->eventABOptRepository->create($data);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Delete event AB.
     */
    public function eventABDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }
        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && $this->statusHistoryRepository->isBlockedTableRecord('event_abs', $request->id)) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
            }
            $r = $this->eventABRepository->find($request->id);
            $r->delete();
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 2])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
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
            $opt = $this->eventABOptRepository->findWithDetails($request->id);
            $eventAB = $opt->event_ab;

            if (!$eventAB) {
                return redirect()->back()->with('flash', ['message' => 'Erro: Registro não associado a um fornecedor válido!', 'type' => 'danger']);
            }

            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_abs', $eventAB->id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAB->event_id, $eventAB->ab_id, 'ab')) {
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
