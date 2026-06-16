<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\Airfares\Repositories\EventAirfareRepositoryInterface;
use App\Domains\Airfares\Repositories\EventAirfareOptRepositoryInterface;
use App\Domains\Airfares\Repositories\EventAirfarePassengerRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;

class EventAirfareApiController extends Controller
{
    protected $eventAirfareRepository;
    protected $eventAirfareOptRepository;
    protected $eventAirfarePassengerRepository;
    protected $userRepository;
    protected $statusHistoryRepository;

    public function __construct(
        EventAirfareRepositoryInterface $eventAirfareRepository,
        EventAirfareOptRepositoryInterface $eventAirfareOptRepository,
        EventAirfarePassengerRepositoryInterface $eventAirfarePassengerRepository,
        UserRepositoryInterface $userRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository
    ) {
        $this->eventAirfareRepository = $eventAirfareRepository;
        $this->eventAirfareOptRepository = $eventAirfareOptRepository;
        $this->eventAirfarePassengerRepository = $eventAirfarePassengerRepository;
        $this->userRepository = $userRepository;
        $this->statusHistoryRepository = $statusHistoryRepository;
    }

    /**
     * POST /api/event-airfares
     * Links a flight provider to an event.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('air_operator')) {
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
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_airfares', $request->id)) {
                        return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'airfare')) {
                        return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                    }
                }
            } else {
                if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                    $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'airfare')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $providerData = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date', 'payment_method'
            ]);
            $providerData['currency_id'] = $request->currency;
            $providerData['airfare_id'] = $request->provider_id;

            $provider = $request->id > 0 
                ? $this->eventAirfareRepository->saveEventAirfare($providerData, $request->id) 
                : $this->eventAirfareRepository->saveEventAirfare($providerData);

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create([
                    'status' => 'created', 
                    'user_id' => Auth::user()->id, 
                    'table' => 'event_airfares', 
                    'table_id' => $provider->id
                ]);
            }

            return response()->json(['message' => 'Fornecedor de aéreo vinculado com sucesso!', 'data' => $provider]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao vincular fornecedor de aéreo.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-airfares/{id}
     * Unlinks a flight provider from the event.
     */
    public function destroy($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('air_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                $this->statusHistoryRepository->isBlockedTableRecord('event_airfares', $id)) {
                return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
            }

            $r = $this->eventAirfareRepository->find($id);
            if ($r) {
                $r->delete();
            }
            return response()->json(['message' => 'Fornecedor de aéreo removido com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover fornecedor de aéreo.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/event-airfares/opts
     * Saves or updates a flight option quote segment.
     */
    public function storeOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('air_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_airfare_id' => 'required|integer',
            'outbound_airline_id' => 'nullable|integer',
            'outbound_flight_number' => 'nullable|string|max:255',
            'outbound_class' => 'nullable|string|max:255',
            'outbound_date' => 'nullable|date',
            'outbound_origin' => 'nullable|string|max:255',
            'outbound_destination' => 'nullable|string|max:255',
            'outbound_departure_time' => 'nullable|string|max:255',
            'outbound_arrival_time' => 'nullable|string|max:255',
            'outbound_connection_details' => 'nullable|string|max:255',
            'inbound_airline_id' => 'nullable|integer',
            'inbound_flight_number' => 'nullable|string|max:255',
            'inbound_class' => 'nullable|string|max:255',
            'inbound_date' => 'nullable|date',
            'inbound_origin' => 'nullable|string|max:255',
            'inbound_destination' => 'nullable|string|max:255',
            'inbound_departure_time' => 'nullable|string|max:255',
            'inbound_arrival_time' => 'nullable|string|max:255',
            'inbound_connection_details' => 'nullable|string|max:255',
            'currency' => 'required|integer',
            'received_proposal' => 'nullable|numeric',
            'received_proposal_percent' => 'nullable|numeric',
            'kickback' => 'nullable|numeric',
            'compare_website' => 'nullable|numeric',
            'compare_client' => 'nullable|numeric',
            'count' => 'nullable|numeric',
            'baggage' => 'nullable|integer',
            'cabin' => 'nullable|integer',
            'status' => 'nullable|string|max:255',
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_airfares', $request->event_airfare_id)) {
                    return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                }

                $eventAirfare = $this->eventAirfareRepository->find($request->event_airfare_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAirfare->event_id, $eventAirfare->airfare_id, 'airfare')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $optData = [
                'event_airfare_id' => $request->event_airfare_id,
                'outbound_airline_id' => $request->outbound_airline_id,
                'outbound_flight_number' => $request->outbound_flight_number,
                'outbound_class' => $request->outbound_class,
                'outbound_date' => $request->outbound_date,
                'outbound_origin' => $request->outbound_origin,
                'outbound_destination' => $request->outbound_destination,
                'outbound_departure_time' => $request->outbound_departure_time,
                'outbound_arrival_time' => $request->outbound_arrival_time,
                'outbound_connection_details' => $request->outbound_connection_details,
                'inbound_airline_id' => $request->inbound_airline_id,
                'inbound_flight_number' => $request->inbound_flight_number,
                'inbound_class' => $request->inbound_class,
                'inbound_date' => $request->inbound_date,
                'inbound_origin' => $request->inbound_origin,
                'inbound_destination' => $request->inbound_destination,
                'inbound_departure_time' => $request->inbound_departure_time,
                'inbound_arrival_time' => $request->inbound_arrival_time,
                'inbound_connection_details' => $request->inbound_connection_details,
                'currency_id' => $request->currency,
                'received_proposal_percent' => $request->received_proposal_percent,
                'received_proposal' => $request->received_proposal,
                'kickback' => $request->kickback,
                'compare_website' => $request->compare_website,
                'compare_client' => $request->compare_client,
                'count' => $request->count,
                'baggage_id' => $request->baggage,
                'cabin_id' => $request->cabin,
                'status' => $request->status,
                'observation' => $request->observation,
                'order' => $request->order ?? 0,
            ];

            if ($request->id > 0) {
                $opt = $this->eventAirfareOptRepository->update($request->id, $optData);
            } else {
                $opt = $this->eventAirfareOptRepository->create($optData);
            }

            return response()->json(['message' => 'Opção de voo salva com sucesso!', 'data' => $opt]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar opção.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-airfares/opts/{id}
     * Deletes a flight option.
     */
    public function destroyOpt($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('air_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $opt = $this->eventAirfareOptRepository->findWithDetails($id);
            if (!$opt) {
                return response()->json(['message' => 'Registro não encontrado.'], 404);
            }

            $eventAirfare = $opt->event_airfare;
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_airfares', $eventAirfare->id)) {
                    return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAirfare->event_id, $eventAirfare->airfare_id, 'airfare')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $opt->delete();
            return response()->json(['message' => 'Opção de voo removida com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover opção.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/event-airfares/passengers
     * Saves or updates a flight passenger (Ficha de Voo).
     */
    public function storePassenger(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('air_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_airfare_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'document' => 'nullable|string|max:255',
            'passport_validity' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'outbound_date' => 'nullable|date',
            'outbound_origin' => 'nullable|string|max:255',
            'outbound_destination' => 'nullable|string|max:255',
            'outbound_departure' => 'nullable|string|max:255',
            'outbound_arrival' => 'nullable|string|max:255',
            'inbound_date' => 'nullable|date',
            'inbound_origin' => 'nullable|string|max:255',
            'inbound_destination' => 'nullable|string|max:255',
            'inbound_departure' => 'nullable|string|max:255',
            'inbound_arrival' => 'nullable|string|max:255',
        ]);

        try {
            $data = $request->only([
                'event_airfare_id', 'name', 'document', 'passport_validity', 'birth_date',
                'outbound_date', 'outbound_origin', 'outbound_destination', 'outbound_departure', 'outbound_arrival',
                'inbound_date', 'inbound_origin', 'inbound_destination', 'inbound_departure', 'inbound_arrival'
            ]);

            if ($request->id > 0) {
                $passenger = $this->eventAirfarePassengerRepository->update($request->id, $data);
            } else {
                $passenger = $this->eventAirfarePassengerRepository->create($data);
            }

            return response()->json(['message' => 'Passageiro salvo com sucesso na ficha de voo!', 'data' => $passenger]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar passageiro.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-airfares/passengers/{id}
     * Deletes a flight passenger from the list.
     */
    public function destroyPassenger($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('air_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->eventAirfarePassengerRepository->delete($id);
            return response()->json(['message' => 'Passageiro removido com sucesso da ficha de voo!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover passageiro.', 'error' => $e->getMessage()], 500);
        }
    }
}
