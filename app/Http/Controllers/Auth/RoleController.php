<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Providers\RouteServiceProvider;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if (!Gate::allows('role_admin')) {
            abort(403);
        }

        $r = Role::with('permissions')->get();

        return Inertia::render('Auth/Role', [
            'roles' => $r,
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
        if (!Gate::allows('role_admin')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required',
        ]);
        $r = Role::with('permissions')->get();
        try {
            $role = Role::create([
                'name' => $request->name,
                'active' => true
            ]);

            // Insert some stuff
            foreach ($request->permissions as $permission) {

                DB::table('role_permission')->insert(
                    array(
                        'permission_id' => DB::table('permission')->select('id')->where('name', $permission)->first()->id,
                        'role_id' => $role->id
                    )
                );
            }
        } catch (Exception $e) {
            redirect()->back()->with('flash', ['message' => trans($e->message), 'type' => 'danger']);
        }

        return redirect()->route('role')->with('flash', ['message' => trans('Register saved Successful'), 'type' => 'success']);
    }
}
