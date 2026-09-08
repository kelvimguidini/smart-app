<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\AirfareCabinServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AirfareCabinApiController extends Controller
{
    protected $cabinService;

    public function __construct(AirfareCabinServiceInterface $cabinService)
    {
        $this->cabinService = $cabinService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('airfare_cabin_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $cabins = $this->cabinService->list($filters, $perPage);

        return response()->json($cabins);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('airfare_cabin_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $cabin = $this->cabinService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $cabin]);
            } else {
                $cabin = $this->cabinService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $cabin]);
            }
        } catch (\App\Exceptions\UniqueNameException $e) {
            return response()->json(['message' => 'Já existe uma cabine cadastrada com este nome!'], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('airfare_cabin_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->cabinService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('airfare_cabin_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->cabinService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('airfare_cabin_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->cabinService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
