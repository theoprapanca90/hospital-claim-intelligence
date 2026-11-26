<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Claim;
use App\Models\Encounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesClinicalFixtures;
use Tests\Concerns\CreatesTenancyFixtures;
use Tests\TestCase;

class ClinicalReadApiTest extends TestCase
{
    use CreatesClinicalFixtures;
    use CreatesTenancyFixtures;
    use RefreshDatabase;

    public function test_endpoints_require_authentication(): void
    {
        $hospital = $this->makeHospital('A');

        $this->getJson("/api/v1/hospitals/{$hospital->id}/encounters")->assertUnauthorized();
        $this->getJson("/api/v1/hospitals/{$hospital->id}/claims")->assertUnauthorized();
    }

    public function test_encounter_list_contains_only_the_requested_hospital(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $member = $this->makeMember($hospitalA);
        $this->grantPermission($member, 'reader', 'encounters.read');
        $mine = $this->makeEncounter($hospitalA);
        $this->makeEncounter($hospitalB);

        Sanctum::actingAs($member->user);

        $response = $this->getJson("/api/v1/hospitals/{$hospitalA->id}/encounters")->assertOk();

        $this->assertSame([$mine->id], collect($response->json('data'))->pluck('id')->all());
    }

    public function test_encounter_response_exposes_no_patient_identifiers(): void
    {
        $hospital = $this->makeHospital('A');
        $member = $this->makeMember($hospital);
        $this->grantPermission($member, 'reader', 'encounters.read');
        $encounter = $this->makeEncounter($hospital);

        Sanctum::actingAs($member->user);

        $json = $this->getJson("/api/v1/hospitals/{$hospital->id}/encounters/{$encounter->id}")
            ->assertOk()
            ->json('data');

        $this->assertSame(
            ['care_unit', 'encounter_type', 'ended_at', 'id', 'source_system', 'started_at', 'status'],
            collect(array_keys($json))->sort()->values()->all(),
        );
    }

    public function test_cannot_read_another_hospitals_encounter_through_own_hospital_route(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $member = $this->makeMember($hospitalA);
        $this->grantPermission($member, 'reader', 'encounters.read');
        $foreign = $this->makeEncounter($hospitalB);

        Sanctum::actingAs($member->user);

        $this->getJson("/api/v1/hospitals/{$hospitalA->id}/encounters/{$foreign->id}")->assertNotFound();
    }

    public function test_cannot_read_encounters_of_a_hospital_without_membership(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $member = $this->makeMember($hospitalA);
        $this->grantPermission($member, 'reader', 'encounters.read');
        $foreign = $this->makeEncounter($hospitalB);

        Sanctum::actingAs($member->user);

        $this->getJson("/api/v1/hospitals/{$hospitalB->id}/encounters")->assertNotFound();
        $this->getJson("/api/v1/hospitals/{$hospitalB->id}/encounters/{$foreign->id}")->assertNotFound();
    }

    public function test_member_without_permission_gets_403(): void
    {
        $hospital = $this->makeHospital('A');
        $member = $this->makeMember($hospital);
        $this->grantPermission($member, 'claims-only', 'claims.read');
        $encounter = $this->makeEncounter($hospital);

        Sanctum::actingAs($member->user);

        $this->getJson("/api/v1/hospitals/{$hospital->id}/encounters")->assertForbidden();
        $this->getJson("/api/v1/hospitals/{$hospital->id}/encounters/{$encounter->id}")->assertForbidden();
    }

    public function test_soft_deleted_encounter_is_not_found(): void
    {
        $hospital = $this->makeHospital('A');
        $member = $this->makeMember($hospital);
        $this->grantPermission($member, 'reader', 'encounters.read');
        $encounter = $this->makeEncounter($hospital);
        $encounter->delete();

        Sanctum::actingAs($member->user);

        $this->getJson("/api/v1/hospitals/{$hospital->id}/encounters/{$encounter->id}")->assertNotFound();
    }

    public function test_viewing_an_encounter_is_audited_without_clinical_data(): void
    {
        $hospital = $this->makeHospital('A');
        $member = $this->makeMember($hospital);
        $this->grantPermission($member, 'reader', 'encounters.read');
        $encounter = $this->makeEncounter($hospital);

        Sanctum::actingAs($member->user);
        $this->getJson("/api/v1/hospitals/{$hospital->id}/encounters/{$encounter->id}")->assertOk();

        $event = AuditEvent::query()->where('event_type', 'encounter.viewed')->sole();
        $this->assertSame($hospital->id, $event->hospital_id);
        $this->assertSame($member->user_id, $event->actor_user_id);
        $this->assertSame($encounter->id, $event->subject_id);
        $this->assertNull($event->metadata);
    }

    public function test_policy_denies_cross_tenant_view_even_without_middleware(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $member = $this->makeMember($hospitalA);
        $this->grantPermission($member, 'reader', 'encounters.read', 'claims.read');
        $encounterA = $this->makeEncounter($hospitalA);
        $encounterB = $this->makeEncounter($hospitalB);
        $claimB = $this->makeClaim($hospitalB);

        $gate = Gate::forUser($member->user);

        $this->assertTrue($gate->allows('view', $encounterA));
        $this->assertTrue($gate->denies('view', $encounterB));
        $this->assertTrue($gate->denies('view', $claimB));
        $this->assertTrue($gate->denies('viewAny', [Encounter::class, $hospitalB->id]));
    }

    public function test_policy_denies_after_membership_is_revoked(): void
    {
        $hospital = $this->makeHospital('A');
        $member = $this->makeMember($hospital);
        $this->grantPermission($member, 'reader', 'encounters.read');
        $encounter = $this->makeEncounter($hospital);

        $member->update(['revoked_at' => now()]);

        $this->assertTrue(Gate::forUser($member->user)->denies('view', $encounter));
    }

    public function test_claim_list_and_show_are_tenant_scoped(): void
    {
        $hospitalA = $this->makeHospital('A');
        $hospitalB = $this->makeHospital('B');
        $member = $this->makeMember($hospitalA);
        $this->grantPermission($member, 'reader', 'claims.read');
        $mine = $this->makeClaim($hospitalA);
        $foreign = $this->makeClaim($hospitalB);

        Sanctum::actingAs($member->user);

        $list = $this->getJson("/api/v1/hospitals/{$hospitalA->id}/claims")->assertOk();
        $this->assertSame([$mine->id], collect($list->json('data'))->pluck('id')->all());

        $this->getJson("/api/v1/hospitals/{$hospitalA->id}/claims/{$mine->id}")
            ->assertOk()
            ->assertJsonPath('data.workflow_status', 'draft');
        $this->getJson("/api/v1/hospitals/{$hospitalA->id}/claims/{$foreign->id}")->assertNotFound();
        $this->assertSame(1, Claim::query()->forHospital($hospitalA->id)->count());
    }

    public function test_per_page_is_capped(): void
    {
        $hospital = $this->makeHospital('A');
        $member = $this->makeMember($hospital);
        $this->grantPermission($member, 'reader', 'encounters.read');
        $this->makeEncounter($hospital);
        $this->makeEncounter($hospital);

        Sanctum::actingAs($member->user);

        $this->getJson("/api/v1/hospitals/{$hospital->id}/encounters?per_page=1")
            ->assertOk()
            ->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/hospitals/{$hospital->id}/encounters?per_page=100000")->assertOk();
    }
}
