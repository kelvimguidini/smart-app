<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Domains\Dashboard\Repositories\DashboardRepositoryInterface;

class HomeController extends Controller
{
    protected $dashboardRepository;

    public function __construct(DashboardRepositoryInterface $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function fetchDashboardData(Request $request)
    {
        try {
            $userId = Auth::user()->id;
            $roles = $this->getUserRoles();

            return response()->json([
                'pendingValidate' => $this->dashboardRepository->getPendingValidateCount($userId, $roles),
                'eventStatus' => $this->dashboardRepository->getEventStatusCounts(),
                'waitApproval' => $this->dashboardRepository->getWaitApprovalCounts(),
                'linksApproved' => $this->dashboardRepository->getBudgetApprovalRate(),
                'byMonths' => $this->dashboardRepository->getMonthlyEvolutionData($userId, $roles),
                'userGroups' => $this->dashboardRepository->getUserGroupDistribution(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna os papéis do usuário logado para fins de filtro.
     */
    private function getUserRoles(): array
    {
        $roles = [];
        if (Gate::allows('event_admin')) $roles[] = 'event_admin';
        if (Gate::allows('hotel_operator')) $roles[] = 'hotel_operator';
        if (Gate::allows('land_operator')) $roles[] = 'land_operator';
        if (Gate::allows('air_operator')) $roles[] = 'air_operator';
        return $roles;
    }

    // Métodos individuais mantidos para compatibilidade se chamados via rota direta, 
    // mas agora utilizando o repositório.

    public function pendingValidate(Request $request)
    {
        return $this->dashboardRepository->getPendingValidateCount(Auth::user()->id, $this->getUserRoles());
    }

    public function eventStatus(Request $request)
    {
        return response()->json($this->dashboardRepository->getEventStatusCounts());
    }

    public function waitApproval(Request $request)
    {
        return response()->json($this->dashboardRepository->getWaitApprovalCounts());
    }

    public function linksApproved(Request $request)
    {
        return $this->dashboardRepository->getBudgetApprovalRate();
    }

    public function byMonths(Request $request)
    {
        return response()->json($this->dashboardRepository->getMonthlyEvolutionData(Auth::user()->id, $this->getUserRoles()));
    }

    public function userGroups(Request $request)
    {
        return response()->json($this->dashboardRepository->getUserGroupDistribution());
    }
}
