<?php

namespace Tests\Unit;

use App\Models\Role;
use PHPUnit\Framework\TestCase;

class RoleScopeTest extends TestCase
{
    private function role(string $scope, ?string $hospitalId): Role
    {
        return new Role(['scope' => $scope, 'hospital_id' => $hospitalId]);
    }

    public function test_system_role_applies_to_any_hospital_only_without_hospital_id(): void
    {
        $this->assertTrue($this->role('system', null)->appliesToHospital('hospital-a'));
        $this->assertFalse($this->role('system', 'hospital-a')->appliesToHospital('hospital-a'));
    }

    public function test_hospital_role_applies_only_to_its_own_hospital(): void
    {
        $this->assertTrue($this->role('hospital', 'hospital-a')->appliesToHospital('hospital-a'));
        $this->assertFalse($this->role('hospital', 'hospital-a')->appliesToHospital('hospital-b'));
        $this->assertFalse($this->role('hospital', null)->appliesToHospital('hospital-a'));
    }

    public function test_unknown_scope_never_applies(): void
    {
        $this->assertFalse($this->role('global', null)->appliesToHospital('hospital-a'));
    }
}
