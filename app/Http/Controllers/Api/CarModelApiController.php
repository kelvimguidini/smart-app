<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\CarModelServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
 
class CarModelApiController extends Controller
{
    protected $carModelService;
 
    public function __construct(CarModelServiceInterface $carModelService)
    {
        $this->carModelService = $carModelService;
    }
 
    public function index(Request $request)
    {
        if (!Gate::allows('car_model_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);
 
        $carModels = $this->carModelService->list($filters, $perPage);
 
        return response()->json($carModels);
    }
 
    public function store(Request $request)
    {
        if (!Gate::allows('car_model_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
 
        try {
            if ($request->id > 0) {
                $carModel = $this->carModelService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $carModel]);
            } else {
                $carModel = $this->carModelService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $carModel]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function destroy($id)
    {
        if (!Gate::allows('car_model_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->carModelService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function activateItem($id)
    {
        if (!Gate::allows('car_model_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->carModelService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function deactivateItem($id)
    {
        if (!Gate::allows('car_model_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->carModelService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
