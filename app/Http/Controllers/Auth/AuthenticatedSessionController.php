<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use PharIo\Manifest\Email;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'flash' => ['message' => session('status'), 'type' => 'warning'],
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        if ($user == null) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'E-mail não encontrado!', 'type' => 'danger'], 401);
            }
            return Inertia::render('Auth/Login', [
                'canResetPassword' => Route::has('password.request'),
                'flash' => ['message' => 'E-mail não encontrado!', 'type' => 'danger'],
                'email' => $request->email
            ]);
        }

        if ($user && $user->is_api_user) {
            Auth::logout();
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Usuário exclusivo da API não pode acessar o painel web.', 'type' => 'danger'], 403);
            }
            return Inertia::render('Auth/Login', [
                'canResetPassword' => Route::has('password.request'),
                'flash' => ['message' => 'Usuário exclusivo da API não pode acessar o painel web.', 'type' => 'danger'],
                'email' => $request->email
            ]);
        }

        if ($user->email_verified_at == null) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'E-mail não verificado.', 'type' => 'warning', 'needs_verification' => true], 403);
            }
            return Inertia::render(
                'Auth/VerifyEmail',
                ['flash' => ['message' => session('status'), 'type' => 'warning'], 'email' => $request->email],
            );
        }


        $response = $request->authenticate();
        if ($response === true) {
            if ($request->wantsJson()) {
                return response()->json(['message' => trans('auth.failed'), 'type' => 'danger'], 401);
            }
            return Inertia::render('Auth/Login', [
                'canResetPassword' => Route::has('password.request'),
                'flash' => ['message' => trans('auth.failed'), 'type' => 'danger'],
                'email' => $request->email
            ]);
        }

        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json(['user' => Auth::user(), 'redirect' => RouteServiceProvider::HOME]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect('/');
    }
}
