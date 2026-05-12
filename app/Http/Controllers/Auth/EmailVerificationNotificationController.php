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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at != null) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Email already verified.']);
            }
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $user->sendEmailVerificationNotification();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'verification-link-sent']);
        }

        return redirect()->back()->with('status', 'verification-link-sent');
    }
}
