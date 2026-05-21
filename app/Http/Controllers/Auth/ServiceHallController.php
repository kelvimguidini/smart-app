<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;

class ServiceHallController extends Controller
{
    protected $lookupRepository;

    public function __construct(LookupRepositoryInterface $lookupRepository)
    {
        $this->lookupRepository = $lookupRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);
        $this->lookupRepository->activateServiceHall($id);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);
        $this->lookupRepository->deactivateServiceHall($id);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create()
    {
        if (!Gate::allows('service_hall_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/ServiceHall', [
            'serviceHalls' => $this->lookupRepository->getServiceHallsWithInactive()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);

        $request->validate(['name' => 'required|string|max:255']);

        try {
            $this->lookupRepository->saveServiceHall(['name' => $request->name], $request->id > 0 ? $request->id : null);
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('service-hall')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('service_hall_admin')) abort(403);
        $this->lookupRepository->deleteServiceHall($request->id);
        return redirect()->route('service-hall')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
