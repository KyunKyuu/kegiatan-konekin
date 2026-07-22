# 🚀 Deployment — Jadwal Kegiatan → daily.konekin.space (Hostinger hPanel)

Aplikasi ini di-deploy ke **Hostinger shared hosting (hPanel)**, domain
**`daily.konekin.space`**, memakai **fitur Git bawaan hPanel** (Websites → Manage →
Advanced → Git) yang terhubung ke repo GitHub ini, branch `main`.

## Cara kerja

hPanel menyambung ke GitHub via OAuth (bukan SSH), jadi tidak perlu deploy key sama
sekali untuk proses pull-nya. Saat auto-deploy terpicu (push ke `main`) atau tombol
**"Deploy HEAD Commit"** ditekan di hPanel:

```
push ke main → hPanel clone/pull repo → composer install --no-dev (otomatis)
             → file live di domains/daily.konekin.space/public_html
```

**Keterbatasan hPanel yang perlu diketahui** (sudah diverifikasi langsung di server):
- ✅ `composer install` otomatis jalan saat deploy.
- ❌ **Tidak ada Node.js di server** → `npm run build` tidak bisa jalan di sana.
  Solusinya: folder `public/build` (hasil `npm run build`) **ikut di-commit ke repo**
  (dikeluarkan dari `.gitignore`), jadi hPanel tinggal pull file jadi.
- ❌ Tidak otomatis menjalankan `artisan migrate`/`config:cache`/`route:cache`/
  `view:cache`. Ini perlu dijalankan manual via SSH setiap kali ada perubahan
  migrasi/konfigurasi (lihat bagian di bawah).

## ⚠️ Penting: build ulang asset sebelum push kalau ada perubahan CSS/JS

Karena `public/build` ikut di-commit, setiap kali ada perubahan pada file
Blade/CSS/JS yang mempengaruhi tampilan, jalankan ini dulu sebelum commit & push:
```bash
npm run build
git add public/build
git commit -m "Build assets"
git push origin main
```
Kalau lupa, hPanel tetap akan deploy file lama di `public/build` (tampilan tidak update,
tapi tidak akan error).

## Info server (untuk referensi)

| Item | Nilai |
|---|---|
| Host | `153.92.9.176` |
| Port SSH | `65002` |
| Username | `u385356168` |
| Path aplikasi | `/home/u385356168/domains/daily.konekin.space/public_html` |
| PHP server | 8.4 (di-set lewat MultiPHP/site PHP version di hPanel) |
| Node di server | **tidak ada** — karena itu `public/build` di-commit ke git |

## Struktur yang sudah disiapkan

| File | Fungsi |
|---|---|
| `.htaccess` (root) | Meneruskan semua request ke folder `public/` tanpa perlu ubah document root, sekaligus blokir akses ke file sensitif. |
| `.env.production.example` | Referensi/cadangan isi `.env` production (file `.env` asli sudah ada langsung di server, tidak ikut git). |
| `public/build/` | Hasil build Vite/Tailwind, di-commit manual (lihat peringatan di atas). |

## Database

Database MySQL dibuat manual lewat **hPanel → Databases → MySQL Databases**
(shared hosting tidak mengizinkan pembuatan DB baru lewat SSH/CLI, hanya lewat panel).
Kredensialnya sudah diisi ke `.env` di server (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

## Setelah tiap deploy yang mengubah migrasi/config

hPanel tidak menjalankan ini otomatis — jalankan manual via SSH:
```bash
ssh -p 65002 u385356168@153.92.9.176
cd domains/daily.konekin.space/public_html
php artisan migrate --force
php artisan config:clear && php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Setelah deploy pertama sukses (sekali saja)

```bash
php artisan storage:link
php artisan db:seed --force   # data awal + akun admin
```
Akun default:
- **Admin:** `admin@example.com` / `password`
- **User:** `user@example.com` / `password`

⚠️ **Ganti password admin setelah login pertama.**

## Troubleshooting

**Deploy gagal di hPanel (lihat log di menu Git → riwayat deploy)**
- Error `"Your lock file does not contain a compatible set of packages"` → biasanya
  berarti `composer.json` butuh versi PHP lebih baru dari yang aktif di situs ini.
  Cek **hPanel → MultiPHP/PHP Configuration** untuk domain ini sudah di versi yang
  sesuai `composer.json` (`"require": {"php": "^8.4"}`).

**500 / halaman blank setelah deploy**
- Cek `storage/logs/laravel.log` di server.
- Pastikan `.env` di server lengkap (`DB_*` terisi, bukan placeholder).

**Aset CSS/JS tidak update**
- Pastikan sudah `npm run build` + commit `public/build` sebelum push (lihat
  peringatan di atas) — hPanel tidak build asset sendiri.

**Perubahan migrasi tidak masuk**
- Jalankan manual `php artisan migrate --force` via SSH setelah deploy (hPanel
  tidak menjalankan ini otomatis).
