<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;

class BrokerController extends Controller
{
    protected $lookupRepository;

    public function __construct(LookupRepositoryInterface $lookupRepository)
    {
        $this->lookupRepository = $lookupRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('broker_admin')) abort(403);
        $this->lookupRepository->activateBroker($id);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('broker_admin')) abort(403);
        $this->lookupRepository->deactivateBroker($id);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create()
    {
        if (!Gate::allows('broker_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/Broker', [
            'brokers' => $this->lookupRepository->getBrokersWithInactive(),
            'cities' => $this->lookupRepository->getAllCities()
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('broker_admin')) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|max:255|email',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'city_id' => $request->city,
                'contact' => $request->contact,
                'phone' => $request->phone,
                'email' => $request->email,
                'national' => $request->national,
            ];
            $this->lookupRepository->saveBroker($data, $request->id > 0 ? $request->id : null);
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('broker')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('broker_admin')) abort(403);
        $this->lookupRepository->deleteBroker($request->id);
        return redirect()->route('broker')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
