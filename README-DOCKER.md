# Panduan Menjalankan SmartExam dengan Docker

Konfigurasi Docker ini dibuat agar project **SmartExam** dapat dipindahkan dan dijalankan di komputer/device lain dengan mudah tanpa perlu install PHP, Composer, Node.js, atau MySQL secara manual.

---

## 📦 Layanan Container yang Disediakan

| Layanan | Container Name | Fungsi | Port Host |
|---|---|---|---|
| **Web Server** | `smartexam_webserver` | Nginx reverse proxy untuk Laravel | `8000` (`http://localhost:8000`) |
| **Aplikasi** | `smartexam_app` | PHP 8.3 FPM + Ekstensi Lengkap | Internal (9000) |
| **Database** | `smartexam_db` | MySQL 8.0 (Auto-import database awal) | `3306` |
| **Queue Worker** | `smartexam_queue` | Background worker untuk koreksi esai AI | Internal |

---

## 🚀 Cara Pindah ke Device Lain

### Langkah 1: Pindahkan Folder Project
Salin (copy/zip) seluruh folder project `smartexam` ini ke device yang baru.
*(Folder `node_modules` dan `.git` tidak wajib disalin karena Docker akan mem-build secara otomatis).*

### Langkah 2: Pastikan Docker Terpasang di Device Baru
Pastikan device baru sudah terinstall:
- **Docker Desktop** (untuk Windows / macOS) atau **Docker Engine + Docker Compose** (untuk Linux).
- Pastikan Docker Daemon dalam status **Running**.

### Langkah 3: Jalankan Project

#### Di Windows:
Cukup klik ganda (double-click) file:
```cmd
docker-start.bat
```
Atau buka PowerShell / Command Prompt di folder project, lalu jalankan:
```bash
docker compose up -d --build
```

#### Di Linux / macOS:
Buka Terminal di folder project, lalu jalankan:
```bash
chmod +x docker-start.sh
./docker-start.sh
```
Atau langsung:
```bash
docker compose up -d --build
```

---

## 🌐 Akses Aplikasi

Setelah container aktif:
- **Aplikasi Web**: Buka browser ke [http://localhost:8000](http://localhost:8000)
- **Database MySQL**:
  - Host: `localhost` (port `3306`)
  - Database: `smartexam`
  - Username: `smartexam`
  - Password: `secret`
  - Root Password: `root`

> [!NOTE]
> Semua data soal, opsi jawaban, log penilaian AI, dan akun pengguna dari database lokal Anda telah otomatis di-export ke dalam `docker/mysql/init/01-smartexam.sql`. Saat container MySQL pertama kali dijalankan, data ini akan langsung diimport secara otomatis.

---

## 🛠️ Perintah Berguna Lainnya

- **Melihat log aplikasi & queue**:
  ```bash
  docker compose logs -f
  ```
- **Melihat status queue worker AI**:
  ```bash
  docker compose logs -f queue
  ```
- **Menjalankan perintah Artisan di dalam container**:
  ```bash
  docker compose exec app php artisan <perintah>
  ```
- **Masuk ke dalam terminal container**:
  ```bash
  docker compose exec app sh
  ```
- **Menghentikan container**:
  ```bash
  docker compose down
  ```
- **Menghentikan dan menghapus volume database (reset data)**:
  ```bash
  docker compose down -v
  ```

