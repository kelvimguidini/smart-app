<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Domains\Shared\Repositories\LookupRepositoryInterface;

class CityController extends Controller
{
    protected $lookupRepository;

    public function __construct(LookupRepositoryInterface $lookupRepository)
    {
        $this->lookupRepository = $lookupRepository;
    }

    public function activateM($id)
    {
        if (!Gate::allows('city_admin')) abort(403);
        $this->lookupRepository->activateCity($id);
        return redirect()->back()->with('flash', ['message' => 'Registro ativado com sucesso!', 'type' => 'success']);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('city_admin')) abort(403);
        $this->lookupRepository->deactivateCity($id);
        return redirect()->back()->with('flash', ['message' => 'Registro inativado com sucesso.']);
    }

    public function create()
    {
        if (!Gate::allows('city_admin')) abort(403);

        return Inertia::render('Auth/Auxiliaries/City', [
            'cities' => $this->lookupRepository->getCitiesWithInactive(),
            'ufs' => Constants::UFS
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('city_admin')) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'states' => $request->states,
                'country' => $request->country,
            ];
            $this->lookupRepository->saveCity($data, $request->id > 0 ? $request->id : null);
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('city')->with('flash', ['message' => 'Registro salvo com sucesso', 'type' => 'success']);
    }

    public function delete(Request $request)
    {
        if (!Gate::allows('city_admin')) abort(403);
        $this->lookupRepository->deleteCity($request->id);
        return redirect()->route('city')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }

    public function searchCities(Request $request)
    {
        $cities = $this->lookupRepository->searchCities($request->term ?? '');
        return response()->json($cities);
    }
}
