<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventHotelOpt;
use App\Models\StatusHistory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\Hotels\Repositories\EventHotelRepositoryInterface;
use App\Domains\Hotels\Repositories\EventHotelOptRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;

class EventHotelApiController extends Controller
{
    protected $eventHotelRepository;
    protected $eventHotelOptRepository;
    protected $statusHistoryRepository;
    protected $userRepository;

    public function __construct(
        EventHotelRepositoryInterface $eventHotelRepository,
        EventHotelOptRepositoryInterface $eventHotelOptRepository,
        StatusHistoryRepositoryInterface $statusHistoryRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->eventHotelRepository = $eventHotelRepository;
        $this->eventHotelOptRepository = $eventHotelOptRepository;
        $this->statusHistoryRepository = $statusHistoryRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * POST /api/event-hotels
     * Links a hotel provider to an event.
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

        $currency = \App\Models\Currency::find($request->currency);
        if ($currency && $currency->sigla !== 'BRL') {
            $request->validate([
                'iof' => 'required|numeric|gt:0',
            ], [
                'iof.required' => 'O IOF é obrigatório para moedas estrangeiras.',
                'iof.gt' => 'O IOF não pode ser zero ou menor para moedas estrangeiras.',
            ]);
        }

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if ($request->id > 0) {
                if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_hotels', $request->id)) {
                        return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'hotel')) {
                        return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                    }
                }
            } else {
                if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                    $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'hotel')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $providerData = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date', 'payment_method',
                'checkin_time', 'checkin_time_end', 'checkout_time', 'checkout_time_end'
            ]);
            $providerData['currency_id'] = $request->currency;
            $providerData['hotel_id'] = $request->provider_id;

            $provider = $request->id > 0 
                ? $this->eventHotelRepository->update($request->id, $providerData) 
                : $this->eventHotelRepository->create($providerData);

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create([
                    'status' => 'created', 
                    'user_id' => Auth::user()->id, 
                    'table' => 'event_hotels', 
                    'table_id' => $provider->id
                ]);
            }

            return response()->json(['message' => 'Hotel vinculado com sucesso!', 'data' => $provider]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao vincular hotel.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-hotels/{id}
     * Unlinks a hotel from the event.
     */
    public function destroy($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                $this->statusHistoryRepository->isBlockedTableRecord('event_hotels', $id)) {
                return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
            }

            $r = $this->eventHotelRepository->find($id);
            if ($r) {
                $r->delete();
            }
            return response()->json(['message' => 'Hotel removido com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover hotel.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/event-hotels/opts
     * Saves or updates a hotel option details.
     */
    public function storeOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_hotel_id' => 'required|integer',
            'broker' => 'required|integer',
            'regime' => 'required|integer',
            'purpose' => 'required|integer',
            'in' => 'required|date',
            'out' => 'required|date|after_or_equal:in',
            'received_proposal' => 'nullable|numeric',
            'received_proposal_percent' => 'nullable|numeric',
            'kickback' => 'nullable|numeric',
            'count' => 'nullable|numeric',
            'compare_trivago' => 'nullable|numeric',
            'compare_website_htl' => 'nullable|numeric',
            'compare_omnibess' => 'nullable|numeric',
        ]);

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            $hasLevel2Permission = $user->getPermissions()->contains('name', 'status_level_2');
            $hasLevel1Permission = $user->getPermissions()->contains('name', 'status_level_1');

            if (!$hasLevel2Permission) {
                $currentStatus = $this->statusHistoryRepository->latestStatusForTable('event_hotels', $request->event_hotel_id);
                if ($currentStatus && !StatusHistory::canUserEditStatus($currentStatus, $hasLevel2Permission, $hasLevel1Permission)) {
                    return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                }

                $eventHotel = $this->eventHotelRepository->find($request->event_hotel_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventHotel->event_id, $eventHotel->hotel_id, 'hotel')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $optData = [
                'event_hotel_id' => $request->event_hotel_id,
                'broker_id' => $request->broker,
                'regime_id' => $request->regime,
                'purpose_id' => $request->purpose,
                'category_hotel_id' => $request->category_id,
                'apto_hotel_id' => $request->apto_id,
                'in' => $request->in,
                'out' => $request->out,
                'received_proposal_percent' => $request->received_proposal_percent,
                'received_proposal' => $request->received_proposal,
                'kickback' => $request->kickback,
                'compare_trivago' => $request->compare_trivago,
                'compare_website_htl' => $request->compare_website_htl,
                'compare_omnibess' => $request->compare_omnibess,
                'count' => $request->count,
                'order' => $request->order ?? 0,
            ];

            if ($request->id > 0) {
                $opt = EventHotelOpt::findOrFail($request->id);
                $opt->update($optData);
            } else {
                $opt = EventHotelOpt::create($optData);
            }

            return response()->json(['message' => 'Opção de hotel salva com sucesso!', 'data' => $opt]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar opção.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-hotels/opts/{id}
     * Deletes a hotel option.
     */
    public function destroyOpt($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $opt = $this->eventHotelOptRepository->findWithHotel($id);
            if (!$opt) {
                return response()->json(['message' => 'Registro não encontrado.'], 404);
            }

            $eventHotel = $opt->event_hotel;
            $user = $this->userRepository->find(Auth::user()->id);
            $hasLevel2Permission = $user->getPermissions()->contains('name', 'status_level_2');
            $hasLevel1Permission = $user->getPermissions()->contains('name', 'status_level_1');

            if (!$hasLevel2Permission) {
                $currentStatus = $this->statusHistoryRepository->latestStatusForTable('event_hotels', $eventHotel->id);
                if ($currentStatus && !StatusHistory::canUserEditStatus($currentStatus, $hasLevel2Permission, $hasLevel1Permission)) {
                    return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventHotel->event_id, $eventHotel->hotel_id, 'hotel')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $opt->delete();
            return response()->json(['message' => 'Opção removida com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover opção.', 'error' => $e->getMessage()], 500);
        }
    }
}
