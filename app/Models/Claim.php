<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHospital;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use BelongsToHospital;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'hospital_id', 'encounter_id', 'payer', 'claim_number', 'workflow_status',
        'grouping_status', 'grouping_source', 'grouping_version', 'service_date', 'created_by',
    ];

    protected function casts(): array
    {
        return ['service_date' => 'date'];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }
}
