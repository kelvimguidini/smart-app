<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CarModel;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class CarModelController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('car_model_admin')) {
            abort(403);
        }

        $t = CarModel::all();
        return Inertia::render('Auth/Auxiliaries/CarModel', [
            'carModels' => $t
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
        if (!Gate::allows('car_model_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $CarModel = CarModel::find($request->id);

                $CarModel->name = $request->name;
                $CarModel->save();
            } else {

                $CarModel = CarModel::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('car-model')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('car_model_admin')) {
            abort(403);
        }
        try {

            $r = CarModel::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('car-model')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
