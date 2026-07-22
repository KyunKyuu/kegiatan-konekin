# 🚀 Panduan Deploy ke cPanel (Auto Deploy dari Branch `main`)

Aplikasi **Jadwal Kegiatan** (Laravel 13, PHP 8.3+, MySQL) di shared hosting cPanel,
dengan **document root = `public_html`** dan **auto-deploy dari branch `main`** memakai
fitur **Git Version Control** cPanel.

Semua file pendukung sudah disiapkan di repo:

| File | Fungsi |
|---|---|
| `.htaccess` (root) | Meneruskan semua request ke folder `public/` tanpa mengubah document root, sekaligus memblokir akses ke file sensitif (`.env`, `vendor/`, dll). |
| `.cpanel.yml` | Script deploy otomatis: `composer install` → `npm build` → `migrate` → cache. |
| `.env.production.example` | Template `.env` untuk produksi (MySQL, `APP_DEBUG=false`). |

---

## Ringkasan Alur

```
git push origin main  ──►  cPanel menerima commit  ──►  jalankan .cpanel.yml
                                                         (composer, npm, migrate, cache)
                                                         ──►  situs live di public_html
```

---

## Langkah Setup (sekali di awal)

### 1. Buat Database MySQL di cPanel
1. cPanel → **MySQL® Database Wizard**.
2. Buat database (mis. `jadwal`) → cPanel jadikan `namaakun_jadwal`.
3. Buat user DB + password kuat → cPanel jadikan `namaakun_dbuser`.
4. Beri user tersebut **ALL PRIVILEGES** pada database.
5. Catat: **nama DB, username, password** → dipakai di `.env`.

### 2. Pastikan Versi PHP 8.3+
cPanel → **MultiPHP Manager** → pilih domain → set ke **PHP 8.3** (atau lebih baru).
> Wajib. Laravel 13 & composer butuh PHP ≥ 8.3.

### 3. Hubungkan Git Version Control
cPanel → **Git™ Version Control** → **Create**.

Ada 2 pola auto-deploy — pilih salah satu:

**Pola A — Push langsung ke repo cPanel (paling sederhana):**
1. Isi **Repository Path**: `public_html` (kosongkan dulu folder ini bila ada file bawaan).
2. Setelah dibuat, cPanel memberi **Clone URL** (SSH), contoh:
   `ssh://namaakun@server:port/home/namaakun/public_html`
3. Di komputer lokal, tambahkan sebagai remote lalu push:
   ```bash
   git remote add cpanel ssh://namaakun@server:port/home/namaakun/public_html
   git push cpanel main
   ```
   Setiap `git push cpanel main` akan otomatis menjalankan `.cpanel.yml`.

**Pola B — Pull dari GitHub/GitLab:**
1. **Clone a Repository** → isi **Clone URL** repo GitHub Anda + Repository Path `public_html`.
2. Setiap ada update: buka menu repo → tab **Pull or Deploy** → **Update from Remote** →
   lalu **Deploy HEAD Commit** (menjalankan `.cpanel.yml`).
   > Agar benar-benar otomatis saat push ke GitHub, tambahkan **webhook** GitHub yang
   > memanggil endpoint update cPanel, atau jadwalkan **cron** yang menjalankan
   > `cd ~/public_html && git pull && /usr/local/cpanel/bin/... ` (opsional/lanjutan).

> **Catatan:** file `.env` di-ignore git, jadi TIDAK akan tertimpa/terhapus saat deploy.

### 4. Buat File `.env` di Server
1. cPanel → **File Manager** → masuk `public_html`.
2. Salin `.env.production.example` menjadi `.env`.
3. Edit `.env`:
   - `APP_URL=https://domain-anda.com`
   - Isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (dari langkah 1).
   - `APP_KEY` — lihat langkah 5.

### 5. Generate `APP_KEY`
**Jika cPanel punya Terminal / akses SSH:**
```bash
cd ~/public_html
php artisan key:generate --force
```
**Jika tidak ada Terminal:** generate lokal di komputer Anda —
```bash
php artisan key:generate --show
```
lalu salin hasilnya (`base64:...`) ke `APP_KEY=` di `.env` server.

### 6. Deploy Pertama
- **Pola A:** cukup `git push cpanel main` — `.cpanel.yml` langsung jalan.
- **Pola B:** menu repo → **Update from Remote** → **Deploy HEAD Commit**.

`.cpanel.yml` akan otomatis: `composer install` → `npm run build` → `migrate` →
`storage:link` → cache config/route/view.

### 7. Isi Data Awal + Akun Admin (sekali saja)
Migrasi hanya membuat tabel kosong. Untuk membuat akun admin + data contoh, jalankan
seeder **satu kali** via Terminal/SSH:
```bash
cd ~/public_html
php artisan db:seed --force
```
Akun default yang dibuat:
- **Admin:** `admin@example.com` / `password`
- **User:** `user@example.com` / `password`

> ⚠️ **Segera ganti password admin** setelah login pertama (lewat menu User di aplikasi).
> Jika hanya ingin akun admin tanpa 45 data contoh, buat user manual via `php artisan tinker`
> alih-alih menjalankan seeder penuh.

---

## ✅ Checklist "Tinggal Pakai"
- [ ] Database MySQL dibuat + privileges diberikan
- [ ] MultiPHP = 8.3+
- [ ] Git Version Control terhubung, Repository Path = `public_html`
- [ ] File `.env` dibuat di server (DB terisi, `APP_DEBUG=false`, `APP_URL` benar)
- [ ] `APP_KEY` terisi
- [ ] Deploy pertama sukses (cek log deploy di cPanel)
- [ ] `php artisan db:seed --force` (data awal + admin)
- [ ] AutoSSL aktif → set `SESSION_SECURE_COOKIE=true` di `.env` lalu redeploy
- [ ] Password admin diganti

---

## 🔧 Troubleshooting

**500 / halaman blank**
- Cek `storage/logs/laravel.log` (via File Manager).
- Pastikan `.env` ada & `APP_KEY` terisi.
- Pastikan folder `storage/` dan `bootstrap/cache/` writable (permission `755`/`775`).

**`composer` atau `php` versi salah saat deploy**
- Edit `.cpanel.yml`, ganti `php`/`composer` dengan path eksplisit PHP 8.3, contoh:
  `/opt/cpanel/ea-php83/root/usr/bin/php /opt/cpanel/composer/bin/composer install ...`

**Perubahan `.env` tidak berpengaruh**
- Config di-cache. Jalankan ulang deploy, atau via Terminal:
  `php artisan config:clear && php artisan config:cache`.

**Gambar dari upload tidak muncul**
- Pastikan `php artisan storage:link` sukses (dijalankan otomatis oleh `.cpanel.yml`).

**Aset CSS/JS tidak muncul (tampilan berantakan)**
- Pastikan `npm run build` sukses saat deploy (cek log). Folder `public/build` harus terisi.
