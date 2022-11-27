<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Constants extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var Array
     */
    const PERMISSIONS = [
        ['name' => "user_admin", "title" => "Administrar Usuários"],
        ['name' => "role_admin", "title" => "Administrar Perfil"]
    ];
}
