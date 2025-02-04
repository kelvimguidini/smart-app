<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Regime;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class RegimeController extends Controller
{
    public function activateM($id)
    {
        if (!Gate::allows('regime_admin')) {
            abort(403);
        }
        return $this->activate($id, Regime::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('regime_admin')) {
            abort(403);
        }
        return $this->deactivate($id, Regime::class);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('regime_admin')) {
            abort(403);
        }

        $t = Regime::withoutGlobalScope('active')->get();
        return Inertia::render('Auth/Auxiliaries/Regime', [
            'regimes' => $t
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
        if (!Gate::allows('regime_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $regime = Regime::withoutGlobalScope('active')->find($request->id);

                $regime->name = $request->name;
                $regime->save();
            } else {

                $regime = Regime::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('regime')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('regime_admin')) {
            abort(403);
        }
        try {

            $r = Regime::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('regime')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
