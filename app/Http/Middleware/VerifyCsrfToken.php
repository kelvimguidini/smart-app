<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'https://smart4bts.com.br',
        'sanctum/csrf-cookie',
    ];

    /**
     * Determine if the session and input CSRF tokens match.
     * Desativa a verificação de CSRF no ambiente de desenvolvimento local.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        if (app()->environment('local')) {
            return true;
        }

        return parent::tokensMatch($request);
    }
}
