<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;
use App\Support\TenantAccess;

class ClaimPolicy
{
    public function viewAny(User $user, string $hospitalId): bool
    {
        return TenantAccess::allows($user, $hospitalId, 'claims.read');
    }

    public function view(User $user, Claim $claim): bool
    {
        return TenantAccess::allows($user, (string) $claim->hospital_id, 'claims.read');
    }
}
