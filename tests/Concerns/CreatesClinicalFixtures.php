<?php

namespace Tests\Concerns;

use App\Models\Claim;
use App\Models\Encounter;
use App\Models\Hospital;
use App\Models\HospitalMembership;
use App\Models\Patient;

/**
 * Fixture sintetis. Memakai bersama CreatesTenancyFixtures.
 */
trait CreatesClinicalFixtures
{
    protected function makeEncounter(Hospital $hospital, string $type = 'outpatient'): Encounter
    {
        $patient = Patient::create([
            'hospital_id' => $hospital->id,
            'source_system' => 'synthetic',
            'external_patient_ref_hash' => hash('sha256', uniqid('p', true)),
        ]);

        return Encounter::create([
            'hospital_id' => $hospital->id,
            'patient_id' => $patient->id,
            'encounter_type' => $type,
            'status' => 'open',
            'started_at' => now()->subHour(),
            'care_unit' => 'Poli Uji',
        ]);
    }

    protected function makeClaim(Hospital $hospital, ?Encounter $encounter = null): Claim
    {
        $encounter ??= $this->makeEncounter($hospital);

        return Claim::create([
            'hospital_id' => $hospital->id,
            'encounter_id' => $encounter->id,
            'claim_number' => 'CLM-'.strtoupper(substr(md5(uniqid('', true)), 0, 8)),
            'workflow_status' => 'draft',
        ]);
    }

    protected function grantPermission(HospitalMembership $membership, string $roleSlug, string ...$permissions): void
    {
        $role = $this->makeRole($roleSlug, 'hospital', $membership->hospital, $permissions);
        $membership->roles()->attach($role->id);
    }
}
