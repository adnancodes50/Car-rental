<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SpatieRoleMiddlewareProxy
{
    /**
     * Handle an incoming request by delegating to Spatie's RoleMiddleware.
     *
     * This avoids constructor type-hinting so we can show a clear error if
     * the Spatie middleware class is not present.
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $spatieClass = \Spatie\Permission\Middlewares\RoleMiddleware::class;

        if (! class_exists($spatieClass)) {
            // Clear, helpful error instead of a container/DI fatal error
            abort(500, "Spatie RoleMiddleware not found. Is spatie/laravel-permission installed correctly?");
        }

        // instantiate and forward the call
        $spatie = new $spatieClass();

        return $spatie->handle($request, $next, $role);
    }
}
