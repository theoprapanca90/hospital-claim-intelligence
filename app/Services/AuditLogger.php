<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function __construct(private readonly ?Request $request = null)
    {
    }

    /**
     * Catat satu kejadian audit.
     *
     * $metadata TIDAK BOLEH berisi data pasien, isi dokumen klinis, token,
     * atau password. Isi hanya identifier, status, dan alasan teknis.
     *
     * @param  array<string, scalar|array<array-key, scalar|null>|null>  $metadata
     */
    public function record(
        string $eventType,
        ?string $hospitalId = null,
        int|string|null $actorUserId = null,
        ?Model $subject = null,
        array $metadata = [],
    ): AuditEvent {
        return AuditEvent::create([
            'hospital_id' => $hospitalId,
            'actor_user_id' => $actorUserId,
            'event_type' => $eventType,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject !== null ? (string) $subject->getKey() : null,
            'request_id' => $this->request?->headers->get('X-Request-Id'),
            'ip_address' => $this->request?->ip(),
            'metadata' => $metadata === [] ? null : $metadata,
            'occurred_at' => now(),
        ]);
    }
}
