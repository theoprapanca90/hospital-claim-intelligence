# HCI — Rangkuman Proyek dan Inventaris File

> Dokumen ini merangkum kondisi repository `theoprapanca90/hospital-claim-intelligence` berdasarkan file yang tercatat pada PR #2 di branch `feature/laravel-api-auth-tenancy`, dokumentasi README, dan file implementasi yang diperiksa pada 26 November 2026. Ini adalah ringkasan kondisi repository, bukan pernyataan bahwa seluruh sistem telah selesai atau lulus pengujian.

## 1. Ringkasan eksekutif

**Hospital Claim Intelligence (HCI)** dirancang sebagai platform pendukung proses abstraksi rekam medis, pemahaman konteks klinis, asistensi koding, dan pemeriksaan klaim rumah sakit. Sistem dirancang untuk berjalan secara **hybrid, on-premise-first, dan offline-first**.

Tujuan arsitektur:
- Mengintegrasikan lebih dari satu SIMRS melalui adapter API.
- Menyediakan konteks klinis dan riwayat pasien untuk membantu proses abstraksi.
- Memanfaatkan AI lokal maupun layanan terkontrol untuk pencarian pengetahuan dan saran.
- Menyediakan manajemen aturan dan workflow klaim.
- Menjaga keputusan diagnosis, kode, dan klaim tetap berada pada petugas/tenaga berwenang melalui human review.

**Status saat ini: fondasi awal / scaffold.** Repository belum dapat dianggap sebagai aplikasi HCI siap pakai, belum terverifikasi berjalan, dan belum boleh dihubungkan ke SIMRS produksi.

## 2. Keputusan arsitektur utama

| Area | Keputusan / arah desain |
|---|---|
| Deployment | Hybrid, dengan layanan inti dapat ditempatkan on-premise |
| Batas data | Data klinis teridentifikasi tetap lokal secara default |
| Ketersediaan | Workflow inti harus tetap dapat digunakan saat internet/cloud terputus |
| Backend aplikasi | Laravel 12, PHP 8.2 |
| Database operasional | MySQL |
| AI service | Python FastAPI |
| Model lokal | Ollama (opsional; model belum dipilih dan dievaluasi) |
| Vector database | Qdrant untuk retrieval knowledge yang disetujui |
| Integrasi SIMRS | Multi-SIMRS; Khanza dan GHOST menjadi target awal, kontrak API wajib divalidasi |
| AI decision | AI memberi rekomendasi; keputusan akhir wajib ditinjau manusia |
| Keamanan | RBAC, hospital membership, tenant isolation, audit trail, dan kontrol akses per resource |
| Terminologi | Simpan provenance sumber, versi terminologi, dan tanggal berlaku aturan |

Tidak boleh mengasumsikan aturan BPJS, tarif, grouper resmi, atau endpoint integrasi resmi tanpa dokumen sumber yang masih berlaku dan dapat diverifikasi.

## 3. Komponen sistem yang direncanakan

1. **Laravel Application/API** — autentikasi, organisasi rumah sakit, membership, role/permission, workflow, audit, dan human review.
2. **Integration Gateway** — adapter per SIMRS, validasi kontrak API, pemetaan data, antrean sinkronisasi, retry, dan pencatatan provenance.
3. **Clinical Context Service** — menyusun ringkasan riwayat dan konteks klinis dari data yang diizinkan.
4. **AI Processing Service (FastAPI)** — orkestrasi ekstraksi, retrieval, dan inferensi.
5. **Knowledge/RAG Layer (Qdrant)** — indeks pengetahuan rumah sakit yang telah disetujui, dengan metadata sumber dan versi.
6. **Local Model Runtime (Ollama)** — opsi inferensi lokal; model dan kualitasnya masih harus dievaluasi.
7. **BPJS Rules Management** — pengelolaan aturan dengan sumber, versi, tanggal efektif, dan proses persetujuan; belum diimplementasikan/ diverifikasi.
8. **Coding & Claim Review Workflow** — saran AI, bukti pendukung, koreksi petugas, keputusan akhir, dan audit.

## 4. Ringkasan implementasi yang ada pada PR #2

PR #2 berjudul **Laravel 12 API Auth and Hospital Tenancy Scaffold**. Perubahan yang terdaftar mencakup 39 file. Kelompok implementasinya:

### 4.1 Fondasi Laravel dan konfigurasi
- `composer.json`: menetapkan PHP ^8.2, Laravel ^12, Sanctum ^4, Tinker, serta dependency pengujian dan formatting.
- `artisan`, `public/index.php`, `bootstrap/app.php`, `bootstrap/providers.php`: entry point dan bootstrap Laravel.
- `config/*.php`: konfigurasi aplikasi, auth, cache, database, filesystem, logging, mail, queue, services, dan session.
- `routes/web.php`, `routes/console.php`, `routes/api.php`: kerangka routing.
- `.env.example`: contoh konfigurasi aplikasi, MySQL, Qdrant, Ollama, dan URL AI service. Nilai kredensial di dalamnya adalah placeholder, bukan kredensial produksi.

