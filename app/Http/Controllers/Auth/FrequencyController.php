<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Frequency;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class FrequencyController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('frequency_admin')) {
            abort(403);
        }

        $t = Frequency::all();
        return Inertia::render('Auth/Auxiliaries/Frequency', [
            'frequencies' => $t
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
        if (!Gate::allows('frequency_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $obj = Frequency::find($request->id);

                $obj->name = $request->name;
                $obj->save();
            } else {

                $obj = Frequency::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('frequency')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('frequency_admin')) {
            abort(403);
        }
        try {

            $r = Frequency::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('frequency')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
