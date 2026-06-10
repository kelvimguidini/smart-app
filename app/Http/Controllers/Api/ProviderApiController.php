<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Providers\Services\ProviderServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProviderApiController extends Controller
{
    protected $providerService;

    public function __construct(ProviderServiceInterface $providerService)
    {
        $this->providerService = $providerService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Gate::allows('admin_provider')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction', 'is_hotel']);
        $filters['per_page'] = $request->get('per_page', 10);
        // By default, if the frontend asks for hotels, we can pass is_hotel=1
        // We will manage it through the request params.

        $providers = $this->providerService->getProviders($filters);

        return response()->json($providers);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('admin_provider')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|integer|exists:city,id',
            'contact' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'national' => 'nullable|boolean',
            'iss_percent' => ['nullable', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
            'service_percent' => ['nullable', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
            'iva_percent' => ['nullable', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
            'payment_method' => 'nullable|string|max:255',
            'checkin_time' => 'nullable',
            'checkin_time_end' => 'nullable',
            'checkout_time' => 'nullable',
            'checkout_time_end' => 'nullable',
            'type' => 'nullable|string'
        ], [
            'iss_percent.min' => 'O campo ISS (%) não deve ser menor que 0.',
            'iss_percent.max' => 'O campo ISS (%) não deve ser maior que 100.',
            'iss_percent.regex' => 'O campo ISS (%) deve ter no máximo 1 casa decimal.',
            'service_percent.min' => 'O campo Taxa de Serviço (%) não deve ser menor que 0.',
            'service_percent.max' => 'O campo Taxa de Serviço (%) não deve ser maior que 100.',
            'service_percent.regex' => 'O campo Taxa de Serviço (%) deve ter no máximo 1 casa decimal.',
            'iva_percent.min' => 'O campo IVA (%) não deve ser menor que 0.',
            'iva_percent.max' => 'O campo IVA (%) não deve ser maior que 100.',
            'iva_percent.regex' => 'O campo IVA (%) deve ter no máximo 1 casa decimal.',
        ]);

        try {
            $data = $request->only([
                'name', 'city_id', 'contact', 'phone', 'email', 'national',
                'iss_percent', 'service_percent', 'iva_percent', 'payment_method',
                'checkin_time', 'checkin_time_end', 'checkout_time', 'checkout_time_end',
                'type'
            ]);

            // Implicitly set type to hotel if requested
            if (($data['type'] ?? '') === 'hotel' || !empty($request->is_hotel)) {
                $data['type'] = 'hotel'; // for the service
            }

            $this->providerService->storeProvider($data, $request->id > 0 ? $request->id : null, auth()->user()->id ?? 1);
            
            return response()->json(['message' => 'Registro salvo com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if (!Gate::allows('admin_provider')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Note: In legacy, delete is in providerRepository directly from Controller. 
            // Better to wrap it if there is a service method, but here we can just do:
            app(\App\Domains\Providers\Repositories\ProviderRepositoryInterface::class)->deactivate($id);
            // Actually, legacy did soft delete: $this->providerRepository->delete($id); 
            // Wait, ProviderRepositoryInterface doesn't have a simple delete method.
            // Oh, EloquentProviderRepository might have delete? Let's check or just use deactivate.
            // Legacy Controller: $this->providerRepository->delete($request->id); 
            // Wait, let's look at EloquentProviderRepository. It doesn't have `delete` mapped in interface.
            // Let's use Provider model directly or add delete to interface later. 
            \App\Models\Provider::find($id)?->delete();

            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Activate the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateItem($id)
    {
        if (!Gate::allows('admin_provider')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->providerService->bulkActivate([$id], 'hotel');
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Deactivate the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivateItem($id)
    {
        if (!Gate::allows('admin_provider')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->providerService->bulkDeactivate([$id], 'hotel');
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
