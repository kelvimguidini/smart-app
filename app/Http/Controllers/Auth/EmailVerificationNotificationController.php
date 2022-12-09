<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at != null) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $user->sendEmailVerificationNotification();

        return Inertia::render('Auth/VerifyEmail', ['status' => session('status'), 'email' => $request->email]);
    }
}
