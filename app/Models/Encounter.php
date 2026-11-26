<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHospital;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encounter extends Model
{
    use BelongsToHospital;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'hospital_id', 'patient_id', 'source_system', 'source_encounter_ref',
        'encounter_type', 'status', 'started_at', 'ended_at', 'care_unit',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
