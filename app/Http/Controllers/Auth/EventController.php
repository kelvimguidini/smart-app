<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CRD;
use App\Models\Customer;
use App\Models\Event;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function list()
    {
        if (!Gate::allows('event_admin')) {
            abort(403);
        }

        $e = Event::with("crd")
            ->with('customer')
            ->with('hotel_operator')
            ->with('air_operator')
            ->with('land_operator')
            ->get();

        return Inertia::render('Auth/EventList', [
            'events' => $e
        ]);
    }


    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        if (!Gate::allows('event_admin')) {
            abort(403);
        }

        $event = Event::find($request->id);

        $crds = CRD::all();
        $customers = Customer::all();
        $users = User::all();

        return Inertia::render('Auth/EventCreate', [
            'crds' => $crds,
            'customers' => $customers,
            'users' => $users,
            'event' => $event
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
        if (!Gate::allows('event_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18',
        ]);
        try {

            if ($request->id > 0) {

                $crd = CRD::find($request->id);

                $crd->name = $request->name;
                $crd->cnpj = $request->cnpj;
                $crd->save();
            } else {

                $crd = CRD::create([
                    'name' => $request->name,
                    'cnpj' => $request->cnpj,
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('crd')->with('flash', ['message' => 'Register saved Successful', 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('crd_admin')) {
            abort(403);
        }
        try {

            $r = CRD::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('crd')->with('flash', ['message' => 'Registro apagado com sucesso!', 'type' => 'success']);
    }
}
