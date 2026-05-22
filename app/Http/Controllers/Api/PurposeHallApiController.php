<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\PurposeHallServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurposeHallApiController extends Controller
{
    protected $purposeHallService;

    public function __construct(PurposeHallServiceInterface $purposeHallService)
    {
        $this->purposeHallService = $purposeHallService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('purpose_hall_admin')) abort(403);

        $params = $request->only(['page', 'per_page', 'search', 'sort_column', 'sort_direction', 'active']);
        $purpose_halls = $this->purposeHallService->getPaginatedPurposeHalls($params);

        return response()->json($purpose_halls);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Gate::allows('purpose_hall_admin')) abort(403);

        $validated = $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'active' => 'boolean'
        ]);

        $purpose_hall = $this->purposeHallService->savePurposeHall($validated);

        return response()->json([
            'message' => 'Propósito do Salão salvo com sucesso',
            'data' => $purpose_hall
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!Gate::allows('purpose_hall_admin')) abort(403);

        $this->purposeHallService->deletePurposeHall($id);

        return response()->json(['message' => 'Propósito do Salão apagado com sucesso']);
    }

    /**
     * Activate the item.
     */
    public function activateItem($id)
    {
        if (!Gate::allows('purpose_hall_admin')) abort(403);

        $this->purposeHallService->activatePurposeHall($id);

        return response()->json(['message' => 'Propósito do Salão ativado com sucesso']);
    }

    /**
     * Deactivate the item.
     */
    public function deactivateItem($id)
    {
        if (!Gate::allows('purpose_hall_admin')) abort(403);

        $this->purposeHallService->deactivatePurposeHall($id);

        return response()->json(['message' => 'Propósito do Salão inativado com sucesso']);
    }
}
