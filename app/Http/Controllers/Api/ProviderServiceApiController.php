<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Providers\Services\ProviderServiceServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProviderServiceApiController extends Controller
{
    protected $providerServiceService;

    public function __construct(ProviderServiceServiceInterface $providerServiceService)
    {
        $this->providerServiceService = $providerServiceService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('admin_provider_service')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $providerServices = $this->providerServiceService->list($filters, $perPage);
        $providerServices->load('city'); // Load city relation for the datatable

        return response()->json($providerServices);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('admin_provider_service')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|integer',
            'contact' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'national' => 'nullable|boolean',
            'iss_percent' => 'nullable|numeric',
            'service_percent' => 'nullable|numeric',
            'iva_percent' => 'nullable|numeric',
            'codestur' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'name',
            'city_id',
            'contact',
            'phone',
            'email',
            'codestur',
            'payment_method',
        ]);

        // Garantir booleanos / numéricos seguros
        $data['national'] = $request->boolean('national', true);
        $data['iss_percent'] = $request->get('iss_percent', 0) ?: 0;
        $data['service_percent'] = $request->get('service_percent', 0) ?: 0;
        $data['iva_percent'] = $request->get('iva_percent', 0) ?: 0;

        try {
            if ($request->id > 0) {
                $providerService = $this->providerServiceService->update($request->id, $data);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $providerService]);
            } else {
                $providerService = $this->providerServiceService->create($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $providerService]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('admin_provider_service')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->providerServiceService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('admin_provider_service')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->providerServiceService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('admin_provider_service')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->providerServiceService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
