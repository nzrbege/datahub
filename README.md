# Sistem Manajemen Data Keluarga
### Compliant UU PDP No. 27 Tahun 2022

Platform Laravel PHP 8.4 untuk pengelolaan data keluarga/kependudukan dengan kepatuhan penuh terhadap Undang-Undang Perlindungan Data Pribadi Indonesia.

---

## 🚀 Instalasi

### Prasyarat
- PHP 8.4+
- MySQL 8.0+ / PostgreSQL 14+
- Composer 2.x
- Node.js 18+ (untuk asset)

### Langkah Instalasi

```bash
# 1. Clone atau ekstrak proyek
cd laravel-pdp-app

# 2. Install dependensi PHP
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Buat file enkripsi khusus (WAJIB — simpan nilai ini dengan aman!)
php -r "echo base64_encode(random_bytes(32));" 
# Salin output ke FILE_ENCRYPTION_KEY di .env

# 6. Konfigurasi database di .env
DB_DATABASE=data_keluarga_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 7. Buat database
mysql -u root -p -e "CREATE DATABASE data_keluarga_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 8. Jalankan migrasi dan seeder
php artisan migrate --seed

# 9. Buat storage link (tidak diperlukan untuk file private)
php artisan storage:link

# 10. Set permission folder
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 11. Jalankan server (development)
php artisan serve
```

---

## 🔐 Login Default

| Role        | Email                              | Password            |
|-------------|------------------------------------|---------------------|
| Super Admin | superadmin@datakeluarga.go.id      | SuperAdmin@12345!   |

> ⚠️ **WAJIB** ganti password segera setelah login pertama!

---

## 🏛️ Fitur Sistem

### Super Admin
| Fitur | Deskripsi |
|-------|-----------|
| Upload File | Unggah Excel/CSV/ZIP, otomatis dienkripsi AES-256 |
| Kelola Izin | Tentukan admin mana yang boleh akses tiap file |
| Review Permintaan | Approve/reject/revoke permintaan dari admin |
| Manajemen User | Tambah, edit, aktif/nonaktif, reset password |
| Audit Log | Lihat semua aktivitas pemrosesan data |
| Log Download | Pantau setiap unduhan dengan IP dan timestamp |

### Admin
| Fitur | Deskripsi |
|-------|-----------|
| Lihat File | Hanya file yang diizinkan Super Admin |
| Ajukan Permintaan | Lampirkan NDA PDF + alasan + dasar hukum |
| Download | Setelah disetujui + verifikasi captcha |
| Riwayat | Lihat semua permintaan dan status |

---

## 🛡️ Kepatuhan UU PDP No. 27/2022

| Pasal | Implementasi |
|-------|-------------|
| **Pasal 20** | Tujuan pemrosesan wajib diisi spesifik saat mengajukan permintaan |
| **Pasal 35** | Enkripsi AES-256-CBC + HMAC-SHA256 untuk semua file tersimpan |
| **Pasal 39** | Command otomatis hapus token expired dan data melewati retensi |
| **Pasal 47** | Audit log lengkap: siapa, apa, kapan, dari mana, untuk apa |
| **Pasal 50** | Prinsip need-to-know: izin akses per file per admin |
| **Pasal 51** | NDA wajib sebelum akses disetujui |
| **Pasal 53** | Log breach siap diekspor untuk pelaporan 14 hari |

### Langkah Keamanan Tambahan
- **Brute Force Protection**: Akun terkunci 30 menit setelah 5 kali gagal login
- **Rate Limiting**: Max 5 percobaan login per menit per IP
- **Session Security**: Encrypted, SameSite=Strict, HTTPOnly
- **Security Headers**: CSP, HSTS, X-Frame-Options, dll.
- **File Security**: File disimpan di storage private, tidak ada URL langsung
- **Hash Integritas**: SHA-256 untuk deteksi tamper pada file
- **Captcha Download**: Verifikasi manusia sebelum setiap unduhan
- **Soft Delete**: Data tidak langsung hilang, dapat di-audit

---

## 📅 Scheduled Tasks

Tambahkan ke crontab server:
```bash
# Setiap menit (Laravel scheduler)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Jadwal otomatis:
- **02:00 WIB** — Bersihkan token download expired + audit log lama

---

## 🗂️ Struktur Penyimpanan File

```
storage/app/private/
├── data-files/          ← File data terenkripsi (*.enc)
├── nda-documents/       ← Dokumen NDA dari admin
└── logs/audit/          ← Audit log harian UU PDP
```

> File di `storage/app/private/` **tidak bisa diakses via URL** — hanya melalui controller yang terotorisasi.

---

## ⚙️ Konfigurasi Produksi

Tambahkan/ubah di `.env`:
```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true    # Aktifkan jika menggunakan HTTPS
SESSION_SAME_SITE=strict
FILE_ENCRYPTION_KEY=          # Kunci AES-256 unik (JANGAN sampai hilang!)
ACTIVITY_LOG_RETENTION_DAYS=365
```

---

## 📦 Dependensi Utama

| Package | Fungsi |
|---------|--------|
| `laravel/framework ^11` | Framework utama |
| `spatie/laravel-permission` | Role & permission (super_admin, admin) |
| `spatie/laravel-activitylog` | Log aktivitas otomatis |
| `mews/captcha` | Captcha verifikasi download |
| `maatwebsite/excel` | Import/export Excel (opsional untuk future) |

---

## 📞 Kontak & Pelaporan Insiden

Sesuai **UU PDP Pasal 53**, insiden kebocoran data wajib dilaporkan dalam **14 hari kalender**.

Log insiden tersedia di: `storage/logs/audit/pdp-audit-YYYY-MM-DD.log`

---

*Sistem ini dibangun dengan mempertimbangkan seluruh aspek UU PDP No.27/2022 sebagai landasan hukum pengelolaan data pribadi.*
