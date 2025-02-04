<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Apto;
use App\Models\Purpose;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class PurposeController extends Controller
{
    public function activateM($id)
    {
        if (!Gate::allows('purpose_admin')) {
            abort(403);
        }
        return $this->activate($id, Purpose::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('purpose_admin')) {
            abort(403);
        }
        return $this->deactivate($id, Purpose::class);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('purpose_admin')) {
            abort(403);
        }

        $t = Purpose::withoutGlobalScope('active')->get();
        return Inertia::render('Auth/Auxiliaries/Purpose', [
            'purposes' => $t
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
        if (!Gate::allows('purpose_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $purpose = Purpose::withoutGlobalScope('active')->find($request->id);

                $purpose->name = $request->name;
                $purpose->save();
            } else {

                $purpose = Purpose::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('purpose')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('purpose_admin')) {
            abort(403);
        }
        try {

            $r = Purpose::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('purpose')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
