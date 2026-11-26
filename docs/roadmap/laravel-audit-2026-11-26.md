# Laravel Skeleton & Tenancy Audit — 2026-11-26

Scope: branch `feature/laravel-api-auth-tenancy`. Audit ini **statis**: sesi audit tidak dapat
menjalankan `composer install` (egress ke packagist diblokir) maupun MySQL. Semua file PHP lolos
`php -l` (PHP 8.3.6) dan `composer validate`, tetapi **tidak ada test yang pernah dijalankan**.

## Temuan dan perbaikan pada commit ini

| # | Temuan | Dampak | Status |
|---|---|---|---|
| 1 | `bootstrap/cache/` dan `storage/**` tidak ada di repo | `composer install` (package:discover) dan kompilasi view gagal | Diperbaiki (placeholder .gitignore) |
| 2 | `.gitignore` memuat `logs/` dan `data/` tanpa jangkar | Akan menyembunyikan `storage/logs/` dan `storage/framework/cache/data/` | Diperbaiki (`/logs/`, `/data/`) |
| 3 | `routes/web.php` memanggil view `welcome`, tetapi `resources/views` tidak ada | `ExampleTest` gagal | Diperbaiki (view minimal) |
| 4 | `tests/Unit` ada di phpunit.xml tetapi folder tidak ada | Peringatan/gagal suite Unit | Diperbaiki (`RoleScopeTest`) |
| 5 | `config/sanctum.php` tidak ada → token tidak pernah kedaluwarsa | Token curian berlaku selamanya | Diperbaiki (default 480 menit, via `SANCTUM_EXPIRATION`) |
| 6 | Model `Hospital` tanpa `SoftDeletes` padahal tabel punya `deleted_at` | RS yang sudah dihapus tetap bisa di-resolve | Diperbaiki + test |
| 7 | `DatabaseSeeder` membuat `test@example.com` / password `password` | Melanggar aturan "tanpa akun default" | Diperbaiki (hanya katalog permission) |
| 8 | `audit_events.subject_id` bertipe uuid, padahal subjek membership/role bigint | Audit role/membership tidak bisa tersimpan | Diperbaiki (string 64) |
| 9 | Dokumen menyebut 403 untuk non-anggota, kode mengembalikan 404 | Dokumen dan perilaku tidak konsisten | Dokumen disesuaikan ke 404 (anti-enumerasi) |
| 10 | Aturan scope role terduplikasi di middleware dan route | Risiko divergensi | Dipusatkan di `Role::appliesToHospital()` |

## Ditambahkan (belum diverifikasi runtime)
- `AuditEvent` (append-only di lapisan aplikasi), `AuditLogger`.
- `MembershipRoleService`: assign/revoke role dalam transaksi, menolak role lintas RS, role non-assignable, dan membership tidak aktif; ditulis ke audit.
- `PermissionSeeder`: katalog permission idempotent.
- Test: `HospitalTenancyTest`, `MembershipRoleServiceTest`, `RoleScopeTest`.

## Lapisan baca-saja klinis (belum diverifikasi runtime)
- Model `Patient`, `Encounter`, `Claim` (UUID, soft delete) dengan `scopeForHospital()`.
- `TenantAccess` + `EncounterPolicy` / `ClaimPolicy`: pemeriksaan membership, permission, dan RS aktif secara mandiri (lapisan kedua di luar middleware).
- Endpoint: `GET /api/v1/hospitals/{hospital}/encounters[/{id}]` (`encounters.read`) dan `.../claims[/{id}]` (`claims.read`). Lookup memakai `forHospital()` sebelum record di-load; resource RS lain mengembalikan 404.
- Response encounter tanpa identitas pasien; akses list/show tercatat di `audit_events` tanpa data klinis.
- Test: `ClinicalReadApiTest` (isolasi lintas RS, 403 tanpa permission, soft delete, audit, policy, batas `per_page`).

## Temuan yang belum diperbaiki (wajib sebelum data pasien)
1. **Unik role sistem**: `UNIQUE(hospital_id, slug)` tidak mencegah slug ganda untuk role sistem karena MySQL memperlakukan NULL sebagai berbeda. Usulan: kolom generated `coalesce(hospital_id,'system')` + unique index; uji pada MySQL 8.4 sebelum dipakai.
2. **Integritas lintas tabel**: `claims`, `clinical_documents`, `encounter_diagnoses`, dll. menyimpan `hospital_id` dan `encounter_id` terpisah tanpa constraint bahwa keduanya milik RS yang sama. Perlu FK komposit atau validasi di service.
3. **Audit append-only di DB**: hanya ditegakkan di aplikasi; butuh user DB tanpa UPDATE/DELETE pada `audit_events`.
4. **Policies untuk aksi tulis** (create/update/review) belum ada; baru baca-saja untuk Encounter dan Claim. Dokumen klinis, diagnosis, dan procedure belum punya model/endpoint.
5. **Perintah setup admin RS pertama** (teraudit) belum ada.
6. **Throttling/rate limit** pada route API belum dikonfigurasi.
7. Test berjalan di SQLite in-memory (`phpunit.xml`); jalankan juga terhadap MySQL 8.4.

## Perintah verifikasi di mesin dengan akses Packagist
```bash
cp .env.example .env            # lalu isi password lokal
composer install
php artisan key:generate
docker compose up -d mysql
php artisan migrate:fresh --seed
php artisan migrate:rollback && php artisan migrate
php artisan test
vendor/bin/pint --test
```
