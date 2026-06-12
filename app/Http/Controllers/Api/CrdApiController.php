<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Models\CRD;

class CrdApiController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Gate::allows('crd_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $query = CRD::with('customer')->withoutGlobalScope('active');

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('number', 'like', '%' . $search . '%');
        }

        $crds = $query->paginate($perPage);

        return response()->json($crds);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('crd_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:18',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'number' => $request->number,
                'customer_id' => $request->customer_id,
            ];

            if ($request->id > 0) {
                $crd = $this->customerRepository->saveCrd($data, $request->id);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $crd]);
            } else {
                $crd = $this->customerRepository->saveCrd($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $crd]);
            }
        } catch (\App\Exceptions\UniqueNameException $e) {
            return response()->json(['message' => 'Já existe um CRD cadastrado com este nome para esta empresa!'], 422);
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
        if (!Gate::allows('crd_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deleteCrd($id);
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
        if (!Gate::allows('crd_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->activateCrd($id);
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
        if (!Gate::allows('crd_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deactivateCrd($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
