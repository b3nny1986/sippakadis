# DEPLOY SIPPAKADIS

Panduan lengkap deployment **SIPPAKADIS** (Laravel 12 + Blade/Tailwind) ke:

- **Supabase** — PostgreSQL (data) + Storage (lampiran, S3-compatible)
- **Vercel** — serverless PHP (`vercel-php@0.8.0`)
- **GitHub Actions** — auto-deploy + cron harian `/cron/daily`

---

## 1. Arsitektur

```
GitHub (repo sipakadis)
   │  push ke main → workflow deploy.yml → vercel --prod
   │  jadwal 22:00 UTC → workflow cron-daily.yml → POST /cron/daily
   ▼
Vercel (vercel-php@0.8.0)
   api/index.php → public/index.php (Laravel)
   env: session/cache/queue → database ; config/route cache → /tmp
   ▼                       ▼
Supabase PostgreSQL      Supabase Storage (S3-compatible)
```

- Scheduler Laravel tidak berjalan di Vercel (tanpa cron OS). Fungsi cron
  diganti **GitHub Actions** yang memanggil endpoint `POST /cron/daily`
  (dilindungi `CRON_TOKEN` via Bearer token).
- `routes/console.php` tetap mendaftarkan jadwal agar sama bila dijalankan
  `php artisan schedule:work` di lingkungan non-Vercel.

---

## 2. Prasyarat

