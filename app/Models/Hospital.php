<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'code', 'name', 'timezone', 'status'];

    public function memberships(): HasMany
    {
        return $this->hasMany(HospitalMembership::class);
    }
}
