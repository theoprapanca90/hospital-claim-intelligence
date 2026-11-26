<?php

namespace App\Models\Concerns;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Untuk model yang memiliki kolom hospital_id. Semua pencarian resource
 * hospital-scoped wajib melewati scopeForHospital() SEBELUM data di-load,
 * bukan memeriksa kepemilikan setelah record diambil.
 */
trait BelongsToHospital
{
    public function scopeForHospital(Builder $query, string $hospitalId): Builder
    {
        return $query->where($this->qualifyColumn('hospital_id'), $hospitalId);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
}
