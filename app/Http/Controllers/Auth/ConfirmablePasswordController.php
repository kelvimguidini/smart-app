<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $path = base_path('public/angular.html');
        if (file_exists($path)) {
            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $request->fullUrl());
            }
            return response(file_get_contents($path) ?: '', 200, ['Content-Type' => 'text/html']);
        }
        return response()->view('app'); // Fallback to old app if angular.html not found
    }

    /**
     * Confirm the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            return response()->json([
                'errors' => [
                    'password' => [trans('auth.password')]
                ]
            ], 422);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return response()->json(['message' => 'Password confirmed']);
    }
}
