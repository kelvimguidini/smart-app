<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;

class CustomerApiController extends Controller
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
        if (!Gate::allows('customer_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $query = Customer::withoutGlobalScope('active');

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('document', 'like', '%' . $search . '%');
        }

        $customers = $query->paginate($perPage);

        return response()->json($customers);
    }

    /**
     * Store a newly created resource in storage or update an existing one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Gate::allows('customer_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $data = $request->only('name', 'color', 'document', 'phone', 'email', 'responsibleAuthorizing');
            
            // Handle logo upload if provided
            if ($request->hasFile('logo')) {
                $path = \Illuminate\Support\Facades\Storage::putFile('public/logos', $request->file('logo'));
                $data['logo'] = \Illuminate\Support\Facades\Storage::url($path);
                \Illuminate\Support\Facades\Artisan::call('files:copy');
            } elseif ($request->filled('logo') && is_string($request->logo)) {
                // Keep existing logo if passed as string (e.g. from existing record)
                $data['logo'] = $request->logo;
            }

            if ($request->id > 0) {
                $customer = $this->customerRepository->update($request->id, $data);
                return response()->json(['message' => 'Registro atualizado com sucesso', 'data' => $customer]);
            } else {
                $customer = $this->customerRepository->create($data);
                return response()->json(['message' => 'Registro salvo com sucesso', 'data' => $customer]);
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
        if (!Gate::allows('customer_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $customer = $this->customerRepository->find($id);
            if ($customer && $customer->logo) {
                $path = str_replace('/storage/', 'public/', $customer->logo);
                \Illuminate\Support\Facades\Storage::delete($path);
            }
            $this->customerRepository->delete($id);
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
        if (!Gate::allows('customer_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->activate($id);
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
        if (!Gate::allows('customer_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->customerRepository->deactivate($id);
            return response()->json(['message' => 'Registro inativado com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao inativar o registro', 'error' => $e->getMessage()], 500);
        }
    }
}
