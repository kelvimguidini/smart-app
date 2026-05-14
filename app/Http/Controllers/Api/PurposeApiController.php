<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurposeApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Gate::allows('purpose_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Purpose::withoutGlobalScope('active');

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
        $purposes = $query->paginate($perPage);

        return response()->json($purposes);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('purpose_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $purpose = Purpose::withoutGlobalScope('active')->findOrFail($request->id);
                $purpose->name = $request->name;
                $purpose->save();
                
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $purpose]);
            } else {
                $purpose = Purpose::create([
                    'name' => $request->name
                ]);
                
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $purpose]);
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
        if (!Gate::allows('purpose_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $purpose = Purpose::withoutGlobalScope('active')->findOrFail($id);
            $purpose->delete();
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
        if (!Gate::allows('purpose_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $purpose = Purpose::withoutGlobalScope('active')->findOrFail($id);
            $purpose->activate();
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
        if (!Gate::allows('purpose_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $purpose = Purpose::withoutGlobalScope('active')->findOrFail($id);
            $purpose->deactivate();
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
