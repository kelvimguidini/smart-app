<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\ServiceAddServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceAddApiController extends Controller
{
    protected $serviceAddService;

    public function __construct(ServiceAddServiceInterface $serviceAddService)
    {
        $this->serviceAddService = $serviceAddService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('service_add_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $serviceAdds = $this->serviceAddService->list($filters, $perPage);

        return response()->json($serviceAdds);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('service_add_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $serviceAdd = $this->serviceAddService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $serviceAdd]);
            } else {
                $serviceAdd = $this->serviceAddService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $serviceAdd]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('service_add_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->serviceAddService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('service_add_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->serviceAddService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('service_add_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->serviceAddService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
