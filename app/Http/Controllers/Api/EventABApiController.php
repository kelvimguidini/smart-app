<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\FoodBeverage\Repositories\EventABRepositoryInterface;
use App\Domains\FoodBeverage\Repositories\EventABOptRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use App\Domains\Shared\Repositories\StatusHistoryRepositoryInterface;
use App\Models\StatusHistory;

class EventABApiController extends Controller
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
     * POST /api/event-abs
     * Links an A&B provider to an event.
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
                    if ($this->statusHistoryRepository->isBlockedTableRecord('event_abs', $request->id)) {
                        return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                    }
                    if ($this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'ab')) {
                        return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                    }
                }
            } else {
                if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                    $this->statusHistoryRepository->isProviderBlockedInEvent($request->event_id, $request->provider_id, 'ab')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $providerData = $request->only([
                'event_id', 'iss_percent', 'service_percent', 'iva_percent', 
                'invoice', 'internal_observation', 'customer_observation', 
                'iof', 'taxa_4bts', 'service_charge', 'deadline_date', 'payment_method'
            ]);
            $providerData['currency_id'] = $request->currency;
            $providerData['ab_id'] = $request->provider_id;

            $provider = $request->id > 0 
                ? $this->eventABRepository->update($request->id, $providerData) 
                : $this->eventABRepository->create($providerData);

            if (!($request->id > 0)) {
                $this->statusHistoryRepository->create([
                    'status' => 'created', 
                    'user_id' => Auth::user()->id, 
                    'table' => 'event_abs', 
                    'table_id' => $provider->id
                ]);
            }

            return response()->json(['message' => 'A&B vinculado com sucesso!', 'data' => $provider]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao vincular A&B.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-abs/{id}
     * Unlinks an A&B provider from the event.
     */
    public function destroy($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $user = $this->userRepository->find(Auth::user()->id);
            if (!$user->getPermissions()->contains('name', 'status_level_2') && 
                $this->statusHistoryRepository->isBlockedTableRecord('event_abs', $id)) {
                return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
            }

            $r = $this->eventABRepository->find($id);
            if ($r) {
                $r->delete();
            }
            return response()->json(['message' => 'A&B removido com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover A&B.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/event-abs/opts
     * Saves or updates an A&B option.
     */
    public function storeOpt(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_ab_id' => 'required|integer',
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
            $hasLevel2Permission = $user->getPermissions()->contains('name', 'status_level_2');
            $hasLevel1Permission = $user->getPermissions()->contains('name', 'status_level_1');

            if (!$hasLevel2Permission) {
                $currentStatus = $this->statusHistoryRepository->latestStatusForTable('event_abs', $request->event_ab_id);
                if ($currentStatus && !StatusHistory::canUserEditStatus($currentStatus, $hasLevel2Permission, $hasLevel1Permission)) {
                    return response()->json(['message' => 'Esse registro não pode ser atualizado devido ao status atual!'], 422);
                }

                $eventAB = $this->eventABRepository->find($request->event_ab_id);
                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAB->event_id, $eventAB->ab_id, 'ab')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $optData = [
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
                'order' => $request->order ?? 0,
            ];

            if ($request->id > 0) {
                $opt = $this->eventABOptRepository->update($request->id, $optData);
            } else {
                $opt = $this->eventABOptRepository->create($optData);
            }

            return response()->json(['message' => 'Opção de A&B salva com sucesso!', 'data' => $opt]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar opção.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/event-abs/opts/{id}
     * Deletes an A&B option.
     */
    public function destroyOpt($id)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('land_operator')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $opt = $this->eventABOptRepository->findWithDetails($id);
            if (!$opt) {
                return response()->json(['message' => 'Registro não encontrado.'], 404);
            }

            $eventAB = $opt->event_ab;
            $user = $this->userRepository->find(Auth::user()->id);
            $hasLevel2Permission = $user->getPermissions()->contains('name', 'status_level_2');
            $hasLevel1Permission = $user->getPermissions()->contains('name', 'status_level_1');

            if (!$hasLevel2Permission) {
                $currentStatus = $this->statusHistoryRepository->latestStatusForTable('event_abs', $eventAB->id);
                if ($currentStatus && !StatusHistory::canUserEditStatus($currentStatus, $hasLevel2Permission, $hasLevel1Permission)) {
                    return response()->json(['message' => 'Esse registro não pode ser apagado devido ao status atual!'], 422);
                }

                if ($this->statusHistoryRepository->isProviderBlockedInEvent($eventAB->event_id, $eventAB->ab_id, 'ab')) {
                    return response()->json(['message' => 'Esse fornecedor já possui um registro bloqueado neste evento!'], 422);
                }
            }

            $opt->delete();
            return response()->json(['message' => 'Opção de A&B removida com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao remover opção.', 'error' => $e->getMessage()], 500);
        }
    }
}
