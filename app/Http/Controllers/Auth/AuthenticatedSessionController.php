<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Domains\Auth\Repositories\UserRepositoryInterface;

class AuthenticatedSessionController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $user = $this->userRepository->findByEmail($request->email);

        if ($user == null) {
            return response()->json([
                'message' => 'E-mail não encontrado!', 
                'type' => 'danger', 
                'email' => $request->email
            ], 401);
        }

        if ($user->type === 'api' || (isset($user->is_api_user) && $user->is_api_user)) {
            Auth::logout();
            return response()->json([
                'message' => 'Usuário exclusivo da API não pode acessar o painel web.', 
                'type' => 'danger', 
                'email' => $request->email
            ], 403);
        }

        if ($user->email_verified_at == null) {
            return response()->json([
                'message' => 'E-mail não verificado.', 
                'type' => 'warning', 
                'email' => $request->email,
                'needs_verification' => true
            ], 403);
        }

        $response = $request->authenticate();
        if ($response === true) {
            return response()->json([
                'message' => trans('auth.failed'), 
                'type' => 'danger', 
                'email' => $request->email
            ], 401);
        }

        $request->session()->regenerate();

        if ($request->wantsJson()) {
            return response()->json(['user' => Auth::user(), 'redirect' => RouteServiceProvider::HOME]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
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
