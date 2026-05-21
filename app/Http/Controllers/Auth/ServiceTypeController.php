<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;

class ServiceTypeController extends Controller
{
    protected $lookupRepository;

    public function __construct(LookupRepositoryInterface $lookupRepository)
    {
        $this->lookupRepository = $lookupRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('service_type_admin')) abort(403);
        $this->lookupRepository->activateServiceType($id);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('service_type_admin')) abort(403);
        $this->lookupRepository->deactivateServiceType($id);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create()
    {
        if (!Gate::allows('service_type_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/ServiceType', [
            'serviceTypes' => $this->lookupRepository->getServiceTypesWithInactive()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('service_type_admin')) abort(403);

        $request->validate(['name' => 'required|string|max:255']);

        try {
            $this->lookupRepository->saveServiceType(['name' => $request->name], $request->id > 0 ? $request->id : null);
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('service-type')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('service_type_admin')) abort(403);
        $this->lookupRepository->deleteServiceType($request->id);
        return redirect()->route('service-type')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
