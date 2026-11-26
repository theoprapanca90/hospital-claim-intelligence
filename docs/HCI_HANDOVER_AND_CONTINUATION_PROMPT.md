# HCI — Handover Summary & Continuation Prompt

Tanggal pembaruan: 26 November 2026

Dokumen ini menjadi titik serah-terima untuk melanjutkan pengembangan Hospital Claim Intelligence (HCI) pada percakapan atau sesi coding berikutnya. Gunakan fakta repository sebagai sumber status implementasi; jangan menganggap rencana sebagai fitur yang sudah selesai.

## A. Ringkasan proyek

**Nama:** Hospital Claim Intelligence (HCI)  
**Repository:** https://github.com/theoprapanca90/hospital-claim-intelligence  
**Branch kerja:** `feature/laravel-api-auth-tenancy`  
**PR aktif yang diketahui:** https://github.com/theoprapanca90/hospital-claim-intelligence/pull/2  
**Fokus PR #2:** Laravel 12 API authentication dan hospital tenancy scaffold.

HCI adalah sistem pendukung abstraksi rekam medis, clinical context, asistensi koding, dan proses review klaim rumah sakit. Sistem ditujukan untuk arsitektur hybrid on-premise/cloud, dengan prinsip on-premise-first dan offline-first.

### Keputusan produk dan arsitektur

- Integrasi beberapa SIMRS melalui API; Khanza dan GHOST merupakan target integrasi awal berdasarkan informasi yang diberikan pemilik proyek. Kontrak API tetap harus diperiksa dan divalidasi.
- Riwayat pasien dan clinical context menjadi bagian dari kebutuhan sistem.
- Pemrosesan AI menggunakan pendekatan hybrid.
- Output yang diinginkan mencakup hasil terstruktur dan penjelasan/konteks.
- Sumber knowledge utama adalah pengetahuan/dokumen rumah sakit yang disetujui.
- RAG dan fine-tuning pernah dipilih sebagai pendekatan yang akan dievaluasi, bukan fitur yang sudah tersedia.
- Keputusan diagnosis, koding, dan klaim wajib melalui human review.
- Data klinis teridentifikasi tetap berada di lingkungan rumah sakit secara default.
- Workflow inti harus tetap dapat berjalan saat internet atau cloud tidak tersedia.

### Stack yang dituju

| Komponen | Teknologi |
|---|---|
| Application/API | Laravel 12, PHP 8.2 |
| Relational database | MySQL |
| AI service | Python FastAPI |
| Local model runtime | Ollama |
| Vector database | Qdrant |
| Integrasi | SIMRS API adapters |
| Development saat ini | Mac Apple M1, RAM 8 GB, SSD 256 GB |
| Deployment tujuan | Linux on-premise / hybrid |

## B. Status repository yang diketahui

PR #2 memiliki scaffold awal berikut:
- Laravel bootstrap, konfigurasi, Composer manifest, entry points, dan routing.
- Sanctum trait pada model User dan migration personal access tokens.
- Model Hospital, HospitalMembership, Role, Permission.
- Middleware ResolveHospitalMembership dan EnsureHospitalPermission.
- Endpoint terautentikasi `GET /api/v1/me`.
- Endpoint konteks rumah sakit `GET /api/v1/hospitals/{hospital}/context`.
- Migration dasar users/cache/jobs dan token.
- Test scaffold untuk autentikasi.
- Dokumentasi status bootstrap dan prosedur runtime foundation.

Dokumentasi penting:
- `README.md`
- `docs/PROJECT_SUMMARY.md`
- `docs/architecture/laravel-bootstrap-status.md`
- `docs/setup/laravel-runtime-foundation.md`

**Batas status:** repository secara eksplisit menyebut scaffold belum diverifikasi runtime. Composer install, package discovery, migration execution, automated tests, tenant isolation tests, FastAPI integration, Qdrant/RAG, Ollama model evaluation, SIMRS adapters, BPJS rules/grouper, backup, offline test, security review, dan deployment belum boleh diklaim selesai tanpa bukti baru.

PR #2 diketahui masih draft/open pada konteks sebelumnya. Periksa ulang status PR dan branch sebelum membuat perubahan.

## C. Kondisi development lokal

Perangkat pengguna:
- Apple M1.
- RAM 8 GB.
- SSD internal 256 GB.
- macOS 27.2.1, build 26B131 (sesuai informasi yang diberikan pengguna).

Strategi resource yang direkomendasikan, belum dikonfirmasi berjalan:
- Laravel native di macOS.
- FastAPI native di macOS saat diperlukan.
- MySQL dan Qdrant melalui Docker.
- Ollama native macOS, dijalankan hanya saat pengujian AI.
- Batasi Docker Desktop sekitar 3 GB sebagai titik awal; MySQL sekitar 768 MB dan Qdrant sekitar 512 MB termasuk dalam batas Docker tersebut.
- Gunakan data sintetis/anonim untuk development.
- Jangan menjalankan seluruh layanan/model sekaligus pada RAM 8 GB.
- Konfigurasi Docker Compose sebelumnya masih berupa rancangan; periksa file repository terlebih dahulu, gunakan image versi terpin, dan jangan menimpa konfigurasi yang sudah ada.