### 4.2 Autentikasi API
- `app/Models/User.php`: model user menggunakan `HasApiTokens`, `HasFactory`, dan `Notifiable`; password disembunyikan dan di-hash melalui cast.
- `database/migrations/2026_11_25_000005_create_personal_access_tokens_table.php`: tabel token personal untuk Sanctum.
- `routes/api.php`: endpoint terautentikasi `GET /api/v1/me`, yang mengembalikan ID, nama, dan email user yang sedang login.

### 4.3 Multi-hospital tenancy dan RBAC awal
- `app/Models/Hospital.php`: entitas rumah sakit dengan ID string dan relasi membership.
- `app/Models/HospitalMembership.php`: relasi user dengan rumah sakit, status keanggotaan, waktu bergabung/pencabutan, serta relasi role.
- `app/Models/Role.php`: role dengan scope system atau hospital dan relasi permission.
- `app/Models/Permission.php`: katalog permission berbasis slug.
- `app/Http/Middleware/ResolveHospitalMembership.php`: memastikan user memiliki membership aktif pada rumah sakit yang diminta; rumah sakit atau membership tidak aktif ditolak.
- `app/Http/Middleware/EnsureHospitalPermission.php`: memeriksa permission slug serta kesesuaian scope role.
- `bootstrap/app.php`: mendaftarkan alias middleware `hospital.member` dan `hospital.permission`.
- `routes/api.php`: endpoint `GET /api/v1/hospitals/{hospital}/context` untuk mengembalikan konteks rumah sakit dan membership user.

Pemeriksaan middleware ini baru **gerbang akses tingkat route**. Ini belum cukup untuk melindungi data pasien/klaim. Resource Policies, query yang selalu dibatasi tenant, validasi input, audit event, dan pengujian isolasi tenant tetap wajib dibuat.

### 4.4 Database dan testing scaffold
- Migration standar: users, cache, jobs, dan personal access tokens.
- `database/factories/UserFactory.php`, `database/seeders/DatabaseSeeder.php`: fondasi factory dan seeder.
- `tests/TestCase.php`, `tests/Feature/ExampleTest.php`, `tests/Feature/ApiAuthenticationTest.php`, `phpunit.xml`: kerangka pengujian, termasuk skenario unauthenticated pada endpoint profil.
- `app/Providers/AppServiceProvider.php`: provider aplikasi awal.

Keberadaan file test tidak berarti test sudah dijalankan atau lulus.

### 4.5 Dokumentasi
- `docs/architecture/laravel-bootstrap-status.md`: menjelaskan cakupan scaffold, middleware, keterbatasan, dan pekerjaan sebelum runtime.
- `docs/setup/laravel-runtime-foundation.md`: prosedur aman untuk melengkapi skeleton Laravel, memasang Sanctum, mengatur MySQL, dan checklist verifikasi.
- `README.md`: baseline arsitektur, batas keamanan, langkah awal, dan status implementasi.

## 5. Inventaris file PR #2

### Environment dan root
- `.env.example`
- `README.md`
- `artisan`
- `composer.json`
- `phpunit.xml`

### Application, middleware, model, provider
- `app/Http/Middleware/EnsureHospitalPermission.php`
- `app/Http/Middleware/ResolveHospitalMembership.php`
- `app/Models/Hospital.php`
- `app/Models/HospitalMembership.php`
- `app/Models/Permission.php`
- `app/Models/Role.php`
- `app/Models/User.php`
- `app/Providers/AppServiceProvider.php`

### Bootstrap, public, routes
- `bootstrap/app.php`
- `bootstrap/providers.php`
- `public/index.php`
- `routes/api.php`
- `routes/console.php`
- `routes/web.php`

### Config
- `config/app.php`
- `config/auth.php`
- `config/cache.php`
- `config/database.php`
- `config/filesystems.php`
- `config/logging.php`
- `config/mail.php`
- `config/queue.php`
- `config/services.php`
- `config/session.php`

### Database
- `database/factories/UserFactory.php`
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/0001_01_01_000001_create_cache_table.php`
- `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `database/migrations/2026_11_25_000005_create_personal_access_tokens_table.php`
- `database/seeders/DatabaseSeeder.php`

### Dokumentasi dan tests
- `docs/architecture/laravel-bootstrap-status.md`
- `docs/setup/laravel-runtime-foundation.md`
- `tests/Feature/ApiAuthenticationTest.php`
- `tests/Feature/ExampleTest.php`
- `tests/TestCase.php`

## 6. Status implementasi: tersedia vs belum

