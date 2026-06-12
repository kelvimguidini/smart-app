<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Models\CustomerMetadata;

class CustomerMetadataApiController extends Controller
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
        if (!Gate::allows('customer_metadata_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $customerFilter = $request->get('customer_id', '');
        $typeFilter = $request->get('type', '');

        $query = CustomerMetadata::with('customer')->withoutGlobalScope('active');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('value', 'like', '%' . $search . '%')
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if (!empty($customerFilter)) {
            $query->where('customer_id', $customerFilter);
        }

        if (!empty($typeFilter)) {
            $query->where('type', $typeFilter);
        }

        $metadata = $query->paginate($perPage);

        return response()->json($metadata);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('customer_metadata_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'customer_id' => 'required|integer|exists:customer,id',
            'type' => 'required|string|in:requester,sector,cost_center',
            'value' => 'required|string|max:255',
        ]);

        try {
            $data = [
                'customer_id' => $request->customer_id,
                'type' => $request->type,
                'value' => $request->value,
            ];

            // Perform uniqueness check for same customer, type and value (excluding self)
            $query = CustomerMetadata::withoutGlobalScope('active')
                ->where('customer_id', $request->customer_id)
                ->where('type', $request->type)
                ->where('value', $request->value);

            if ($request->id > 0) {
                $query->where('id', '!=', $request->id);
            }

            if ($query->exists()) {
                return response()->json([
                    'message' => 'Já existe essa opção cadastrada para esse cliente!',
                    'type' => 'danger'
                ], 422);
            }

            if ($request->id > 0) {
                $metadata = $this->customerRepository->saveMetadata($data, $request->id);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $metadata]);
            } else {
                $metadata = $this->customerRepository->saveMetadata($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $metadata]);
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
        if (!Gate::allows('customer_metadata_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deleteMetadata($id);
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
        if (!Gate::allows('customer_metadata_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->activateMetadata($id);
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
        if (!Gate::allows('customer_metadata_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deactivateMetadata($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
