<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesTenancyFixtures;
use Tests\TestCase;

class HospitalTenancyTest extends TestCase
{
    use CreatesTenancyFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Route uji untuk gate permission (belum ada resource nyata).
        Route::middleware(['api', 'auth:sanctum', 'hospital.member', 'hospital.permission:claims.read'])
            ->get('/api/_test/hospitals/{hospital}/claims', fn () => response()->json(['ok' => true]));
    }

    public function test_context_requires_authentication(): void
    {
        $hospital = $this->makeHospital('A');

        $this->getJson("/api/v1/hospitals/{$hospital->id}/context")->assertUnauthorized();
    }

    public function test_active_member_can_read_own_hospital_context(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital);
        $role = $this->makeRole('reviewer', 'hospital', $hospital, ['claims.read']);
        $membership->roles()->attach($role->id);

        Sanctum::actingAs($membership->user);

        $this->getJson("/api/v1/hospitals/{$hospital->id}/context")
            ->assertOk()
            ->assertJsonPath('data.hospital.code', 'A')
            ->assertJsonPath('data.membership.roles.0', 'reviewer');
    }

    public function test_user_cannot_access_a_hospital_they_do_not_belong_to(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $memberOfA = $this->makeMember($hospitalA);

        Sanctum::actingAs($memberOfA->user);

        $this->getJson("/api/v1/hospitals/{$hospitalB->id}/context")->assertNotFound();
    }

    public function test_unknown_hospital_id_is_indistinguishable_from_forbidden_hospital(): void
    {
        $hospitalA = $this->makeHospital('A');
        $member = $this->makeMember($hospitalA);
        Sanctum::actingAs($member->user);

        $this->getJson('/api/v1/hospitals/00000000-0000-0000-0000-000000000000/context')->assertNotFound();
    }

    public function test_revoked_membership_is_denied(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital, revoked: true);

        Sanctum::actingAs($membership->user);

        $this->getJson("/api/v1/hospitals/{$hospital->id}/context")->assertNotFound();
    }

    public function test_suspended_membership_is_denied(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital, status: 'suspended');

        Sanctum::actingAs($membership->user);

        $this->getJson("/api/v1/hospitals/{$hospital->id}/context")->assertNotFound();
    }

    public function test_inactive_hospital_is_denied(): void
    {
        $hospital = $this->makeHospital('A', status: 'suspended');
        $membership = $this->makeMember($hospital);

        Sanctum::actingAs($membership->user);

        $this->getJson("/api/v1/hospitals/{$hospital->id}/context")->assertNotFound();
    }

    public function test_soft_deleted_hospital_is_denied(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital);
        $hospital->delete();

        Sanctum::actingAs($membership->user);

        $this->getJson("/api/v1/hospitals/{$hospital->id}/context")->assertNotFound();
    }

    public function test_permission_is_granted_only_in_the_hospital_where_the_role_lives(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $user = User::factory()->create();

        $memberA = $this->makeMember($hospitalA, $user);
        $this->makeMember($hospitalB, $user);
        $memberA->roles()->attach($this->makeRole('reviewer', 'hospital', $hospitalA, ['claims.read'])->id);

        Sanctum::actingAs($user);

        $this->getJson("/api/_test/hospitals/{$hospitalA->id}/claims")->assertOk();
        // Anggota RS B tanpa role di RS B: 403 (anggota sah, tidak punya permission).
        $this->getJson("/api/_test/hospitals/{$hospitalB->id}/claims")->assertForbidden();
    }

    public function test_member_without_the_required_permission_gets_403(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital);
        $membership->roles()->attach($this->makeRole('viewer', 'hospital', $hospital, ['encounters.read'])->id);

        Sanctum::actingAs($membership->user);

        $this->getJson("/api/_test/hospitals/{$hospital->id}/claims")->assertForbidden();
    }

    public function test_hospital_role_of_another_hospital_attached_directly_grants_nothing(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $memberB = $this->makeMember($hospitalB);

        // Simulasi penulisan DB langsung yang melewati service: role RS A di membership RS B.
        $foreignRole = $this->makeRole('reviewer', 'hospital', $hospitalA, ['claims.read']);
        $memberB->roles()->attach($foreignRole->id);

        Sanctum::actingAs($memberB->user);

        $this->getJson("/api/_test/hospitals/{$hospitalB->id}/claims")->assertForbidden();
        $this->getJson("/api/v1/hospitals/{$hospitalB->id}/context")
            ->assertOk()
            ->assertJsonPath('data.membership.roles', []);
    }

    public function test_system_role_grants_permission_in_member_hospitals(): void
    {
        $hospital = $this->makeHospital('A');
        $membership = $this->makeMember($hospital);
        $membership->roles()->attach($this->makeRole('auditor', 'system', null, ['claims.read'])->id);

        Sanctum::actingAs($membership->user);

        $this->getJson("/api/_test/hospitals/{$hospital->id}/claims")->assertOk();
    }

    public function test_system_role_does_not_bypass_membership(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $memberA = $this->makeMember($hospitalA);
        $memberA->roles()->attach($this->makeRole('auditor', 'system', null, ['claims.read'])->id);

        Sanctum::actingAs($memberA->user);

        $this->getJson("/api/_test/hospitals/{$hospitalB->id}/claims")->assertNotFound();
    }
}
