<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{


    private $token;
    private $email;

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $hash = $request->hash;

        $user = User::where('id', $request->id)->first();



        if ($user->email_verified_at != null) {
            return redirect()->route('login')->with('status',  'Conta já ativada, caso não se lembra da senha, use link abaixo');
        }

        // if($user->){
        //     return redirect()->route('login')->with('status',  'Link inválido!');
        // }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $status = Password::sendResetLink(
            ['email' => $user->email],
            function ($user, $token) {
                $this->token =  $token;
                $this->email = $user->email;
            }
        );
        if ($status == Password::RESET_LINK_SENT) {
            return redirect()->route('password.reset', ['token' => $this->token, 'email' => $this->email, 'verified' => 1]);
        }

        return redirect()->route('login')->with('status', [trans($status)]);
    }
}
