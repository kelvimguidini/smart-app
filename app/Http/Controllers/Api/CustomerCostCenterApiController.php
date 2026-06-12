<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Models\CustomerCostCenter;

class CustomerCostCenterApiController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('customer_cost_center_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $query = CustomerCostCenter::with('customer')->withoutGlobalScope('active');

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $items = $query->paginate($perPage);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('customer_cost_center_admin')) {
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
                $item = $this->customerRepository->saveCostCenter($data, $request->id);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $item]);
            } else {
                $item = $this->customerRepository->saveCostCenter($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $item]);
            }
        } catch (\App\Exceptions\UniqueNameException $e) {
            return response()->json(['message' => 'Já existe um centro de custo cadastrado com este nome para esta empresa!'], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!Gate::allows('customer_cost_center_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deleteCostCenter($id);
            return response()->json(['message' => 'Registro apagado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao apagar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateItem($id)
    {
        if (!Gate::allows('customer_cost_center_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->activateCostCenter($id);
            return response()->json(['message' => 'Registro ativado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao ativar o registro', 'error' => $e->getMessage()], 500);
        }
    }

    public function deactivateItem($id)
    {
        if (!Gate::allows('customer_cost_center_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deactivateCostCenter($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
