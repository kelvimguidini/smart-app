<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PurposeHall;
use App\Models\Service;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class PurposeHallController extends Controller
{
    public function activateM($id)
    {
        if (!Gate::allows('purpose_hall_admin')) {
            abort(403);
        }
        return $this->activate($id, PurposeHall::class);
    }

    public function deactivateM($id)
    {
        if (!Gate::allows('purpose_hall_admin')) {
            abort(403);
        }
        return $this->deactivate($id, PurposeHall::class);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('purpose_hall_admin')) {
            abort(403);
        }

        $t = PurposeHall::withoutGlobalScope('active')->get();
        return Inertia::render('Auth/Auxiliaries/PurposeHall', [
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
        if (!Gate::allows('purpose_hall_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        try {

            if ($request->id > 0) {

                $obj = PurposeHall::withoutGlobalScope('active')->find($request->id);

                $obj->name = $request->name;
                $obj->save();
            } else {

                $obj = PurposeHall::create([
                    'name' => $request->name
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
        return redirect()->route('purpose-hall')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
    }

    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('purpose_hall_admin')) {
            abort(403);
        }
        try {

            $r = PurposeHall::withoutGlobalScope('active')->find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('purpose-hall')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
