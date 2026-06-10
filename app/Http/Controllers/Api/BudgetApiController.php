<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Budgets\Services\BudgetServiceInterface;
use App\Domains\Budgets\Repositories\ProviderBudgetRepositoryInterface;
use App\Domains\Events\Repositories\EventRepositoryInterface;
use App\Models\ProviderBudget;

class BudgetApiController extends Controller
{
    protected $budgetService;
    protected $budgetRepository;
    protected $eventRepository;

    public function __construct(
        BudgetServiceInterface $budgetService,
        ProviderBudgetRepositoryInterface $budgetRepository,
        EventRepositoryInterface $eventRepository
    ) {
        $this->budgetService = $budgetService;
        $this->budgetRepository = $budgetRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Show budget details by token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $token = $request->get('token');
        if (empty($token)) {
            return response()->json(['tokenValid' => false, 'message' => 'Token não fornecido.'], 400);
        }

        $data = $this->budgetRepository->getBudgetDataByToken($token);
        
        if ($data['eventHotel'] || $data['eventAb'] || $data['eventHall'] || $data['eventAdd']) {
            $provider = $data['eventHotel']->hotel ?? $data['eventAb']->ab ?? $data['eventHall']->hall ?? $data['eventAdd']->add;
            $eventId = $data['eventHotel']->event_id ?? $data['eventAb']->event_id ?? $data['eventHall']->event_id ?? $data['eventAdd']->event_id;
            
            $budget = ProviderBudget::with(['providerBudgetItems'])
                ->when($data['eventHotel'], fn($q) => $q->where('event_hotel_id', $data['eventHotel']->id))
                ->when($data['eventAb'], fn($q) => $q->where('event_ab_id', $data['eventAb']->id))
                ->when($data['eventHall'], fn($q) => $q->where('event_hall_id', $data['eventHall']->id))
                ->when($data['eventAdd'], fn($q) => $q->where('event_add_id', $data['eventAdd']->id))
                ->first();

            $event = $this->eventRepository->find($eventId);

            // Eager load necessary options relations for the tables
            if ($data['eventHotel']) {
                $data['eventHotel']->load(['eventHotelsOpt.regime', 'eventHotelsOpt.purpose', 'eventHotelsOpt.categoryHotel', 'eventHotelsOpt.aptoHotel', 'currency']);
            }
            if ($data['eventAb']) {
                $data['eventAb']->load(['eventAbOpts.service', 'eventAbOpts.serviceType', 'eventAbOpts.local', 'currency']);
            }
            if ($data['eventHall']) {
                $data['eventHall']->load(['eventHallOpts.purpose', 'currency']);
            }
            if ($data['eventAdd']) {
                $data['eventAdd']->load(['eventAddOpts.service', 'eventAddOpts.measure', 'eventAddOpts.frequency', 'currency']);
            }

            $prove = $request->get('prove') === 'true' || $request->get('prove') == 1;

            return response()->json([
                'tokenValid' => true,
                'event' => $event,
                'providerCity' => $provider->city ?? null,
                'providerName' => $provider->name ?? '',
                'eventHotel' => $data['eventHotel'],
                'eventAb' => $data['eventAb'],
                'eventHall' => $data['eventHall'],
                'eventAdd' => $data['eventAdd'],
                'budget' => $budget,
                'prove' => $prove,
                'user' => $request->get('user') ? (int) $request->get('user') : 0,
                'token' => $token,
                'tokenEvaluated' => $budget->evaluated ?? false
            ]);
        }

        return response()->json(['tokenValid' => false]);
    }

    /**
     * Store/submit a new budget response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $this->budgetService->submitBudget($request->all());
            return response()->json(['message' => 'Orçamento salvo com sucesso!', 'type' => 'success']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao salvar: ' . $e->getMessage(), 'type' => 'danger'], 500);
        }
    }

    /**
     * Prove/approve/reject the budget.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function prove(Request $request)
    {
        // Check if the user is authorized to save/evaluate budget
        if (!Gate::allows('save_budget', $request->get('user'))) {
            return response()->json(['message' => 'Sem autorização!', 'type' => 'danger'], 403);
        }

        try {
            $this->budgetService->evaluateBudget($request->get('id'), $request->get('user'), $request->get('decision'));
            return response()->json(['message' => 'Orçamento processado com sucesso!', 'type' => 'success']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Erro ao processar: ' . $e->getMessage(), 'type' => 'danger'], 500);
        }
    }
}
