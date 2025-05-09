<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class VehicleController extends Controller
{
    public function activateM($id)
    {
        if (!Gate::allows('vehicle_admin')) {
            abort(403);
        }
        return $this->activate($id, Vehicle::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('vehicle_admin')) {
            abort(403);
        }
        return $this->deactivate($id, Vehicle::class);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('vehicle_admin')) {
            abort(403);
        }

        $t = Vehicle::withoutGlobalScope('active')->get();
        return Inertia::render('Auth/Auxiliaries/Vehicle', [
            'vehicles' => $t
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
        if (!Gate::allows('vehicle_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $Vehicle = Vehicle::withoutGlobalScope('active')->find($request->id);

                $Vehicle->name = $request->name;
                $Vehicle->save();
            } else {

                $Vehicle = Vehicle::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('vehicle')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('vehicle_admin')) {
            abort(403);
        }
        try {

            $r = Vehicle::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('vehicle')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
