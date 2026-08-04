<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\Additions\Repositories\EventAddRepositoryInterface;
use App\Domains\Additions\Repositories\EventAddOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Models\StatusHistory;

class EventAddApiController extends Controller
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
     * POST /api/event-adds
     * Links an Additional Service provider (ProviderService) to an event.
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
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_adds', $request->id)) {
                        return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'add')) {
                        return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                    }
                }
            } else {
                if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                    $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'add')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $providerData = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date', 'payment_method'
            ]);
            $providerData['currency_id'] = $request->currency;
            $providerData['add_id'] = $request->provider_id;

            $provider = $request->id > 0 
                ? $this->eventAddRepository->saveEventAdd($providerData, $request->id) 
                : $this->eventAddRepository->saveEventAdd($providerData);

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create([
                    'status' => 'created', 
                    'user_id' => Auth::user()->id, 
                    'table' => 'event_adds', 
                    'table_id' => $provider->id
                ]);
            }

            return response()->json(['message' => 'Serviço Adicional vinculado com sucesso!', 'data' => $provider]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao vincular serviço adicional.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-adds/{id}
     * Unlinks an Additional Service provider from the event.
     */
    public function destroy($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                $this->statusHistoryRepository->isBlockedTableRecord('event_adds', $id)) {
                return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
            }

            $r = $this->eventAddRepository->find($id);
            if ($r) {
                $r->delete();
            }
            return response()->json(['message' => 'Serviço Adicional removido com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover serviço adicional.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/event-adds/opts
     * Saves or updates an additional service option.
     */
    public function storeOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_add_id' => 'required|integer',
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
            $hasLevel2Permission = $user->getPermissions()->contains('name', 'status_level_2');
            $hasLevel1Permission = $user->getPermissions()->contains('name', 'status_level_1');

            if (!$hasLevel2Permission) {
                $currentStatus = $this->statusHistoryRepository->latestStatusForTable('event_adds', $request->event_add_id);
                if ($currentStatus && !StatusHistory::canUserEditStatus($currentStatus, $hasLevel2Permission, $hasLevel1Permission)) {
                    return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                }

                $eventAdd = $this->eventAddRepository->find($request->event_add_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAdd->event_id, $eventAdd->add_id, 'add')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $optData = [
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
                'order' => $request->order ?? 0,
            ];

            if ($request->id > 0) {
                $opt = $this->eventAddOptRepository->update($request->id, $optData);
            } else {
                $opt = $this->eventAddOptRepository->create($optData);
            }

            return response()->json(['message' => 'Opção de serviço adicional salva com sucesso!', 'data' => $opt]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar opção.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-adds/opts/{id}
     * Deletes an additional service option.
     */
    public function destroyOpt($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $opt = $this->eventAddOptRepository->findWithDetails($id);
            if (!$opt) {
                return response()->json(['message' => 'Registro não encontrado.'], 404);
            }

            $eventAdd = $opt->event_add;
            $user = $this->userRepository->find(Auth::user()->id);
            $hasLevel2Permission = $user->getPermissions()->contains('name', 'status_level_2');
            $hasLevel1Permission = $user->getPermissions()->contains('name', 'status_level_1');

            if (!$hasLevel2Permission) {
                $currentStatus = $this->statusHistoryRepository->latestStatusForTable('event_adds', $eventAdd->id);
                if ($currentStatus && !StatusHistory::canUserEditStatus($currentStatus, $hasLevel2Permission, $hasLevel1Permission)) {
                    return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAdd->event_id, $eventAdd->add_id, 'add')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $opt->delete();
            return response()->json(['message' => 'Opção de serviço adicional removida com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover opção.', 'error' => $e->getMessage()], 500);
        }
    }
}
