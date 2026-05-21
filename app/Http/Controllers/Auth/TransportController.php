<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Domains\Transports\Repositories\EventTransportRepositoryInterface;
use App\Domains\Transports\Repositories\EventTransportOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;

class TransportController extends Controller
{
    protected $eventTransportRepository;
    protected $eventTransportOptRepository;
    protected $userRepository;
    protected $statusHistoryRepository;

    public function __construct(
        EventTransportRepositoryInterface $eventTransportRepository,
        EventTransportOptRepositoryInterface $eventTransportOptRepository,
        UserRepositoryInterface $userRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository
    ) {
        $this->eventTransportRepository = $eventTransportRepository;
        $this->eventTransportOptRepository = $eventTransportOptRepository;
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
            'vehicle' => 'required|integer',
            'model' => 'required|integer',
            'service' => 'required|integer',
            'brand' => 'required|integer',
            'in' => 'required|date',
            'out' => 'required|date|after_or_equal:in',
            'received_proposal' => 'nullable|numeric',
            'received_proposal_percent' => 'nullable|numeric',
            'kickback' => 'nullable|numeric',
            'count' => 'nullable|numeric'
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_transports', $request->event_transport_id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser atualizado devido ao status atual!', 'type' => 'danger']);
                }

                $eventTransport = $this->eventTransportRepository->find($request->event_transport_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventTransport->event_id, $eventTransport->transport_id, 'transport')) {
                    return redirect()->back()->with('flash', ['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!', 'type' => 'danger']);
                }
            }

            $data = [
                'event_transport_id' => $request->event_transport_id,
                'broker_id' => $request->broker,
                'vehicle_id' => $request->vehicle,
                'model_id' => $request->model,
                'service_id' => $request->service,
                'brand_id' => $request->brand,
                'observation' => $request->observation,
                'in' => $request->in,
                'out' => $request->out,
                'received_proposal_percent' => $request->received_proposal_percent,
                'received_proposal' => $request->received_proposal,
                'kickback' => $request->kickback,
                'count' => $request->count,
                'order' => $request->order,
            ];

            if ($request->id > 0) {
                $this->eventTransportOptRepository->update($request->id, $data);
            } else {
                $this->eventTransportOptRepository->create($data);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->back()->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    /**
     * Delete event transport.
     */
    public function eventTransportDelete(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('transport_operator')) {
            abort(403);
        }
        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && $this->statusHistoryRepository->isBlockedTableRecord('event_transports', $request->id)) {
                return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
            }
            $r = $this->eventTransportRepository->find($request->id);
            $r->delete();
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('event-edit',  ['id' => $request->event_id, 'tab' => 5])->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
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
            $opt = $this->eventTransportOptRepository->findWithDetails($request->id);
            $eventTransport = $opt->event_transport;

            if (!$eventTransport) {
                return redirect()->back()->with('flash', ['message' => 'Erro: Registro não associado a um fornecedor válido!', 'type' => 'danger']);
            }

            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_transports', $eventTransport->id)) {
                    return redirect()->back()->with('flash', ['message' => 'Esse registro não pode ser apagado devido ao status atual!', 'type' => 'danger']);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventTransport->event_id, $eventTransport->transport_id, 'transport')) {
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
