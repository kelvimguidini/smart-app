<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceType;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class ServiceTypeController extends Controller
{
    public function activateM($id)
    {
        if (!Gate::allows('service_type_admin')) {
            abort(403);
        }
        return $this->activate($id, ServiceType::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('service_type_admin')) {
            abort(403);
        }
        return $this->deactivate($id, ServiceType::class);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('service_type_admin')) {
            abort(403);
        }

        $t = ServiceType::withoutGlobalScope('active')->get();
        return Inertia::render('Auth/Auxiliaries/ServiceType', [
            'serviceTypes' => $t
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
        if (!Gate::allows('service_type_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $obj = ServiceType::withoutGlobalScope('active')->find($request->id);

                $obj->name = $request->name;
                $obj->save();
            } else {

                $obj = ServiceType::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('service-type')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('service_type_admin')) {
            abort(403);
        }
        try {

            $r = ServiceType::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('service-type')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
