<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Catatan audit bersifat append-only di lapisan aplikasi. Perlindungan di
 * lapisan database (hak akses user DB tanpa UPDATE/DELETE) tetap diperlukan.
 */
class AuditEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hospital_id', 'actor_user_id', 'event_type', 'subject_type', 'subject_id',
        'request_id', 'ip_address', 'metadata', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'occurred_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Audit events are append-only and cannot be updated.');
        });

        static::deleting(function (): never {
            throw new LogicException('Audit events are append-only and cannot be deleted.');
        });
    }
}
