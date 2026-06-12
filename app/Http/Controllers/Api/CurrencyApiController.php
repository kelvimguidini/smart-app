<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;
use App\Models\Currency;

class CurrencyApiController extends Controller
{
    protected $lookupRepository;

    public function __construct(LookupRepositoryInterface $lookupRepository)
    {
        $this->lookupRepository = $lookupRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Gate::allows('currency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $sortColumn = $request->get('sort_column', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        $query = Currency::withoutGlobalScope('active');

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Apply sorting safely
        $allowedColumns = ['id', 'name', 'sigla', 'symbol'];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $currencies = $query->paginate($perPage);

        return response()->json($currencies);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('currency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:255'
        ]);

        try {
            if ($request->id > 0) {
                $currency = $this->lookupRepository->saveCurrency($request->only('name', 'sigla', 'symbol'), $request->id);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $currency]);
            } else {
                $currency = $this->lookupRepository->saveCurrency($request->only('name', 'sigla', 'symbol'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $currency]);
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
        if (!Gate::allows('currency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->lookupRepository->deleteCurrency($id);
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
        if (!Gate::allows('currency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->lookupRepository->activateCurrency($id);
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
        if (!Gate::allows('currency_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->lookupRepository->deactivateCurrency($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
