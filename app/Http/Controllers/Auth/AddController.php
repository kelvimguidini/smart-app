<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Domains\Additions\Repositories\EventAddRepositoryInterface;
use App\Domains\Additions\Repositories\EventAddOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;

class AddController extends Controller
{
    protected $eventAddRepository;
    protected $eventAddOptRepository;
    protected $userRepository;
    protected $statusHistoryRepository;

    public function __construct(
        EventAddRepositoryInterface $eventAddRepository,
        EventAddOptRepositoryInterface $eventAddOptRepository,
        UserRepositoryInterface $userRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository
    ) {
        $this->eventAddRepository = $eventAddRepository;
        $this->eventAddOptRepository = $eventAddOptRepository;
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
            'frequency' => 'required|integer',
            'measure' => 'required|integer',
            'service' => 'required|integer',
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
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_adds', $request->event_add_id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                }

                $eventAdd = $this->eventAddRepository->find($request->event_add_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAdd->event_id, $eventAdd->add_id, 'add')) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            $data = [
                'event_add_id' => $request->event_add_id,
                'frequency_id' => $request->frequency,
                'measure_id' => $request->measure,
                'service_id' => $request->service,
                'unit' => $request->unit,
                'pax' => $request->pax,
                'in' => $request->in,
                'out' => $request->out,
                'received_proposal_percent' => $request->received_proposal_percent,
                'received_proposal' => $request->received_proposal,
                'kickback' => $request->kickback,
                'count' => $request->count,
                'order' => $request->order,
            ];

            if ($request->id > 0) {
                $this->eventAddOptRepository->update($request->id, $data);
            } else {
                $this->eventAddOptRepository->create($data);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Delete event add.
     */
    public function eventAddDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            abort(403);
        }
        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && $this->statusHistoryRepository->isBlockedTableRecord('event_adds', $request->id)) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
            }
            $r = $this->eventAddRepository->find($request->id);
            $r->delete();
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 4])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
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
            $opt = $this->eventAddOptRepository->findWithDetails($request->id);
            $eventAdd = $opt->event_add;

            if (!$eventAdd) {
                return redirect()->back()->with('flash', ['message' => 'Erro: Registro não associado a um fornecedor válido!', 'type' => 'danger']);
            }

            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_adds', $eventAdd->id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAdd->event_id, $eventAdd->add_id, 'add')) {
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
