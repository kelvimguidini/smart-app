<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

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

            if ($request->id > 0) {

                $role = Role::find($request->id);

                $role->name = $request->name;
                $role->active = $request->active;

                $role->save();

                DB::table('role_permission')->where([
                    ['role_id', '=', $request->id]
                ])->delete();
            } else {

                $role = Role::create([
                    'name' => $request->name,
                    'active' => true
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }

        // Insert some stuff
        foreach ($request->permissions as $permission) {

            DB::table('role_permission')->insert(
                array(
                    'permission_id' => DB::table('permission')->select('id')->where('name', $permission)->first()->id,
                    'role_id' => $role->id
                )
            );
        }

        return redirect()->route('role')->with('flash', ['message' => trans('Registro salvo com sucesso'), 'type' => 'success']);
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
