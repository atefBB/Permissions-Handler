<?php

namespace PermissionsHandler\Middleware;

use Closure;
use PermissionsHandler;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param array                    $roles
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $requireAll = false)
    {
        if (PermissionsHandler::isExcludedRoute($request)) {
            return $next($request);
        }

        $user = auth()->user();
        $roles = explode('|', $roles);
        $requireAll = (boolean) $requireAll;
        $canGo = $user->hasRole($roles, $requireAll);

        if (! $canGo) {
            $redirectTo = config('permissionsHandler.redirectUrl');
            if ($redirectTo) {
                return redirect($redirectTo);
            }

            return abort(403);
        }

        return $next($request);
    }
}
