<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\GroupPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
        Gate::define('check-in-out', function (User $user) {
                return $user->can('registrar_asistencia');
            });
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
