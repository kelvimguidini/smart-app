<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use App\Models\City;
use App\Models\CRD;
use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class CityController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('city_admin')) {
            abort(403);
        }

        $t = City::get();
        return Inertia::render('Auth/Auxiliaries/City', [
            'cities' => $t,
            'ufs' => Constants::UFS
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        if (!Gate::allows('city_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);
        try {

            if ($request->id > 0) {

                $city = City::find($request->id);

                $city->name = $request->name;
                $city->states = $request->states;
                $city->country = $request->country;
                $city->save();
            } else {

                $city = City::create([
                    'name' => $request->name,
                    'states' => $request->states,
                    'country' => $request->country,
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('city')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('city_admin')) {
            abort(403);
        }
        try {

            $r = City::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('city')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function searchCities(Request $request)
    {
        $term = $request->term;

        // Realize a consulta no banco de dados usando o termo de pesquisa
        $cities = City::where('name', 'LIKE', '%' . $term . '%')
            ->orWhere('states', 'LIKE', '%' . $term . '%')
            ->select('id', 'name', 'states', 'country')
            ->get();

        return response()->json($cities);
    }
}
