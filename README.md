# 🛍️ Self Order (Silverstripe CMS)

Proyek ini adalah platform e-commerce sederhana yang dibangun menggunakan Silverstripe CMS dan terintegrasi dengan API seperti Duitku.

## 🚀 Fitur Utama
- Login dengan Google Auth
- Manajemen produk & kategori
- Pembayaran otomatis (Duitku Sandbox)
- Dashboard admin (CMS)
- Invoice PDF dan email
- Dukungan URL Ngrok untuk testing public

---

## 📦 Langkah Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/namaproject.git
cd namaproject
```

### 2. Salin dan Atur File `.env`

Salin file `.env.example` menjadi `.env` lalu isi seperti berikut:

```dotenv
# Environment type
SS_ENVIRONMENT_TYPE="dev"

# Base URL
SS_BASE_URL="http://localhost/namaproject"

# Database
SS_DATABASE_CLASS="MySQLDatabase"
SS_DATABASE_SERVER="127.0.0.1"
SS_DATABASE_NAME="namadatabase"
SS_DATABASE_USERNAME="root"
SS_DATABASE_PASSWORD=""

# Admin Login (pertama kali build)
SS_DEFAULT_ADMIN_USERNAME="admin"
SS_DEFAULT_ADMIN_PASSWORD="password"

# Mailer
MAILER_DSN=""

# Google OAuth Configuration
GOOGLE_CLIENT_ID=""
GOOGLE_CLIENT_SECRET=""

# API Duitku (Sandbox)
DUITKU_MERCHANT_CODE=
DUITKU_API_KEY=
DUITKU_GETPAYMENTMETHOD_URL=
DUITKU_BASE_URL=

# Ngrok
NGROK_URL=https://ngrok_url/MetroShoppingG

# Mixed Content Fix
SS_TRUSTED_PROXY_IPS="*"
SS_TRUSTED_PROXY_PROTOCOL_HEADER="X-Forwarded-Proto"
SS_TRUSTED_PROXY_HOST_HEADER="X-Forwarded-Host"
```

> 📝 **Catatan:** Ganti `namaproject`, `namadatabase`, dan `NGROK_URL` sesuai kebutuhanmu.

---

### 3. Instalasi Dependensi

```bash
composer install
composer update
composer vendor-expose
composer require dompdf/dompdf

```

---

### 4. Setup Database

Buka browser:

```
http://localhost/namaproject/dev/build
```

---

### 5. Login Admin Panel

```
http://localhost/namaproject/admin
```

Login dengan akun dari `.env`:
```
Username: admin
Password: password
```

---

## 🌐 Ngrok (Testing URL Publik)

Jika kamu ingin mengakses project dari luar atau untuk webhook, jalankan:

```bash
ngrok http 80
```

Ganti `SS_BASE_URL` dan `NGROK_URL` di `.env` dengan URL yang dihasilkan Ngrok.

---

## 🧩 Struktur Umum Silverstripe

- `app/` - kode kustom seperti PageController, extensions, dan templates
- `public/` - folder web root, berisi `index.php` dan file publik
- `.env` - konfigurasi environment & API
- `composer.json` - dependensi proyek

---

## 🛠️ Tools yang Digunakan

- [Silverstripe CMS](https://www.silverstripe.org/)
- [Duitku Sandbox](https://docs.duitku.com/)
- [Ngrok](https://ngrok.com/)

---

## 🤝 Kontribusi

Pull request dan kontribusi sangat dihargai! Pastikan untuk melakukan:

- `composer lint`
- Testing fungsi sebelum PR
- Sertakan penjelasan pada setiap perubahan

---

## 📄 Lisensi

Project ini menggunakan [BSD-3-Clause License](LICENSE).