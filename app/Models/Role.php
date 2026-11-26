<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['hospital_id', 'slug', 'name', 'scope', 'description', 'is_assignable'];

    protected function casts(): array
    {
        return ['is_assignable' => 'boolean'];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')->withTimestamps();
    }

    /**
     * Satu-satunya aturan scope role. Role sistem tidak boleh terikat ke
     * rumah sakit; role rumah sakit hanya berlaku di rumah sakit pemiliknya.
     * Scope tak dikenal selalu ditolak (deny by default).
     */
    public function appliesToHospital(string $hospitalId): bool
    {
        return match ($this->scope) {
            'system' => $this->hospital_id === null,
            'hospital' => $this->hospital_id !== null
                && (string) $this->hospital_id === $hospitalId,
            default => false,
        };
    }
}
