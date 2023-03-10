<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ServiceAdd;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ServiceAddController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('service_add_admin')) {
            abort(403);
        }

        $t = ServiceAdd::all();
        return Inertia::render('Auth/Auxiliaries/ServiceAdd', [
            'services' => $t
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
        if (!Gate::allows('service_add_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $obj = ServiceAdd::find($request->id);

                $obj->name = $request->name;
                $obj->save();
            } else {

                $obj = ServiceAdd::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('service-add')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('service_add_admin')) {
            abort(403);
        }
        try {

            $r = ServiceAdd::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('service-add')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
