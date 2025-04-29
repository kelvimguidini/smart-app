<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;
use App\Http\Middleware\Constants;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed[]
     */
    public function share(Request $request)
    {
        $flash = $request->session()->get('flash');
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
                'permissions' =>  $request->user() != null ? $request->user()->getPermissions() : null
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'appName' => env('APP_NAME'),
            'permissionList' => Constants::PERMISSIONS,
            'flash' => $flash,
            'error' => $request->session()->get('error'), // Compartilha erros genéricos
        ]);
    }
}
