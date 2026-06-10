<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\VehicleServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
 
class VehicleApiController extends Controller
{
    protected $vehicleService;
 
    public function __construct(VehicleServiceInterface $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }
 
    public function index(Request $request)
    {
        if (!Gate::allows('vehicle_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);
 
        $vehicles = $this->vehicleService->list($filters, $perPage);
 
        return response()->json($vehicles);
    }
 
    public function store(Request $request)
    {
        if (!Gate::allows('vehicle_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
 
        try {
            if ($request->id > 0) {
                $vehicle = $this->vehicleService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $vehicle]);
            } else {
                $vehicle = $this->vehicleService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $vehicle]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function destroy($id)
    {
        if (!Gate::allows('vehicle_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->vehicleService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function activateItem($id)
    {
        if (!Gate::allows('vehicle_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->vehicleService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function deactivateItem($id)
    {
        if (!Gate::allows('vehicle_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->vehicleService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
