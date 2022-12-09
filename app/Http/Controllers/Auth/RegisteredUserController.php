<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('user_admin')) {
            abort(403);
        }

        $users = User::with('roles')->get();
        $roles = Role::all();
        // $userEdit = $request->id > 0 ? User::find($request->id) : null;

        return Inertia::render('Auth/Register', [
            'users' => $users,
            'roles' => $roles,
            // 'userEdit' => $userEdit
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
        if (!Gate::allows('user_admin')) {
            abort(403);
        }


        if ($request->id > 0) {


            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255'
            ]);

            $userEdit = User::with('roles')->find($request->id);

            $userEdit->name = $request->name;
            $userEdit->email = $request->email;
            $userEdit->save();

            foreach ($userEdit->roles as $role) {
                DB::table('user_role')->where(['role_id' => $role->id, 'user_id' => $userEdit->id])->delete();
            }

            foreach ($request->roles as $role) {

                DB::table('user_role')->insert(
                    array(
                        'user_id' => $userEdit->id,
                        'role_id' => $role
                    )
                );
            }

            return redirect()->route('register')->with('flash', ['message' => trans('Register saved Successful'), 'type' => 'success']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('qwerty'),
        ]);

        // Insert some stuff
        foreach ($request->roles as $role) {

            DB::table('user_role')->insert(
                array(
                    'user_id' => $user->id,
                    'role_id' => $role
                )
            );
        }

        $user->sendEmailVerificationNotification();

        return redirect()->route('register')->with('flash', ['message' => trans('Register saved Successful'), 'type' => 'success']);
    }



    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function delete(Request $request)
    {
        if (!Gate::allows('user_admin')) {
            abort(403);
        }
        try {

            $r = User::find($request->id);

            $r->delete();
        } catch (Exception $e) {

            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }



    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function roleRemove(Request $request)
    {
        if (!Gate::allows('user_admin')) {
            abort(403);
        }
        try {
            DB::table('user_role')->where([
                ['role_id', '=', $request->role_id],
                ['user_id', '=', $request->user_id],
            ])->delete();
        } catch (Exception $e) {
            throw $e;
        }

        return redirect()->back()->with('flash', ['message' => trans('Registro apagado com sucesso!'), 'type' => 'success']);
    }
}
