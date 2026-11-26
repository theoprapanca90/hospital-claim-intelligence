<?php

namespace Tests\Concerns;

use App\Models\Hospital;
use App\Models\HospitalMembership;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Fixture sintetis saja. Jangan memasukkan data pasien nyata ke test.
 */
trait CreatesTenancyFixtures
{
    protected function makeHospital(string $code, string $status = 'active'): Hospital
    {
        return Hospital::create([
            'id' => (string) Str::uuid(),
            'code' => $code,
            'name' => 'RS Uji '.$code,
            'status' => $status,
        ]);
    }

    protected function makeMember(Hospital $hospital, ?User $user = null, string $status = 'active', bool $revoked = false): HospitalMembership
    {
        $user ??= User::factory()->create();

        return HospitalMembership::create([
            'hospital_id' => $hospital->id,
            'user_id' => $user->id,
            'status' => $status,
            'joined_at' => now(),
            'revoked_at' => $revoked ? now() : null,
        ]);
    }

    /**
     * @param  list<string>  $permissionSlugs
     */
    protected function makeRole(string $slug, string $scope, ?Hospital $hospital, array $permissionSlugs = [], bool $assignable = true): Role
    {
        $role = Role::create([
            'hospital_id' => $hospital?->id,
            'slug' => $slug,
            'name' => $slug,
            'scope' => $scope,
            'is_assignable' => $assignable,
        ]);

        foreach ($permissionSlugs as $permissionSlug) {
            $permission = Permission::firstOrCreate(
                ['slug' => $permissionSlug],
                ['description' => $permissionSlug],
            );
            $role->permissions()->attach($permission->id);
        }

        return $role;
    }
}
