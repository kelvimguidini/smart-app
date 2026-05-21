<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;

class CustomerController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('customer_admin')) abort(403);
        $this->customerRepository->activate($id);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('customer_admin')) abort(403);
        $this->customerRepository->deactivate($id);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create(Request $request)
    {
        if (!Gate::allows('customer_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/Customer', [
            'customers' => $this->customerRepository->allWithInactive()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('customer_admin')) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $url = null;
            if ($request->file('logo')) {
                $path = Storage::putFile('public/logos', $request->file('logo'));
                $url = Storage::url($path);
                Artisan::call('files:copy');
            }

            $data = [
                'name' => $request->name,
                'color' => $request->color,
                'document' => $request->document,
                'phone' => $request->phone,
                'email' => $request->email,
                'responsibleAuthorizing' => $request->responsibleAuthorizing,
            ];

            if ($url) {
                $data['logo'] = $url;
            } elseif ($request->logo) {
                $data['logo'] = $request->logo;
            }

            if ($request->id > 0) {
                $this->customerRepository->update($request->id, $data);
            } else {
                $this->customerRepository->create($data);
            }
        } catch (Exception $e) {
            if (isset($path)) Storage::delete($path);
            throw $e;
        }

        return redirect()->route('customer')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('customer_admin')) abort(403);

        try {
            $customer = $this->customerRepository->find($request->id);
            if ($customer->logo) {
                // Tentar converter URL pública de volta para path de storage se necessário
                $path = str_replace('/storage/', 'public/', $customer->logo);
                Storage::delete($path);
            }
            $this->customerRepository->delete($request->id);
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('customer')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
