<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to access this resource.');
        }

        $user = Auth::user();

        // Check if user has any of the required roles
        if (! $this->hasAnyRole($user, $roles)) {
            // If it's an AJAX request, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'You do not have the required role to perform this action.',
                    'roles_required' => $roles,
                ], 403);
            }

            // For regular requests, redirect back or to dashboard with error
            return redirect()->route('dashboard')->with('error', 'You do not have permission to access this resource.');
        }

        return $next($request);
    }

    /**
     * Check if user has any of the required roles
     */
    private function hasAnyRole($user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }
}