- Akun [Supabase](https://supabase.com)
- Akun [Vercel](https://vercel.com)
- Repo GitHub berisi project ini (sudah di-push)
- CLI opsional untuk uji lokal: PHP 8.2+ dengan ekstensi `pdo_pgsql`, `gd`, `zip`

---

## 3. Setup Supabase

### 3.1 Buat project

1. Dashboard Supabase → **New project**
2. Isi:
   - Name: `sippakadis`
   - Database password: simpan aman (dipakai untuk koneksi)
   - Region: **Singapore** (South East Asia)
3. Tunggu provisioning selesai.

### 3.2 Kredensial database (PostgreSQL)

1. **Project Settings → Database → Connection string**
2. Copy parameter koneksi (host, port `5432`, database `postgres`).
   Bila memakai **connection pooler** (port `6543`), tetap valid untuk Laravel.
3. Variabel yang dibutuhkan app: `DB_HOST`, `DB_PORT`, `DB_DATABASE`,
   `DB_USERNAME`, `DB_PASSWORD`, `DB_SSLMODE=require`.

### 3.3 Storage bucket (S3-compatible)

1. **Storage → New bucket** → nama: `sippakadis` → **Public** (jika lampiran
   boleh publik) atau **Private**.
2. Buat **S3 Access Keys**:
   **Project Settings → Storage → S3 Access Keys → Create new key**.
   Simpan `Access Key` dan `Secret Key`.
3. Salin endpoint S3: `https://<project-ref>.supabase.co/storage/v1/s3`
   (bisa dari tab S3 Access Keys).
4. **Storage → Settings → CORS** — tambahkan origin `https://<app>.vercel.app`
   dengan method `GET, PUT, POST, DELETE`.

### 3.4 Migrasi + seed + import data ke Supabase

> Jalankan dari mesin lokal. Project wajib punya ekstensi `pdo_pgsql`.

```bash
export DB_CONNECTION=pgsql
export DB_HOST=<db-host.supabase.co>
export DB_PORT=5432
export DB_DATABASE=postgres
export DB_USERNAME=postgres
export DB_PASSWORD='<password>'
export DB_SSLMODE=require

php artisan migrate --force
php artisan db:seed --force
```

- `db:seed` membuat: role admin/opd, 11 status, 6 jenis, akun contoh
  (`admin@sippakadis.test` / `password`, `opd@sippakadis.test` / `password`).
- Import CSV master otomatis berjalan bila file
  `data_master_sippakadis.csv` ada di root (`DatabaseSeeder` memanggil
  `import:csv`). Kalau ingin manual:

```bash
php artisan import:csv --path=data_master_sippakadis.csv
```

> **Catatan keamanan**: ganti password admin/OPD contoh segera setelah
> deployment. Di Vercel bisa set env `ADMIN_EMAIL`, `ADMIN_PASSWORD`,
> `OPD_EMAIL`, `OPD_PASSWORD` bila ingin disisipkan saat `db:seed`.

---

## 4. Setup Vercel

### 4.1 Import project

1. [vercel.com → Add New → Project](https://vercel.com/new)
2. Pilih repo `sipakadis`.
3. **Framework Preset: Other** (jangan pilih Laravel bawaan Vercel).
   - Build command: kosongkan (runtime `vercel-php` menjalankan
     `composer install` sendiri).
   - Output directory: kosongkan.
4. Tambahkan environment variables (tabel 4.2).
5. **Deploy**.

### 4.2 Environment variables Vercel

| Variable | Contoh / Keterangan |
|---|---|
| `APP_KEY` | wajib. Buat: `php artisan key:generate --show` |
| `APP_ENV` | `production` (sudah di-set di `vercel.json`) |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://sippakadis-xxxx.vercel.app` |
| `CRON_TOKEN` | string acak (lihat 5.2) |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | dari Supabase (3.2) |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres` |
| `DB_PASSWORD` | dari Supabase |
| `DB_SSLMODE` | `require` |
| `FILESYSTEM_DISK` | `s3` |
| `AWS_ENDPOINT` | `https://<project-ref>.supabase.co/storage/v1/s3` |
| `AWS_ACCESS_KEY_ID` | dari Supabase (3.3) |
| `AWS_SECRET_ACCESS_KEY` | dari Supabase (3.3) |
| `AWS_BUCKET` | `sippakadis` |
| `AWS_DEFAULT_REGION` | `ap-southeast-1` |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` |
| `SIMPATOR_URL` | (opsional) default `http://simpator.kaltimprov.go.id/cari.php` |

> `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE` sudah di-set
> `database` via `vercel.json`. `LOG_CHANNEL=stderr` + cache di `/tmp`
> juga sudah di-set otomatis.

### 4.3 Verifikasi

- Buka `https://<app>.vercel.app/up` → harus tampil teks `OK`.
- Buka halaman login, masuk dengan akun admin.

---

## 5. Setup GitHub

### 5.1 Push repo

```bash
git remote add origin https://github.com/<user>/sipakadis.git
git push -u origin main
```

Workflow `deploy.yml` otomatis terpicu dan mendeploy ke Vercel.

### 5.2 Generate CRON_TOKEN

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Gunakan token yang sama untuk: env Vercel `CRON_TOKEN` (4.2) dan secret
GitHub `CRON_TOKEN`.

### 5.3 Secrets GitHub

**Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Isi |
|---|---|
| `VERCEL_TOKEN` | Token dari [Vercel Account → Settings → Tokens](https://vercel.com/account/settings/tokens) (scope sesuai project) |
| `PROD_URL` | `https://sippakadis-xxxx.vercel.app` |
| `CRON_TOKEN` | token acak dari 5.2 |

### 5.4 Cron harian

Workflow `cron-daily.yml` sudah berjalan otomatis setiap **22:00 UTC
(= 06:00 WITA)** memanggil `POST /cron/daily` dengan Bearer `CRON_TOKEN`.
Endpoint menjalankan:
1. `monitoring:daily` — perbarui status jatuh tempo + bangun notifikasi
2. sinkronisasi Simpator (batch 100)
3. notifikasi jatuh tempo

Untuk uji manual: **Actions → Cron Harian → Run workflow**.

---

## 6. Alur deploy berikutnya

- Setiap `git push` ke `main` → GitHub Actions → `vercel --prod`.
- Artifact deploy yang terlibat:
  - `vercel.json` (runtime + routes + env serverless)
  - `api/index.php` (forward ke `public/index.php`)
  - `.vercelignore` (exclude `vendor`, `storage`, `.env`, dll)
  - `.github/workflows/deploy.yml`
  - `.github/workflows/cron-daily.yml`
  - `routes/console.php` (scheduler opsional)

---

## 7. Troubleshooting

| Gejala | Penyebab / Solusi |
|---|---|
| `relation ... does not exist` saat deploy | Migrasi belum dijalankan ke Supabase (lihat 3.4) |
| Login gagal / session hilang | Pastikan tabel `sessions` ada; `SESSION_DRIVER=database` |
| 500 saat build asset | `public/build` belum ada → jalankan `npm install && npm run build` lalu push |
| Upload lampiran gagal | Cek `FILESYSTEM_DISK=s3`, kredensial S3, endpoint, dan CORS bucket |
| Cron tidak jalan | Cek secret `PROD_URL`, `CRON_TOKEN`; coba *Run workflow* manual (5.4) |
| `is_readable` false di WSL | Sudah ditangani app (pakai `fopen` probe); hanya isu lingkungan Windows |

---

## 8. Keamanan (checklist sebelum produksi)

- [ ] Ganti `APP_KEY` (tidak boleh sama dengan nilai di repo).
- [ ] `APP_DEBUG=false`.
- [ ] Ganti password akun admin/OPD contoh.
- [ ] Rotasi Supabase database password bila pernah bocor.
- [ ] Pastikan S3 bucket hanya menyimpan lampiran yang diperlukan.
- [ ] `CRON_TOKEN` acak & hanya di Vercel env + GitHub secret.
