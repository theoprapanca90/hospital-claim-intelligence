<?php

namespace App\Models;

use App\Models\Concerns\BelongsToHospital;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Hanya menyimpan referensi ter-hash; tidak ada nama/NIK/alamat di tabel ini.
 */
class Patient extends Model
{
    use BelongsToHospital;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'hospital_id', 'external_patient_ref_hash', 'source_system',
        'medical_record_no_hash', 'identity_status',
    ];

    protected $hidden = ['external_patient_ref_hash', 'medical_record_no_hash'];

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }
}
