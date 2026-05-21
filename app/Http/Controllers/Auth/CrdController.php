<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;

class CrdController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('crd_admin')) abort(403);
        $this->customerRepository->activateCrd($id);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('crd_admin')) abort(403);
        $this->customerRepository->deactivateCrd($id);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create()
    {
        if (!Gate::allows('crd_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/CRD', [
            'crds' => $this->customerRepository->allCrdsWithInactive(),
            'customers' => $this->customerRepository->all()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('crd_admin')) abort(403);

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
            $this->customerRepository->saveCrd($data, $request->id > 0 ? $request->id : null);
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('crd')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('crd_admin')) abort(403);
        $this->customerRepository->deleteCrd($request->id);
        return redirect()->route('crd')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
