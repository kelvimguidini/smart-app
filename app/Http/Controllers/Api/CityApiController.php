<?php

namespace App\Http\Controllers\Api;

use App\Domains\Shared\Services\CityServiceInterface;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\City;

class CityApiController extends Controller
{
    protected $cityService;
    protected $lookupRepository;

    public function __construct(CityServiceInterface $cityService, LookupRepositoryInterface $lookupRepository)
    {
        $this->cityService = $cityService;
        $this->lookupRepository = $lookupRepository;
    }

    /**
     * Search cities for autocomplete
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');
        
        $cities = $this->cityService->search($term);

        return response()->json($cities);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Gate::allows('city_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $sortColumn = $request->get('sort_column', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        $query = City::withoutGlobalScope('active');

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('states', 'like', '%' . $search . '%')
                  ->orWhere('country', 'like', '%' . $search . '%');
        }

        // Apply sorting safely
        $allowedColumns = ['id', 'name', 'states', 'country'];
        if (in_array($sortColumn, $allowedColumns)) {
            $query->orderBy($sortColumn, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $cities = $query->paginate($perPage);

        return response()->json($cities);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('city_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'states' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'lat' => 'nullable|string|max:50',
            'lng' => 'nullable|string|max:50',
        ]);

        try {
            $data = $request->only('name', 'states', 'country', 'lat', 'lng', 'place_id');

            if ($request->id > 0) {
                $city = $this->lookupRepository->saveCity($data, $request->id);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $city]);
            } else {
                $city = $this->lookupRepository->saveCity($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $city]);
            }
        } catch (\App\Exceptions\UniqueNameException $e) {
            return response()->json(['message' => 'Já existe uma cidade cadastrada com este nome para o mesmo estado e país!'], 422);
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
        if (!Gate::allows('city_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->lookupRepository->deleteCity($id);
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
        if (!Gate::allows('city_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->lookupRepository->activateCity($id);
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
        if (!Gate::allows('city_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->lookupRepository->deactivateCity($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
