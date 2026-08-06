# SIPPAKADIS

**Sistem Pemantauan Pajak Kendaraan Dinas** — memantau jatuh tempo PKB & STNK kendaraan dinas di lingkungan Pemerintah Daerah.

## Link & Akun Uji Coba

| | |
|---|---|
| Produksi | https://sipakadis.vercel.app |
| Email | `admin@sippakadis.test` |
| Password | `password` |

## Fitur Utama

- **Dashboard & Monitoring** — status jatuh tempo PKB/STNK (LEWAT, HARI_H, H1, H7, H14, H30, AMAN), notifikasi otomatis per kendaraan/OPD.
- **Data Kendaraan** — daftar, filter (OPD/status/monitoring), detail, dan **edit manual** Masa Berlaku PKB/STNK. Status dihitung ulang otomatis saat disimpan.
- **Sinkronisasi Simpator (manual)** — scraping data pajak dari Simpator Bapenda Kaltim:
  - **Manual per NOPOL**: centang kendaraan lalu "Sinkronisasi Terpilih" (ada juga "Pilih semua di halaman ini").
  - **Massal batch**: "Jalankan Sekarang" memproses 100 kendaraan (prioritas yang belum pernah diskrap).
- **Cron harian** — menghitung ulang seluruh status + membangun notifikasi (tanpa scraping Simpator).
- **Peran & Workflow** — Admin dan OPD (pengajuan penetapan, perubahan status, verifikasi).

## Teknologi

- Laravel 12, Blade + Tailwind CSS 4, PostgreSQL (Supabase)
- Hosting: Vercel (vercel-php) + GitHub Actions
- Koneksi DB produksi via **Supavisor pooler IPv4** (host `aws-0-ap-southeast-1.pooler.supabase.com`)

## Menjalankan Lokal

```bash
composer install
cp .env.example .env        # isi DB_* dengan koneksi Supabase (pooler)
php artisan key:generate
php artisan migrate --seed  # migrasi + data awal
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Deploy ke Vercel

1. Fork/push repo ke GitHub.
2. Buat project Vercel dari repo (framework: **Other**; `vercel.json` sudah berisi konfigurasi `builds` vercel-php).
3. Set **Environment Variables** (production):

   ```
   APP_KEY             = base64:...   # php artisan key:generate --show
   APP_DEBUG           = false
   APP_URL             = https://<nama-project>.vercel.app
   DB_CONNECTION       = pgsql
   DB_HOST             = aws-0-ap-southeast-1.pooler.supabase.com
   DB_PORT             = 5432
   DB_DATABASE         = postgres
   DB_USERNAME         = postgres.<ref>      # dari Supabase > Connect > Pooler
   DB_PASSWORD         = <password>
   DB_SSLMODE          = require
   CRON_TOKEN          = <string acak>
   ```

4. Deploy otomatis setiap push ke `main` via `.github/workflows/deploy.yml`. Secret GitHub yang dibutuhkan: `VERCEL_TOKEN`, `PROD_URL`, `CRON_TOKEN`.

> Catatan: host Supabase direct (`db.*.supabase.co`) kadang **IPv6-only** sehingga tidak bisa diakses Lambda Vercel (IPv4-only). Gunakan **pooler Supavisor** (IPv4) pada koneksi produksi & lokal.

## Cron Harian

Tidak ada cron OS di Vercel, sehingga scheduler dieksekusi lewat endpoint yang dipanggil GitHub Actions setiap hari (`.github/workflows/cron-daily.yml`):

```bash
curl -X POST https://sipakadis.vercel.app/cron/daily \
  -H "Authorization: Bearer <CRON_TOKEN>"
```

Menghitung ulang seluruh status (≈1961 kendaraan) dalam ±25 detik. Jadwal scheduler lokal tercatat di `routes/console.php` (22:30 WITA).

## Struktur Penting

```
app/Http/Controllers/CronController.php     # endpoint cron (status + notifikasi)
app/Services/MonitoringService.php          # hitungSemuaStatus + bangunNotifikasi
app/Services/SimpatorService.php            # scraping Simpator (manual)
app/Http/Controllers/Admin/SinkronisasiController.php  # sync manual per NOPOL / batch
config/monitoring.php                       # ambang status, konfigurasi Simpator
routes/console.php                          # jadwal scheduler
```

## Lisensi

Proyek internal Pemerintah Daerah.