Perintah verifikasi perangkat yang diminta tetapi hasilnya belum diterima:
```bash
uname -m
php -v
docker --version
```

## D. Prioritas kerja selanjutnya

Kerjakan secara bertahap, dengan urutan:

1. **Audit repository dan PR terkini**
   - Periksa branch, status PR, file tree, README, dokumentasi, dan perubahan terbaru.
   - Jangan mengasumsikan ringkasan ini lebih baru daripada repository.
   - Catat file yang ada, file yang hilang, dan potensi konflik.

2. **Siapkan environment Mac M1**
   - Verifikasi arsitektur `arm64`, PHP 8.2+, Composer 2, Docker Desktop, dan resource.
   - Lengkapi dokumentasi setup yang dapat direproduksi.
   - Tambahkan Docker Compose hanya setelah memeriksa konfigurasi existing.
   - Pastikan secret berada di file lokal yang di-ignore Git.

3. **Lengkapi dan verifikasi Laravel runtime**
   - Bandingkan scaffold dengan Laravel 12 skeleton yang valid.
   - Instal dependency hanya setelah struktur dan manifest diperiksa.
   - Verifikasi Sanctum dan pastikan hanya ada satu migration personal access tokens.
   - Jalankan migration pada database disposable.
   - Jalankan lint dan test; simpan output hasil aktual.

4. **Perkuat authentication, authorization, dan tenancy**
   - Tambahkan resource Policies.
   - Pastikan setiap query resource dibatasi tenant/hospital.
   - Validasi assignment role dan scope.
   - Buat automated tests untuk akses antar-rumah-sakit, user nonaktif, membership dicabut, role scope, dan permission.
   - Jangan menganggap middleware route-level yang ada sudah cukup melindungi data klinis.

5. **Audit trail dan workflow**
   - Rancang audit event untuk login, akses data, perubahan, saran AI, koreksi petugas, dan keputusan akhir.
   - Catat actor, tenant, action, resource, timestamp, source, serta metadata yang diperlukan tanpa menyalin data sensitif secara berlebihan.

6. **Integration Gateway SIMRS**
   - Mulai dari discovery dokumentasi dan kontrak API Khanza/GHOST.
   - Implementasi read-only lebih dahulu.
   - Validasi autentikasi, pagination, rate limits, mapping, timeout, retry, idempotency, dan provenance.
   - Gunakan data uji yang disetujui; jangan terhubung ke produksi sebelum security review.

7. **FastAPI dan AI foundation**
   - Tetapkan API contract Laravel ↔ FastAPI.
   - Tambahkan health check, schema validation, timeout, retry, error handling, dan logging aman.
   - Pisahkan clinical data processing dari layanan eksternal.

8. **Qdrant dan RAG**
   - Buat pipeline ingestion dokumen knowledge rumah sakit yang disetujui.
   - Simpan metadata sumber, pemilik, versi, tanggal berlaku, dan hak akses.
   - Terapkan tenant/access filtering saat retrieval.
   - Buat evaluation dataset dan ukur retrieval quality sebelum dipakai pada workflow klinis.

9. **Clinical Context dan Coding Assistant**
   - Definisikan data contract dan provenance.
   - Tampilkan evidence/source untuk setiap rekomendasi.
   - Jangan menghasilkan keputusan final otomatis.
   - Uji dengan dataset validasi yang ditinjau tenaga kompeten.

10. **BPJS Rules Management**
    - Gunakan hanya regulasi, pedoman, dan dokumen resmi yang masih berlaku dan dapat diverifikasi.
    - Catat versi, tanggal berlaku, sumber, approval, dan histori perubahan.
    - Jangan mengarang tarif, rule, grouper, atau endpoint resmi.

11. **Deployment Linux dan pilot**
    - Buat backup/restore, monitoring, secret management, update strategy, offline behavior, incident response, dan security checklist.
    - Pilot hanya setelah seluruh test keamanan, isolasi tenant, dan validasi pengguna terpenuhi.

## E. Aturan kerja wajib untuk AI coding assistant

