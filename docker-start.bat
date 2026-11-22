@echo off
echo ============================================================
echo   SmartExam - Docker Setup & Runner
echo ============================================================

REM Check if .env exists, if not copy from .env.docker
if not exist .env (
    echo [INFO] File .env tidak ditemukan. Menyalin dari .env.docker...
    copy .env.docker .env
    echo [OK] File .env berhasil dibuat.
) else (
    echo [INFO] File .env sudah ada.
)

echo [INFO] Menjalankan Docker Compose...
docker compose up -d --build

if %ERRORLEVEL% equ 0 (
    echo.
    echo ============================================================
    echo   SmartExam Berhasil Dijalankan di Docker!
    echo ============================================================
    echo   - Web URL       : http://localhost:8000
    echo   - Database      : localhost:3306 (user: smartexam, pass: secret)
    echo   - Queue Worker  : Aktif di container 'smartexam_queue'
    echo ============================================================
    echo   Untuk melihat log: docker compose logs -f
    echo   Untuk mematikan  : docker compose down
    echo ============================================================
) else (
    echo.
    echo [ERROR] Gagal menjalankan container. Pastikan Docker Desktop sudah aktif!
)
pause

