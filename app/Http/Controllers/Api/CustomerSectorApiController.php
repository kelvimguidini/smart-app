<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Models\CustomerSector;

class CustomerSectorApiController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('customer_sector_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $query = CustomerSector::with('customer')->withoutGlobalScope('active');

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $items = $query->paginate($perPage);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('customer_sector_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'customer_id' => 'required|integer',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'customer_id' => $request->customer_id,
            ];

            if ($request->id > 0) {
                $item = $this->customerRepository->saveSector($data, $request->id);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $item]);
            } else {
                $item = $this->customerRepository->saveSector($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $item]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('customer_sector_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deleteSector($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('customer_sector_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->activateSector($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('customer_sector_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deactivateSector($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
