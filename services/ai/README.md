# HCI AI Service

FastAPI service. Saat ini hanya **kontrak dan pengamanan dasar**; belum ada model, RAG, terminologi, atau rekomendasi kode.

## Endpoint
| Method | Path | Auth | Keterangan |
|---|---|---|---|
| GET | `/health` | tidak | Liveness; tidak menyentuh dependency |
| GET | `/ready` | tidak | 200 bila token layanan terkonfigurasi, 503 bila tidak. Qdrant/Ollama hanya menandai `degraded` (workflow manual tidak boleh bergantung pada AI) |
| POST | `/v1/abstraction/suggest` | `X-Service-Token` | Kontrak placeholder; selalu `status: "unavailable"` + `requires_human_review: true` |

## Aturan keamanan yang sudah ditegakkan
- Fail closed: tanpa `AI_SERVICE_TOKEN` (minimal 32 karakter) endpoint kerja menjawab 503.
- Token dibandingkan dengan `hmac.compare_digest`.
- Body request tidak pernah masuk log; log hanya method, path, status, durasi, request id.
- Error validasi tidak mengulang input yang ditolak (bisa berisi teks klinis).
- Skema request menolak field tak dikenal dan membatasi `document_text` maksimal 20.000 karakter.
- Output selalu bertipe rekomendasi (`requires_human_review` selalu `true`).

## Menjalankan
```bash
export AI_SERVICE_TOKEN="$(openssl rand -hex 32)"
python -m venv .venv && . .venv/bin/activate
pip install -r requirements-dev.txt
python -m pytest
uvicorn app.main:app --port 8001
```

## Belum ada
Autentikasi per-pengguna/tenant, rate limit, TLS antar-layanan, integrasi Ollama/Qdrant yang sebenarnya, evaluasi model.
