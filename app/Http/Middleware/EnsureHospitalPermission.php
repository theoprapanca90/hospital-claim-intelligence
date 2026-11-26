<?php

namespace App\Http\Middleware;

use App\Models\HospitalMembership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHospitalPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $membership = $request->attributes->get('hospital_membership');

        if (! $membership instanceof HospitalMembership || ! $membership->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
