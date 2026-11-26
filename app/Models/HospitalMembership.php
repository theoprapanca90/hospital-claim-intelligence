<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class HospitalMembership extends Model
{
    protected $table = 'hospital_memberships';

    protected $fillable = [
        'hospital_id', 'user_id', 'status', 'joined_at', 'revoked_at',
    ];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'membership_roles',
            'hospital_membership_id', 'role_id')
            ->withPivot(['assigned_by', 'assigned_at'])->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->revoked_at === null;
    }

    /**
     * Role yang scope-nya valid untuk rumah sakit membership ini. Role yang
     * tersangkut lintas rumah sakit (mis. lewat penulisan DB langsung) diabaikan.
     *
     * @return Collection<int, Role>
     */
    public function effectiveRoles(): Collection
    {
        $hospitalId = (string) $this->hospital_id;

        return $this->roles
            ->filter(fn (Role $role): bool => $role->appliesToHospital($hospitalId))
            ->values();
    }

    public function hasPermission(string $slug): bool
    {
        return $this->isActive()
            && $this->effectiveRoles()->contains(
                fn (Role $role): bool => $role->permissions->contains('slug', $slug)
            );
    }
}
