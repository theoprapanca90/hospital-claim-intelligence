<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHospitalPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $membership = $request->attributes->get('hospital_membership');

        if (!$membership || !$membership->isActive()) {
            abort(403);
        }

        $hospitalId = (string) $membership->hospital_id;

        $allowed = $membership->roles->contains(function ($role) use ($hospitalId, $permission): bool {
            $validScope = match ($role->scope) {
                'system' => $role->hospital_id === null,
                'hospital' => (string) $role->hospital_id === $hospitalId,
                default => false,
            };

            return $validScope && $role->permissions->contains('slug', $permission);
        });

        if (!$allowed) {
            abort(403);
        }

        return $next($request);
    }
}
