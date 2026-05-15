<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;

class VehicleController extends Controller
{
    protected $lookupRepository;

    public function __construct(LookupRepositoryInterface $lookupRepository)
    {
        $this->lookupRepository = $lookupRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('vehicle_admin')) abort(403);
        $this->lookupRepository->activateVehicle($id);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('vehicle_admin')) abort(403);
        $this->lookupRepository->deactivateVehicle($id);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create()
    {
        if (!Gate::allows('vehicle_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/Vehicle', [
            'vehicles' => $this->lookupRepository->getVehiclesWithInactive()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('vehicle_admin')) abort(403);

        $request->validate(['name' => 'required|string|max:255']);

        try {
            $this->lookupRepository->saveVehicle(['name' => $request->name], $request->id > 0 ? $request->id : null);
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('vehicle')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('vehicle_admin')) abort(403);
        $this->lookupRepository->deleteVehicle($request->id);
        return redirect()->route('vehicle')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
