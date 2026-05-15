<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Domains\Budgets\Services\BudgetServiceInterface;
use App\Domains\Budgets\Repositories\ProviderBudgetRepositoryInterface;
use App\Domains\Events\Repositories\EventRepositoryInterface;
use App\Models\ProviderBudget;

class BudgetController extends Controller
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

    public function createLink(Request $request)
    {
        if (!Gate::allows('event_admin') && !Gate::allows('hotel_operator') && !Gate::allows('land_operator')) {
            abort(403);
        }

        try {
            $this->budgetService->sendBudgetLink($request->all(), Auth::user()->id);
        } catch (Exception $e) {
            return redirect()->back()->with('flash', ['message' => 'Erro ao enviar e-mail: ' . $e->getMessage(), 'type' => 'danger']);
        }

        return redirect()->back()->with('flash', ['message' => 'E-mail enviado com sucesso!', 'type' => 'success']);
    }

    public function budget(Request $request)
    {
        $data = $this->budgetRepository->getBudgetDataByToken($request->token);
        
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

            return Inertia::render('Auth/Event/Budget', [
                'tokenValid' => true,
                'event' => $event,
                'providerCity' => $provider->city ?? null,
                'providerName' => $provider->name ?? '',
                'eventHotel' => $data['eventHotel'],
                'eventAb' => $data['eventAb'],
                'eventHall' => $data['eventHall'],
                'eventAdd' => $data['eventAdd'],
                'budget' => $budget,
                'prove' => $request->prove,
                'user' => $request->user,
                'token' => $request->token,
                'tokenEvaluated' => $budget->evaluated ?? false
            ]);
        }

        return Inertia::render('Auth/Event/Budget', ['tokenValid' => false]);
    }

    public function store(Request $request)
    {
        try {
            $this->budgetService->submitBudget($request->all());
        } catch (Exception $e) {
            return redirect()->back()->with('flash', ['message' => 'Erro ao salvar: ' . $e->getMessage(), 'type' => 'danger']);
        }

        return redirect()->back()->with('flash', ['message' => 'Orçamento salvo com sucesso', 'type' => 'success']);
    }

    public function prove(Request $request)
    {
        if (!Gate::allows('save_budget', $request->user)) {
            return redirect()->back()->with('flash', ['message' => 'Sem autorização!', 'type' => 'danger']);
        }

        try {
            $this->budgetService->evaluateBudget($request->id, $request->user, $request->decision);
        } catch (Exception $e) {
            return redirect()->back()->with('flash', ['message' => 'Erro ao processar: ' . $e->getMessage(), 'type' => 'danger']);
        }

        return redirect()->back()->with('flash', ['message' => 'Orçamento processado com sucesso', 'type' => 'success']);
    }
}
