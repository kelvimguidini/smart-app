<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BrokerApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Gate::allows('broker_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Broker::with('city')->withoutGlobalScope('active');

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('contact', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortColumn = $request->get('sort_column', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Allowed columns for sorting
        $allowedColumns = ['id', 'name', 'email', 'contact', 'active'];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $brokers = $query->paginate($perPage);

        return response()->json($brokers);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('broker_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'city_id' => 'required|integer|exists:cities,id',
            'national' => 'nullable|boolean'
        ]);

        try {
            if ($request->id > 0) {
                $broker = Broker::withoutGlobalScope('active')->findOrFail($request->id);
                $broker->fill($request->only(['name', 'city_id', 'contact', 'phone', 'email', 'national']));
                $broker->save();
                
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $broker]);
            } else {
                $broker = Broker::create($request->only(['name', 'city_id', 'contact', 'phone', 'email', 'national']));
                
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $broker]);
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
        if (!Gate::allows('broker_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $broker = Broker::withoutGlobalScope('active')->findOrFail($id);
            $broker->delete();
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
        if (!Gate::allows('broker_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $broker = Broker::withoutGlobalScope('active')->findOrFail($id);
            $broker->activate();
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
        if (!Gate::allows('broker_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $broker = Broker::withoutGlobalScope('active')->findOrFail($id);
            $broker->deactivate();
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
