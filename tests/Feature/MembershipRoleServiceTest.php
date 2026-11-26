<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\MembershipRoleService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\Concerns\CreatesTenancyFixtures;
use Tests\TestCase;

class MembershipRoleServiceTest extends TestCase
{
    use CreatesTenancyFixtures;
    use RefreshDatabase;

    private function service(): MembershipRoleService
    {
        return $this->app->make(MembershipRoleService::class);
    }

    public function test_assigns_a_role_of_the_same_hospital_and_writes_an_audit_event(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital);
        $role = $this->makeRole('reviewer', 'hospital', $hospital);
        $actor = User::factory()->create();

        $this->service()->assign($membership, $role, $actor->id);

        $this->assertTrue($membership->roles()->whereKey($role->id)->exists());
        $event = AuditEvent::query()->where('event_type', 'membership.role_assigned')->sole();
        $this->assertSame($hospital->id, $event->hospital_id);
        $this->assertSame($actor->id, $event->actor_user_id);
        $this->assertSame((string) $membership->id, $event->subject_id);
    }

    public function test_rejects_a_role_that_belongs_to_another_hospital(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $membershipB = $this->makeMember($hospitalB);
        $roleA = $this->makeRole('reviewer', 'hospital', $hospitalA);

        $this->expectException(DomainException::class);

        try {
            $this->service()->assign($membershipB, $roleA);
        } finally {
            $this->assertSame(0, $membershipB->roles()->count());
            $this->assertSame(0, AuditEvent::query()->count());
        }
    }

    public function test_accepts_a_system_role_without_hospital(): void
    {
        $membership = $this->makeMember($this->makeHospital('A'));
        $role = $this->makeRole('auditor', 'system', null);

        $this->service()->assign($membership, $role);

        $this->assertTrue($membership->roles()->whereKey($role->id)->exists());
    }

    public function test_rejects_non_assignable_role(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital);
        $role = $this->makeRole('locked', 'hospital', $hospital, assignable: false);

        $this->expectException(DomainException::class);
        $this->service()->assign($membership, $role);
    }

    public function test_rejects_assignment_to_a_revoked_membership(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital, revoked: true);
        $role = $this->makeRole('reviewer', 'hospital', $hospital);

        $this->expectException(DomainException::class);
        $this->service()->assign($membership, $role);
    }

    public function test_revoke_removes_the_role_and_is_audited(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital);
        $role = $this->makeRole('reviewer', 'hospital', $hospital);
        $this->service()->assign($membership, $role);

        $this->service()->revoke($membership, $role);

        $this->assertSame(0, $membership->roles()->count());
        $this->assertSame(1, AuditEvent::query()->where('event_type', 'membership.role_revoked')->count());
    }

    public function test_audit_events_cannot_be_updated_or_deleted(): void
    {
        $this->makeHospital('A');
        $this->service();
        $event = AuditEvent::create(['event_type' => 'test.event', 'occurred_at' => now()]);

        try {
            $event->update(['event_type' => 'tampered']);
            $this->fail('Update should have been blocked.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }

        $this->expectException(LogicException::class);
        $event->delete();
    }
}
