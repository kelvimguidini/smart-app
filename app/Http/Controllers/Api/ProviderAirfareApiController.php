<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\ProviderAirfareServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProviderAirfareApiController extends Controller
{
    protected $providerAirfare;

    public function __construct(ProviderAirfareServiceInterface $providerAirfare)
    {
        $this->providerAirfare = $providerAirfare;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('admin_provider_airfare')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $filters['per_page'] = $request->get('per_page', 10);

        $result = $this->providerAirfare->list($filters);

        return response()->json($result);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('admin_provider_airfare')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name'            => 'required|string|max:255',
            'contact'         => 'required|string|max:255',
            'phone'           => 'required|string|max:255',
            'email'           => 'required|string|max:255|email',
            'iss_percent'     => 'nullable|numeric|min:0|max:100',
            'service_percent' => 'nullable|numeric|min:0|max:100',
            'iva_percent'     => 'nullable|numeric|min:0|max:100',
            'payment_method'  => 'nullable|string|max:255',
        ]);

        $data = [
            'name'            => $request->name,
            'city_id'         => $request->city_id,
            'contact'         => $request->contact,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'national'        => $request->national ?? false,
            'iss_percent'     => $request->iss_percent,
            'service_percent' => $request->service_percent,
            'iva_percent'     => $request->iva_percent,
            'payment_method'  => $request->payment_method,
        ];

        try {
            if ($request->id > 0) {
                $provider = $this->providerAirfare->update($request->id, $data);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $provider]);
            } else {
                $provider = $this->providerAirfare->store($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $provider]);
            }
        } catch (\App\Exceptions\UniqueNameException $e) {
            return response()->json(['message' => 'Já existe um fornecedor aéreo cadastrado com este nome!'], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('admin_provider_airfare')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->providerAirfare->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('admin_provider_airfare')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->providerAirfare->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('admin_provider_airfare')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->providerAirfare->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
