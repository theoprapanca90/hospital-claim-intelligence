<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Hanya katalog permission (idempotent). Tidak membuat user, role, atau
 * password. Admin rumah sakit pertama dibuat lewat perintah setup terkontrol.
 */
class PermissionSeeder extends Seeder
{
    /** @var array<string, string> */
    public const CATALOGUE = [
        'encounters.read' => 'Melihat data kunjungan',
        'documents.read' => 'Melihat dokumen klinis',
        'diagnoses.review' => 'Meninjau dan memutuskan saran diagnosis/kode',
        'claims.read' => 'Melihat klaim',
        'claims.manage' => 'Mengelola klaim',
        'audit.read' => 'Melihat jejak audit',
        'members.manage' => 'Mengelola anggota dan role rumah sakit',
    ];

    public function run(): void
    {
        foreach (self::CATALOGUE as $slug => $description) {
            Permission::updateOrCreate(['slug' => $slug], ['description' => $description]);
        }
    }
}
