<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\Airfares\Repositories\EventAirfareRepositoryInterface;
use App\Domains\Airfares\Repositories\EventAirfareOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;

class EventAirfareApiController extends Controller
{
    protected $eventAirfareRepository;
    protected $eventAirfareOptRepository;
    protected $userRepository;
    protected $statusHistoryRepository;

    public function __construct(
        EventAirfareRepositoryInterface $eventAirfareRepository,
        EventAirfareOptRepositoryInterface $eventAirfareOptRepository,
        UserRepositoryInterface $userRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository
    ) {
        $this->eventAirfareRepository = $eventAirfareRepository;
        $this->eventAirfareOptRepository = $eventAirfareOptRepository;
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
            'event_id' => 'required|integer',
            'currency' => 'required|integer',
            'taxa_4bts' => 'required|numeric|min:0|max:100',
        ]);

        $airlineId = $request->airline_id ?: $request->provider_id;
        if (!$airlineId) {
            return response()->json(['message' => 'Por favor, selecione a Companhia Aérea.'], 422);
        }

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if ($request->id > 0) {
                if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_airfares', $request->id)) {
                        return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $airlineId, 'airfare')) {
                        return response()->json(['message' => 'Essa companhia aérea já possui um registro bloqueado neste evento!'], 422);
                    }
                }
            } else {
                if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                    $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $airlineId, 'airfare')) {
                    return response()->json(['message' => 'Essa companhia aérea já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $providerData = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date', 'payment_method',
                'equipment',
                'pax_first', 'pax_executiva', 'pax_premium', 'pax_economica', 'total_pax',
                'prazo_cia',
                'inc_taxa_embarque', 'inc_servico_bordo', 'inc_porao', 'inc_bagagem_bordo',
                'inc_sala_vip', 'inc_fbo_origem', 'inc_fbo_destino', 'inc_alteracao_nomes',
                'taxa_embarque_unit', 'total_net_sem_4bts', 'markup',
                'observations', 'notes'
            ]);
            $providerData['currency_id'] = $request->currency;
            if (\Illuminate\Support\Facades\Schema::hasColumn('event_airfare', 'airfare_id')) {
                $providerData['airfare_id'] = $request->provider_id ?: null;
            }
            $providerData['airline_id'] = $request->airline_id ?: $request->provider_id;
            if (isset($providerData['markup']) && $providerData['markup'] !== '' && $providerData['markup'] !== null) {
                $providerData['markup'] = round((float)$providerData['markup'], 2);
            }

            foreach (['photo_1', 'photo_2', 'photo_3', 'photo_4'] as $photoKey) {
                if ($request->hasFile($photoKey)) {
                    $path = \Illuminate\Support\Facades\Storage::putFile('public/airfares', $request->file($photoKey));
                    $providerData[$photoKey] = \Illuminate\Support\Facades\Storage::url($path);
                    try {
                        \Illuminate\Support\Facades\Artisan::call('files:copy');
                    } catch (\Throwable $e) {
                        // Ignora se falhar
                    }
                } elseif ($request->has($photoKey)) {
                    $existingVal = $request->input($photoKey);
                    if ($existingVal && preg_match('/^data:image\/(\w+);base64,/', $existingVal, $matches)) {
                        $imageType = strtolower($matches[1]);
                        $imageData = substr($existingVal, strpos($existingVal, ',') + 1);
                        $imageData = base64_decode($imageData);
                        if ($imageData !== false) {
                            $fileName = 'airfares/' . uniqid('photo_') . '.' . ($imageType === 'jpeg' ? 'jpg' : $imageType);
                            \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageData);
                            $providerData[$photoKey] = \Illuminate\Support\Facades\Storage::disk('public')->url($fileName);
                            try {
                                \Illuminate\Support\Facades\Artisan::call('files:copy');
                            } catch (\Throwable $e) {
                                // Ignora se falhar
                            }
                        }
                    } elseif ($existingVal && (str_starts_with($existingVal, '/storage/') || str_starts_with($existingVal, 'http'))) {
                        $providerData[$photoKey] = $existingVal;
                    } elseif ($existingVal === '' || $existingVal === null || $existingVal === 'null') {
                        $providerData[$photoKey] = null;
                    }
                }
            }

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
            'currency' => 'nullable|integer',
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
                $airlineId = $eventAirfare ? ($eventAirfare->airline_id ?: ($eventAirfare->airfare_id ?? null)) : null;
                if ($eventAirfare && $airlineId && $this->statusHistoryRepository->isProviderBlockedInEvent($eventAirfare->event_id, $airlineId, 'airfare')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            } else {
                $eventAirfare = $this->eventAirfareRepository->find($request->event_airfare_id);
            }

            $optData = [
                'event_airfare_id' => $request->event_airfare_id,
                'outbound_airline_id' => $request->outbound_airline_id ?: ($eventAirfare?->airline_id ?? null),
                'outbound_flight_number' => $request->outbound_flight_number,
                'outbound_origin' => $request->outbound_origin,
                'outbound_destination' => $request->outbound_destination,
                'outbound_date' => $request->outbound_date,
                'outbound_departure_time' => $request->outbound_departure_time,
                'outbound_arrival_time' => $request->outbound_arrival_time,
            ];

            if ($request->id > 0) {
                $opt = $this->eventAirfareOptRepository->update($request->id, $optData);
            } else {
                $opt = $this->eventAirfareOptRepository->create($optData);
            }

            return response()->json(['message' => 'Trecho de voo salvo com sucesso!', 'data' => $opt]);
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

                $airlineId = $eventAirfare ? ($eventAirfare->airline_id ?: ($eventAirfare->airfare_id ?? null)) : null;
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAirfare->event_id, $airlineId, 'airfare')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $opt->delete();
            return response()->json(['message' => 'Opção de voo removida com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover opção.', 'error' => $e->getMessage()], 500);
        }
    }
}
