<?php

namespace App\Support;

use App\Models\HospitalMembership;
use App\Models\User;

/**
 * Pemeriksaan akses tenant yang berdiri sendiri (tidak bergantung pada
 * middleware), dipakai oleh Policies sebagai lapisan kedua. Deny by default.
 */
final class TenantAccess
{
    public static function allows(User $user, string $hospitalId, string $permission): bool
    {
        $membership = HospitalMembership::query()
            ->with(['hospital', 'roles.permissions'])
            ->where('hospital_id', $hospitalId)
            ->where('user_id', $user->getKey())
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->first();

        return $membership !== null
            && $membership->hospital !== null
            && $membership->hospital->isActive()
            && $membership->hasPermission($permission);
    }
}
