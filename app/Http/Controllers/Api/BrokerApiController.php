<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\BrokerServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BrokerApiController extends Controller
{
    protected $brokerService;

    public function __construct(BrokerServiceInterface $brokerService)
    {
        $this->brokerService = $brokerService;
    }

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

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $brokers = $this->brokerService->list($filters, $perPage);

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
                $broker = $this->brokerService->update($request->id, $request->only(['name', 'city_id', 'contact', 'phone', 'email', 'national']));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $broker]);
            } else {
                $broker = $this->brokerService->create($request->only(['name', 'city_id', 'contact', 'phone', 'email', 'national']));
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
            $this->brokerService->delete($id);
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
            $this->brokerService->activate($id);
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
            $this->brokerService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
