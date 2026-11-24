from fastapi import FastAPI

app = FastAPI(title="HCI AI Service", version="0.1.0")


@app.get("/health")
def health():
    return {"status": "ok", "service": "hci-ai", "mode": "scaffold"}


@app.get("/ready")
def ready():
    # Replace with dependency checks before deployment.
    return {"ready": False, "reason": "Dependencies and model are not configured"}
