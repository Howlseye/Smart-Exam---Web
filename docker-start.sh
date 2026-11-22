#!/bin/bash
set -e

echo "============================================================"
echo "  SmartExam - Docker Setup & Runner"
echo "============================================================"

# Check if .env exists, if not copy from .env.docker
if [ ! -f .env ]; then
    echo "[INFO] File .env tidak ditemukan. Menyalin dari .env.docker..."
    cp .env.docker .env
    echo "[OK] File .env berhasil dibuat."
else
    echo "[INFO] File .env sudah ada."
fi

echo "[INFO] Menjalankan Docker Compose..."
docker compose up -d --build

echo ""
echo "============================================================"
echo "  SmartExam Berhasil Dijalankan di Docker!"
echo "============================================================"
echo "  - Web URL       : http://localhost:8000"
echo "  - Database      : localhost:3306 (user: smartexam, pass: secret)"
echo "  - Queue Worker  : Aktif di container 'smartexam_queue'"
echo "============================================================"
echo "  Untuk melihat log: docker compose logs -f"
echo "  Untuk mematikan  : docker compose down"
echo "============================================================"

