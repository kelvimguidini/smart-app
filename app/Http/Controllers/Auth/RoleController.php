<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        return Inertia::render('Auth/Role');
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

        // $user = Role::create([
        //     'name' => $request->name,
        //     'actve' => true
        // ]);



        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');

        // if ($status == Password::RESET_LINK_SENT) {
        //     return back()->with('status', __($status));
        // }

        // throw ValidationException::withMessages([
        //     'email' => [trans($status)],
        // ]);
    }
}
