<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Constants;
use App\Models\Broker;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class BrokerController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('broker_admin')) {
            abort(403);
        }

        $t = Broker::all();
        return Inertia::render('Auth/Auxiliaries/Broker', [
            'brokers' => $t,
            'cities' =>  Constants::CITIES,
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
        if (!Gate::allows('broker_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|max:255|email',
        ]);
        try {

            if ($request->id > 0) {

                $broker = Broker::find($request->id);

                $broker->name = $request->name;
                $broker->city = $request->city;
                $broker->contact = $request->contact;
                $broker->phone = $request->phone;
                $broker->email = $request->email;
                $broker->national = $request->national;
                $broker->save();
            } else {

                $broker = Broker::create([
                    'name' => $request->name,
                    'city' => $request->city,
                    'contact' => $request->contact,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'national' => $request->national,
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('broker')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('broker_admin')) {
            abort(403);
        }
        try {

            $r = Broker::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('broker')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
