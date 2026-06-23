<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\Halls\Repositories\EventHallRepositoryInterface;
use App\Domains\Halls\Repositories\EventHallOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;

class EventHallApiController extends Controller
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
     * POST /api/event-halls
     * Links a hall provider to an event.
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
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_halls', $request->id)) {
                        return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'hall')) {
                        return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                    }
                }
            } else {
                if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                    $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'hall')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $providerData = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date', 'payment_method'
            ]);
            $providerData['currency_id'] = $request->currency;
            $providerData['hall_id'] = $request->provider_id;

            $provider = $request->id > 0 
                ? $this->eventHallRepository->update($request->id, $providerData) 
                : $this->eventHallRepository->create($providerData);

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create([
                    'status' => 'created', 
                    'user_id' => Auth::user()->id, 
                    'table' => 'event_halls', 
                    'table_id' => $provider->id
                ]);
            }

            return response()->json(['message' => 'Salão vinculado com sucesso!', 'data' => $provider]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao vincular salão.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-halls/{id}
     * Unlinks a hall provider from the event.
     */
    public function destroy($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                $this->statusHistoryRepository->isBlockedTableRecord('event_halls', $id)) {
                return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
            }

            $r = $this->eventHallRepository->find($id);
            if ($r) {
                $r->delete();
            }
            return response()->json(['message' => 'Salão removido com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover salão.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/event-halls/opts
     * Saves or updates a hall option.
     */
    public function storeOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_hall_id' => 'required|integer',
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
                    return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                }

                $eventHall = $this->eventHallRepository->find($request->event_hall_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventHall->event_id, $eventHall->hall_id, 'hall')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $optData = [
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
                'order' => $request->order ?? 0,
            ];

            if ($request->id > 0) {
                $opt = $this->eventHallOptRepository->update($request->id, $optData);
            } else {
                $opt = $this->eventHallOptRepository->create($optData);
            }

            return response()->json(['message' => 'Opção de salão salva com sucesso!', 'data' => $opt]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar opção.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-halls/opts/{id}
     * Deletes a hall option.
     */
    public function destroyOpt($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $opt = $this->eventHallOptRepository->findWithDetails($id);
            if (!$opt) {
                return response()->json(['message' => 'Registro não encontrado.'], 404);
            }

            $eventHall = $opt->event_hall;
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2')) {
                if ($this->statusHistoryRepository->isBlockedTableRecord('event_halls', $eventHall->id)) {
                    return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventHall->event_id, $eventHall->hall_id, 'hall')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $opt->delete();
            return response()->json(['message' => 'Opção de salão removida com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover opção.', 'error' => $e->getMessage()], 500);
        }
    }
}
