<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Only allow users with the admin role to continue.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $this->isAdmin($user)) {
            return response()->json([
                'message' => 'Forbidden. Admin access required.',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Adjust this check to match how your User model stores roles:
     *
     *  - Spatie laravel-permission:  return $user->hasRole('admin');
     *  - users.role column:          return $user->role === 'admin';
     *  - users.is_admin column:      return (bool) $user->is_admin;
     */
    private function isAdmin($user): bool
    {
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin');
        }

        if (isset($user->role)) {
            return $user->role === 'admin';
        }

        return (bool) ($user->is_admin ?? false);
    }
}
