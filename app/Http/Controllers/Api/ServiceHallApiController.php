<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\ServiceHallServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceHallApiController extends Controller
{
    protected $serviceHallService;

    public function __construct(ServiceHallServiceInterface $serviceHallService)
    {
        $this->serviceHallService = $serviceHallService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);

        $params = $request->only(['page', 'per_page', 'search', 'sort_column', 'sort_direction', 'active']);
        $service_halls = $this->serviceHallService->getPaginatedServiceHalls($params);

        return response()->json($service_halls);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);

        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'active' => 'boolean'
        ]);

        $service_hall = $this->serviceHallService->saveServiceHall($validated);

        return response()->json([
            'message' => 'Serviço do Salão salvo com sucesso',
            'data' => $service_hall
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);

        $this->serviceHallService->deleteServiceHall($id);

        return response()->json(['message' => 'Serviço do Salão apagado com sucesso']);
    }

    /**
     * Activate the item.
     */
    public function activateItem($id)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);

        $this->serviceHallService->activateServiceHall($id);

        return response()->json(['message' => 'Serviço do Salão ativado com sucesso']);
    }

    /**
     * Deactivate the item.
     */
    public function deactivateItem($id)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);

        $this->serviceHallService->deactivateServiceHall($id);

        return response()->json(['message' => 'Serviço do Salão inativado com sucesso']);
    }
}
