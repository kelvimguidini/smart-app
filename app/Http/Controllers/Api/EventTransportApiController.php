<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\Transports\Repositories\EventTransportRepositoryInterface;
use App\Domains\Transports\Repositories\EventTransportOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;

class EventTransportApiController extends Controller
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
     * POST /api/event-transports
     * Links a transport provider to an event.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'provider_id' => 'required|integer',
            'event_id' => 'required|integer',
            'currency' => 'required|integer',
            'taxa_4bts' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if ($request->id > 0) {
                if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_transports', $request->id)) {
                        return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'transport')) {
                        return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                    }
                }
            } else {
                if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                    $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'transport')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $providerData = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date', 'payment_method'
            ]);
            $providerData['currency_id'] = $request->currency;
            $providerData['transport_id'] = $request->provider_id;

            $provider = $request->id > 0 
                ? $this->eventTransportRepository->saveEventTransport($providerData, $request->id) 
                : $this->eventTransportRepository->saveEventTransport($providerData);

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create([
                    'status' => 'created', 
                    'user_id' => Auth::user()->id, 
                    'table' => 'event_transports', 
                    'table_id' => $provider->id
                ]);
            }

            return response()->json(['message' => 'Fornecedor de transporte vinculado com sucesso!', 'data' => $provider]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao vincular fornecedor de transporte.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-transports/{id}
     * Unlinks a transport provider from the event.
     */
    public function destroy($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator') && !Gate::allows('transport_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                $this->statusHistoryRepository->isBlockedTableRecord('event_transports', $id)) {
                return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
            }

            $r = $this->eventTransportRepository->find($id);
            if ($r) {
                $r->delete();
            }
            return response()->json(['message' => 'Fornecedor de transporte removido com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover fornecedor de transporte.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/event-transports/opts
     * Saves or updates a transport segment option.
     */
    public function storeOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_transport_id' => 'required|integer',
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
            'count' => 'nullable|numeric',
            'observation' => 'nullable|string',
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_transports', $request->event_transport_id)) {
                    return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                }

                $eventTransport = $this->eventTransportRepository->find($request->event_transport_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventTransport->event_id, $eventTransport->transport_id, 'transport')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $optData = [
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
                'order' => $request->order ?? 0,
            ];

            if ($request->id > 0) {
                $opt = $this->eventTransportOptRepository->update($request->id, $optData);
            } else {
                $opt = $this->eventTransportOptRepository->create($optData);
            }

            return response()->json(['message' => 'Opção de transporte salva com sucesso!', 'data' => $opt]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar opção.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-transports/opts/{id}
     * Deletes a transport option.
     */
    public function destroyOpt($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $opt = $this->eventTransportOptRepository->findWithDetails($id);
            if (!$opt) {
                return response()->json(['message' => 'Registro não encontrado.'], 404);
            }

            $eventTransport = $opt->event_transport;
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_transports', $eventTransport->id)) {
                    return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventTransport->event_id, $eventTransport->transport_id, 'transport')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $opt->delete();
            return response()->json(['message' => 'Opção de transporte removida com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover opção.', 'error' => $e->getMessage()], 500);
        }
    }
}
