<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sengaja tanpa identitas pasien (hash, MRN, nama). Tambah field hanya lewat
 * keputusan eksplisit dan review keamanan.
 *
 * @mixin \App\Models\Encounter
 */
class EncounterResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'encounter_type' => $this->encounter_type,
            'status' => $this->status,
            'started_at' => $this->started_at?->toIso8601String(),
            'ended_at' => $this->ended_at?->toIso8601String(),
            'care_unit' => $this->care_unit,
            'source_system' => $this->source_system,
        ];
    }
}
