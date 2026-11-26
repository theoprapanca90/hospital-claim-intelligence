<?php

namespace App\Policies;

use App\Models\Encounter;
use App\Models\User;
use App\Support\TenantAccess;

class EncounterPolicy
{
    public function viewAny(User $user, string $hospitalId): bool
    {
        return TenantAccess::allows($user, $hospitalId, 'encounters.read');
    }

    public function view(User $user, Encounter $encounter): bool
    {
        return TenantAccess::allows($user, (string) $encounter->hospital_id, 'encounters.read');
    }
}
