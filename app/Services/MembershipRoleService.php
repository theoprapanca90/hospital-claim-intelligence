<?php

namespace App\Services;

use App\Models\HospitalMembership;
use App\Models\Role;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya jalur yang sah untuk memberi/mencabut role pada membership.
 * Database tidak memaksa kecocokan hospital_id role vs membership, jadi
 * aturan itu ditegakkan di sini di dalam transaksi.
 *
 * Service ini TIDAK memeriksa apakah actor berhak (members.manage); pemanggil
 * (controller/policy) wajib melakukannya sebelum memanggil service ini.
 */
class MembershipRoleService
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function assign(HospitalMembership $membership, Role $role, int|string|null $actorUserId = null): void
    {
        DB::transaction(function () use ($membership, $role, $actorUserId): void {
            /** @var HospitalMembership $locked */
            $locked = HospitalMembership::query()->lockForUpdate()->findOrFail($membership->getKey());

            if (! $locked->isActive()) {
                throw new DomainException('Membership is not active.');
            }

            if (! $role->is_assignable) {
                throw new DomainException('Role is not assignable.');
            }

            if (! $role->appliesToHospital((string) $locked->hospital_id)) {
                throw new DomainException('Role scope does not match the membership hospital.');
            }

            $locked->roles()->syncWithoutDetaching([
                $role->getKey() => ['assigned_by' => $actorUserId, 'assigned_at' => now()],
            ]);

            $this->audit->record(
                'membership.role_assigned',
                (string) $locked->hospital_id,
                $actorUserId,
                $locked,
                ['role_id' => $role->getKey(), 'role_slug' => $role->slug],
            );
        });
    }

    public function revoke(HospitalMembership $membership, Role $role, int|string|null $actorUserId = null): void
    {
        DB::transaction(function () use ($membership, $role, $actorUserId): void {
            $membership->roles()->detach($role->getKey());

            $this->audit->record(
                'membership.role_revoked',
                (string) $membership->hospital_id,
                $actorUserId,
                $membership,
                ['role_id' => $role->getKey(), 'role_slug' => $role->slug],
            );
        });
    }
}
