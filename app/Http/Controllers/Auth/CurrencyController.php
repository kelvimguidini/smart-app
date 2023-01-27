<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Apto;
use App\Models\Currency;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class CurrencyController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('currency_admin')) {
            abort(403);
        }

        $t = Currency::all();
        return Inertia::render('Auth/Auxiliaries/Currency', [
            'currencies' => $t
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
        if (!Gate::allows('currency_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $currency = Currency::find($request->id);

                $currency->name = $request->name;
                $currency->symbol = $request->symbol;
                $currency->save();
            } else {

                $currency = Currency::create([
                    'name' => $request->name,
                    'symbol' => $request->symbol
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('currency')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('currency_admin')) {
            abort(403);
        }
        try {

            $r = Currency::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('currency')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
