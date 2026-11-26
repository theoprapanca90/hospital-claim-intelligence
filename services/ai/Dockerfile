FROM python:3.12-slim
WORKDIR /app
RUN useradd --system --no-create-home --uid 10001 hci
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY app ./app
USER hci
EXPOSE 8001
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001"]
