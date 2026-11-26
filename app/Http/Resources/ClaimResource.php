<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Claim
 */
class ClaimResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'encounter_id' => $this->encounter_id,
            'payer' => $this->payer,
            'claim_number' => $this->claim_number,
            'workflow_status' => $this->workflow_status,
            'grouping_status' => $this->grouping_status,
            'grouping_source' => $this->grouping_source,
            'grouping_version' => $this->grouping_version,
            'service_date' => $this->service_date?->toDateString(),
        ];
    }
}
