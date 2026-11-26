<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospital extends Model
{
    // Tabel hospitals memiliki deleted_at; tanpa trait ini rumah sakit yang
    // sudah di-soft-delete tetap dapat di-resolve oleh middleware tenancy.
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'code', 'name', 'timezone', 'status'];

    public function memberships(): HasMany
    {
        return $this->hasMany(HospitalMembership::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->trashed();
    }
}
