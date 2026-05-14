<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceTypeApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Gate::allows('service_type_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = ServiceType::withoutGlobalScope('active');

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortColumn = $request->get('sort_column', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Allowed columns for sorting
        $allowedColumns = ['id', 'name', 'active'];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $serviceTypes = $query->paginate($perPage);

        return response()->json($serviceTypes);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('service_type_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $serviceType = ServiceType::withoutGlobalScope('active')->findOrFail($request->id);
                $serviceType->name = $request->name;
                $serviceType->save();
                
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $serviceType]);
            } else {
                $serviceType = ServiceType::create([
                    'name' => $request->name
                ]);
                
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $serviceType]);
            }
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
    public function destroy($id)
    {
        if (!Gate::allows('service_type_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $serviceType = ServiceType::withoutGlobalScope('active')->findOrFail($id);
            $serviceType->delete();
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
        if (!Gate::allows('service_type_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $serviceType = ServiceType::withoutGlobalScope('active')->findOrFail($id);
            $serviceType->activate();
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
        if (!Gate::allows('service_type_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $serviceType = ServiceType::withoutGlobalScope('active')->findOrFail($id);
            $serviceType->deactivate();
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
