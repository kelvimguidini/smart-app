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
        ['name' => "role_admin", "title" => "Administrar Grupos de Acesso"],
        ['name' => "customer_admin", "title" => "Administrar Clientes"],
        ['name' => "crd_admin", "title" => "Administrar CRD's"],
        ['name' => "event_admin", "title" => "Administrar Eventos"],
        ['name' => "event_operator", "title" => "Operador do Evento", "verifyId" => true]
    ];
}
