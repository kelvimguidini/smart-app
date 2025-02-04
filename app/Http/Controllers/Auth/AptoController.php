<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Apto;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class AptoController extends Controller
{
    public function activateM($id)
    {
        if (!Gate::allows('apto_admin')) {
            abort(403);
        }
        return $this->activate($id, Apto::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('apto_admin')) {
            abort(403);
        }
        return $this->deactivate($id, Apto::class);
    }
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('apto_admin')) {
            abort(403);
        }

        $t = Apto::withoutGlobalScope('active')->get();
        return Inertia::render('Auth/Auxiliaries/Apto', [
            'aptos' => $t
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
        if (!Gate::allows('apto_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $Apto = Apto::withoutGlobalScope('active')->find($request->id);

                $Apto->name = $request->name;
                $Apto->save();
            } else {

                $Apto = Apto::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('apto')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('apto_admin')) {
            abort(403);
        }
        try {

            $r = Apto::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('apto')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
