<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Domains\Shared\Services\BrandServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
 
class BrandApiController extends Controller
{
    protected $brandService;
 
    public function __construct(BrandServiceInterface $brandService)
    {
        $this->brandService = $brandService;
    }
 
    public function index(Request $request)
    {
        if (!Gate::allows('brand_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        $filters = $request->only(['search', 'sort_column', 'sort_direction']);
        $perPage = $request->get('per_page', 10);
 
        $brands = $this->brandService->list($filters, $perPage);
 
        return response()->json($brands);
    }
 
    public function store(Request $request)
    {
        if (!Gate::allows('brand_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
 
        try {
            if ($request->id > 0) {
                $brand = $this->brandService->update($request->id, $request->only('name'));
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $brand]);
            } else {
                $brand = $this->brandService->create($request->only('name'));
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $brand]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function destroy($id)
    {
        if (!Gate::allows('brand_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->brandService->delete($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function activateItem($id)
    {
        if (!Gate::allows('brand_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->brandService->activate($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
 
    public function deactivateItem($id)
    {
        if (!Gate::allows('brand_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        try {
            $this->brandService->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
