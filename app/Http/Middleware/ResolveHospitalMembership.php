<?php

namespace App\\Http\\Middleware;

use App\\Models\\HospitalMembership;
use Closure;
use Illuminate\\Http\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

class ResolveHospitalMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $hospitalId = $request->route('hospital');

        if (!$user || !is_string($hospitalId)) {
            abort(404);
        }

        $membership = HospitalMembership::query()
            ->with(['hospital', 'roles.permissions'])
            ->where('hospital_id', $hospitalId)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->first();

        if (!$membership) {
            abort(404);
        }

        $request->attributes->set('hospital_membership', $membership);
        $request->attributes->set('hospital', $membership->hospital);

        return $next($request);
    }
}