| Area | Status berdasarkan repository yang diperiksa |
|---|---|
| Laravel 12 scaffold | Sebagian file runtime sudah ditambahkan; kelengkapan runtime belum diverifikasi |
| Sanctum | Trait dan migration token ada; instalasi/package discovery belum diverifikasi |
| API profile | Route `/api/v1/me` tersedia pada kode; belum dibuktikan berjalan |
| Hospital membership | Model dan middleware awal tersedia |
| RBAC | Model role/permission dan middleware awal tersedia; manajemen assignment belum lengkap |
| Tenant isolation | Belum cukup; Policies dan tenant-scoped query belum tersedia/terverifikasi |
| MySQL schema | Migration dasar tersedia; belum dijalankan |
| FastAPI | Disebut sebagai komponen arsitektur; integrasi belum diuji |
| Qdrant/RAG | URL konfigurasi dicontohkan; collection dan pipeline belum dibuat |
| Ollama | URL konfigurasi dicontohkan; model belum dipilih/dievaluasi |
| SIMRS Khanza/GHOST | Target integrasi; adapter belum dibuat |
| BPJS rules/grouper | Belum diimplementasikan atau diverifikasi |
| Audit trail | Kebutuhan arsitektur; implementasi belum dibuktikan |
| Backup, offline, security review | Belum dilakukan |
| Deployment Linux/pilot | Belum dilakukan |

## 7. Batasan keamanan dan tata kelola

- Jangan commit file `.env`, password, token, API key, data pasien, atau catatan klinis.
- Gunakan data sintetis atau data yang telah dianonimkan untuk development.
- Data klinis teridentifikasi tidak dikirim ke cloud secara default.
- Setiap akses data harus memeriksa identitas, hospital membership, permission, dan otorisasi resource.
- Terapkan audit trail untuk akses, perubahan data, saran AI, koreksi, dan keputusan akhir.
- Catat sumber, versi, tanggal berlaku, dan status persetujuan setiap aturan/knowledge.
- Kegagalan AI atau koneksi internet tidak boleh menghentikan proses manual.
- Jangan menganggap Sanctum token abilities sebagai pengganti otorisasi tenant dan resource.

## 8. Prioritas pekerjaan berikutnya

1. **Verifikasi environment Mac M1**: PHP 8.2+, Composer 2, Docker Desktop, dan resource memory.
2. **Lengkapi dan audit Laravel skeleton**: bandingkan file dengan skeleton resmi Laravel 12; selesaikan konflik sebelum Composer install.
3. **Verifikasi Sanctum**: package discovery, migration tunggal, token expiry/revocation, dan policy abilities.
4. **Verifikasi database**: jalankan migration pada database disposable; uji rollback dan constraint.
5. **Perkuat tenancy**: resource Policies, tenant-scoped query/repository, assignment validation, dan test lintas rumah sakit.
6. **Bangun audit trail**: actor, tenant, action, resource, timestamp, source, dan perubahan yang relevan.
7. **Rancang Integration Gateway**: dokumentasikan kontrak Khanza/GHOST; mulai read-only dengan data uji.
8. **Bangun FastAPI service**: kontrak API, validasi, timeout, retry, logging tanpa data sensitif, dan health check.
9. **Bangun RAG secara terkontrol**: sumber knowledge rumah sakit yang disetujui, metadata provenance, versioning, dan evaluasi retrieval.
10. **Rancang BPJS Rules Management**: hanya dari dokumen resmi yang tervalidasi; simpan effective date dan histori perubahan.
11. **Uji end-to-end**: keamanan, tenant isolation, offline, backup/restore, performa, dan human review.
12. **Deployment Linux dan pilot**: setelah seluruh gate keamanan dan pengujian terpenuhi.

## 9. Kriteria minimum sebelum pilot

- [ ] Composer install dan package discovery berhasil.
- [ ] Seluruh migration dan rollback diuji pada database uji.
- [ ] Login/token, expiry, revocation, dan unauthorized response diuji.
- [ ] User rumah sakit A tidak dapat membaca atau mengubah data rumah sakit B.
- [ ] Policies dan tenant-scoped query diterapkan pada semua resource sensitif.
- [ ] Audit trail dapat ditelusuri.
- [ ] Integrasi SIMRS diuji dengan kontrak dan data non-produksi.
- [ ] Tidak ada data pasien atau secret di Git/log.
- [ ] Workflow manual tetap berjalan saat AI/internet tidak tersedia.
- [ ] Output AI selalu berstatus rekomendasi sampai disetujui petugas.
- [ ] Backup/restore, monitoring, incident response, dan security review dilakukan.

## 10. Catatan verifikasi

Pada saat ringkasan ini dibuat, file yang diperiksa berasal dari branch `feature/laravel-api-auth-tenancy` dan daftar file yang berubah pada PR #2. Dokumen status di repository secara eksplisit menyebut runtime installation, migration execution, dan automated tests masih pending. Karena itu, ringkasan ini membedakan **file yang tersedia** dari **fitur yang sudah terbukti berjalan**.

Referensi kerja:
- Repository: https://github.com/theoprapanca90/hospital-claim-intelligence
- PR #2: https://github.com/theoprapanca90/hospital-claim-intelligence/pull/2
- Branch: `feature/laravel-api-auth-tenancy`
