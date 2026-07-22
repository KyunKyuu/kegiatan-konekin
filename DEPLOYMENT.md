# 🚀 Deployment — Jadwal Kegiatan → daily.konekin.space (Hostinger hPanel)

Aplikasi ini di-deploy ke **Hostinger shared hosting (hPanel)**, domain
**`daily.konekin.space`**, dengan **auto-deploy dari branch `main` via GitHub Actions**.

## Kenapa bukan fitur Git bawaan hPanel?

hPanel punya fitur Git sendiri (Websites → Manage → Advanced → Git), tapi setelah
dicoba & dicek langsung di server, ada 2 keterbatasan yang membuatnya tidak cukup:
- **Tidak ada Node.js di server** → `npm run build` (Tailwind/Vite) tidak bisa jalan,
  padahal aplikasi ini butuh build asset.
- Tidak menjalankan `artisan migrate`, `config:cache`, dll secara otomatis.

Karena itu, deployment sepenuhnya ditangani oleh **GitHub Actions**
(`.github/workflows/deploy.yml`), yang membangun asset di runner Actions (ada PHP & Node)
lalu meng-upload hasilnya ke server via SSH/rsync.

## Alur

```
git push origin main
        │
        ▼
GitHub Actions runner:
  composer install --no-dev  (PHP 8.3)
  npm ci && npm run build    (Node 20)
        │
        ▼
rsync seluruh project → server (SSH) → domains/daily.konekin.space/public_html
        │
        ▼
SSH ke server, jalankan:
  artisan migrate --force
  artisan storage:link
  artisan config:cache / route:cache / view:cache
        │
        ▼
Situs langsung update di https://daily.konekin.space
```

## Info server (untuk referensi)

| Item | Nilai |
|---|---|
| Host | `153.92.9.176` |
| Port SSH | `65002` |
| Username | `u385356168` |
| Path aplikasi | `/home/u385356168/domains/daily.konekin.space/public_html` |
| PHP server | 8.4.11 (Composer di Actions pakai 8.3 agar konsisten dgn `composer.json`) |
| Node di server | **tidak ada** — build selalu di GitHub Actions, bukan di server |

## Struktur yang sudah disiapkan

| File | Fungsi |
|---|---|
| `.github/workflows/deploy.yml` | Workflow CI/CD: build (composer+npm) → rsync → migrate & cache via SSH. Jalan otomatis tiap push ke `main`. |
| `.htaccess` (root) | Meneruskan semua request ke folder `public/` tanpa perlu ubah document root, sekaligus blokir akses ke file sensitif. |
| `.env.production.example` | Referensi/cadangan isi `.env` production (file `.env` asli sudah ada langsung di server, tidak ikut git). |

## GitHub Secrets yang dipakai

Hanya **satu** secret yang wajib ada di repo (Settings → Secrets and variables → Actions):
- `SSH_PRIVATE_KEY` — private key khusus deploy (sudah ditambahkan).

Host/port/username/path sengaja ditulis langsung di `deploy.yml` (bukan secret) karena
tanpa private key yang cocok, info itu saja tidak berguna untuk siapa pun.

## Database

Database MySQL dibuat manual sekali lewat **hPanel → Databases → MySQL Databases**
(shared hosting tidak mengizinkan pembuatan DB baru lewat SSH/CLI, hanya lewat panel).
Setelah dibuat, kredensialnya diisi ke `.env` di server (`DB_DATABASE`, `DB_USERNAME`,
`DB_PASSWORD`).

## Setelah deploy pertama sukses

Jalankan seeder **sekali saja** untuk data awal + akun admin:
```bash
ssh -p 65002 u385356168@153.92.9.176
cd domains/daily.konekin.space/public_html
php artisan db:seed --force
```
Akun default:
- **Admin:** `admin@example.com` / `password`
- **User:** `user@example.com` / `password`

⚠️ **Ganti password admin setelah login pertama.**

## Troubleshooting

**Actions gagal di step SSH/rsync**
- Cek `SSH_PRIVATE_KEY` di GitHub Secrets sudah benar (termasuk baris BEGIN/END).
- Cek public key masih terdaftar di hPanel → SSH Access → SSH Keys.

**500 / halaman blank setelah deploy**
- Cek `storage/logs/laravel.log` di server.
- Pastikan `.env` di server sudah lengkap (`DB_*` terisi, bukan `__PENDING__`).

**Aset CSS/JS tidak update**
- Cek log job "Install & build frontend assets" di tab Actions GitHub — pastikan
  `npm run build` sukses sebelum rsync jalan.

**Perubahan migrasi tidak masuk**
- Cek log step "Post-deploy" di Actions — `artisan migrate --force` harus sukses
  tanpa error di sana.