1. Mulai dengan memeriksa repository/branch/file yang aktual. Jangan langsung menulis kode berdasarkan asumsi.
2. Jangan mengarang file, commit, hasil test, status PR, dokumentasi API, atau fitur.
3. Sebelum mengubah file, baca isi dan pahami dependensinya.
4. Buat perubahan kecil, terfokus, dan dapat direview; hindari rewrite besar tanpa alasan.
5. Jangan merge PR atau mengubah branch default tanpa instruksi eksplisit.
6. Jangan menaruh credential, token, PHI, atau data pasien ke repository, log, prompt eksternal, maupun contoh dokumentasi.
7. Gunakan synthetic/de-identified data untuk pengujian.
8. Semua perubahan harus disertai dokumentasi dan test yang relevan.
9. Jalankan test/lint yang tersedia. Jika tidak dapat dijalankan, sebutkan alasan dan tandai sebagai belum diverifikasi.
10. Pisahkan status menjadi: **implemented**, **tested**, **verified**, **blocked**, dan **planned**.
11. Untuk perubahan regulasi/aturan BPJS atau standar klinis, cari sumber resmi terkini dan cantumkan sumber serta tanggal berlakunya.
12. Jangan menyatakan AI sebagai pengambil keputusan klinis/klaim. Hasil AI adalah rekomendasi yang memerlukan persetujuan manusia.

## F. Prompt lanjutan — salin untuk sesi pengembangan berikutnya

```text
Kita melanjutkan pengembangan Hospital Claim Intelligence (HCI).

Repository:
https://github.com/theoprapanca90/hospital-claim-intelligence

Branch kerja yang diketahui:
feature/laravel-api-auth-tenancy

PR yang diketahui:
https://github.com/theoprapanca90/hospital-claim-intelligence/pull/2

Baca terlebih dahulu dokumen:
- README.md
- docs/PROJECT_SUMMARY.md
- docs/architecture/laravel-bootstrap-status.md
- docs/setup/laravel-runtime-foundation.md
- docs/HCI_HANDOVER_AND_CONTINUATION_PROMPT.md

KONTEKS PROYEK:
HCI adalah sistem pendukung abstraksi rekam medis, clinical context, asistensi koding, dan workflow review klaim rumah sakit. Arsitektur hybrid, on-premise-first, dan offline-first. Stack target Laravel 12/PHP 8.2, MySQL, Python FastAPI, Ollama, dan Qdrant. Integrasi multi-SIMRS dengan Khanza dan GHOST sebagai target awal. Data klinis teridentifikasi tetap lokal secara default. Semua rekomendasi AI wajib melalui human review.

KONDISI DEVELOPMENT:
Pengembangan awal dilakukan pada Mac Apple M1, RAM 8 GB, SSD 256 GB, macOS 27.2.1. Rencana awal adalah Laravel dan FastAPI native, MySQL dan Qdrant melalui Docker, serta Ollama native dan dijalankan saat diperlukan. Verifikasi PHP, Composer, Docker, dan arsitektur mesin harus dilakukan sebelum menyimpulkan environment siap.

TUGAS PERTAMA:
1. Audit status repository, branch, PR, file tree, dan dokumentasi terkini.
2. Bandingkan kondisi aktual dengan dokumen handover ini.
3. Identifikasi pekerjaan yang sudah implemented, sudah tested, sudah verified, belum dikerjakan, atau blocked.
4. Laporkan temuan dan rekomendasikan satu langkah teknis berikutnya yang paling aman.
5. Jangan mengubah file sebelum audit awal selesai dan rencana perubahan dijelaskan.

ATURAN:
- Jangan mengarang commit, file, test result, fitur, API, atau status PR.
- Baca file sebelum mengubahnya.
- Buat perubahan kecil dan terfokus, dokumentasikan, lalu jalankan test yang relevan.
- Jangan merge PR atau mengubah branch default tanpa izin.
- Jangan gunakan data pasien nyata untuk development; gunakan data sintetis/anonim.
- Jangan mengirim PHI ke cloud secara default.
- Terapkan tenant isolation pada semua akses resource sensitif.
- Jangan menganggap middleware route-level sebagai pengganti Policies dan tenant-scoped queries.
- Jangan mengasumsikan aturan BPJS, tarif, grouper, atau endpoint resmi; verifikasi melalui sumber resmi terkini.
- AI hanya memberikan rekomendasi; keputusan akhir dilakukan petugas berwenang.
- Jika test tidak dapat dijalankan, jelaskan hambatannya dan jangan klaim berhasil.

Mulai dengan audit repository dan berikan laporan faktual sebelum coding.
```

## G. Definisi selesai untuk setiap tahap

Setiap tahap baru dianggap selesai jika:
- Perubahan kode dan dokumentasi tercatat.
- Test yang relevan dijalankan dan hasil aktual dilaporkan.
- Risiko keamanan dan dampak tenant isolation diperiksa.
- Tidak ada secret atau data pasien yang ikut ter-commit.
- PR/commit dan daftar file berubah dapat ditunjukkan.
- Hal yang belum diuji tetap ditandai secara eksplisit.

---
**Prinsip utama HCI: akurasi dan keselamatan data lebih penting daripada kecepatan menambah fitur.**
