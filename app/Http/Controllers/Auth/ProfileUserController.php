<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProfileUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {;

        return Inertia::render('Auth/Profile', [
            'user' => Auth::user()
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
        if (!Gate::allows('profile_edit', $request->id)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255'
        ]);

        $userEdit = User::find($request->id);

        $userEdit->name = $request->name;
        $userEdit->email = $request->email;
        $userEdit->save();

        return redirect()->back()->with('flash', ['message' => trans('Register saved Successful'), 'type' => 'success']);
    }



    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('role_admin')) {
            abort(403);
        }
        try {

            $r = Role::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->route('role')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }



    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function permissionRemove(Request $request)
    {
        if (!Gate::allows('role_admin')) {
            abort(403);
        }
        try {
            DB::table('role_permission')->where([
                ['role_id', '=', $request->role_id],
                ['permission_id', '=', $request->permission_id],
            ])->delete();
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->route('role')->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
