<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\AirfareAirportServiceInterface;
use App\Models\AirfareAirport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AirportApiController extends Controller
{
    protected $airportService;

    public function __construct(AirfareAirportServiceInterface $airportService)
    {
        $this->airportService = $airportService;
    }

    /**
     * Search airports for autocomplete by IATA, name, or city.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');

        $airports = AirfareAirport::search($term)
            ->where('active', true)
            ->orderBy('iata_code', 'asc')
            ->limit(30)
            ->get();

        return response()->json($airports);
    }

    public function index(Request $request)
    {
        if (!Gate::allows('airfare_airport_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);

        $airports = $this->airportService->list($filters, $perPage);

        return response()->json($airports);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('airfare_airport_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $id = $request->id;

        $request->validate([
            'iata_code' => [
                'required',
                'string',
                'size:3',
                Rule::unique('airfare_airports', 'iata_code')->ignore($id)->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
        ], [
            'iata_code.required' => 'O código IATA é obrigatório.',
            'iata_code.size' => 'O código IATA deve ter exatamente 3 letras.',
            'iata_code.unique' => 'Já existe um aeroporto com este código IATA.',
            'name.required' => 'O nome do aeroporto é obrigatório.',
            'city.required' => 'A cidade é obrigatória.',
        ]);

        $data = $request->only(['iata_code', 'name', 'city', 'state', 'country']);
        if (empty($data['country'])) {
            $data['country'] = 'Brasil';
        }

        try {
            if ($id > 0) {
                $airport = $this->airportService->update($id, $data);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $airport]);
            } else {
                $airport = $this->airportService->create($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $airport]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('airfare_airport_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->airportService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('airfare_airport_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->airportService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('airfare_airport_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->airportService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
