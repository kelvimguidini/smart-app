<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\TransportServiceServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransportServiceApiController extends Controller
{
    protected $transportService;

    public function __construct(TransportServiceServiceInterface $transportService)
    {
        $this->transportService = $transportService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('transport_service_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $services = $this->transportService->list($filters, $perPage);

        return response()->json($services);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('transport_service_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $service = $this->transportService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $service]);
            } else {
                $service = $this->transportService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $service]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('transport_service_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->transportService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('transport_service_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->transportService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('transport_service_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->transportService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
