<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        try {
            $permissions = Permission::all();

            foreach ($permissions as $permission) {

                Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->whereHas('roles', function ($query) use ($permission) {
                        return $query->whereHas('permissions', function ($query) use ($permission) {
                            return $query->whereName($permission->name);
                        });
                    })->count() > 0;
                });

                if ($permission->verifyId) {
                    Gate::define($permission->name . "_user", function ($user, $id) use ($permission) {
                        return $user->whereHas('roles', function ($query) use ($permission) {
                            return $query->whereHas('permissions', function ($query) use ($permission) {
                                return $query->whereName($permission->name);
                            });
                        })->count() > 0 && ((is_array($id) && in_array($user->id, $id)) || $user->id === $id);
                    });
                }
            }

            Gate::define('profile_edit', function (User $user, $id) {
                return $user->id === $id;
            });
        } catch (Exception $e) {
        }
    }
}
