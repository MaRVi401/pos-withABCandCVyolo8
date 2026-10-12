# Integrated POS System with ABC Analysis & YOLOv8 Computer Vision

Sistem Point of Sale (POS) modern berbasis **Laravel 13** yang diintegrasikan dengan modul **Analisis Inventaris ABC** serta layanan **Computer Vision (YOLOv8)** berbasis Python untuk deteksi produk secara visual dan otomatis.

---

## 🚀 Fitur Utama

- **Point of Sale (POS)**: Antarmuka kasir interaktif untuk pemrosesan transaksi penjualan cepat.
- **Computer Vision Service (YOLOv8)**: Layanan terpisah berbasis Python (`yolo-service`) untuk perhitungan pengunjung yang datang ke toko.
- **Analisis Inventaris ABC (ABC Analysis)**: Pengelompokan produk otomatis ke dalam kategori **A** (Nilai tinggi), **B** (Nilai sedang), dan **C** (Nilai rendah) guna mendukung pengambilan keputusan manajemen stok.
- **Manajemen Stok & Penyesuaian**: Pelacakan stok masuk/keluar serta fitur *Stock Adjustment*.
- **Pelaporan & Ekspor**: Generasi laporan transaksi dan stok dengan *template* cetak PDF.
- **RESTful API V1**: API yang diamankan menggunakan **Laravel Sanctum** untuk integrasi aplikasi *mobile* atau layanan pihak ketiga.
- **Role-Based Access Control (RBAC)**: Pembatasan hak akses pengguna (Admin, Kasir, dll).

---

## 🛠️ Arsitektur & Teknologi

- **Backend / Web App**: Laravel 13 (PHP 8.3+)
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Computer Vision**: Python 3.x, Ultralytics YOLOv8, OpenCV
- **Frontend Views**: Blade Templates, CSS/JS (Vite)

---

## 📁 Struktur Direktori

```text
.
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/   # API V1 (Auth, Product, Transaction, ABC, dll)
│   │   └── Middleware/           # Middleware hak akses (CheckAdminRole)
│   ├── Models/                   # Model Eloquent (Product, Transaction, Category, dll)
│   └── Services/                 # Business Logic (ABCService)
├── config/                       # Konfigurasi aplikasi Laravel
├── database/
│   ├── migrations/               # Skema tabel POS & Token Akses
│   └── seeders/                  # Seeder data master, produk, dan pengguna
├── resources/
│   └── views/
│       ├── admin/                # Antarmuka Admin & Kasir (POS, ABC, Reports)
│       └── reports/              # Template PDF Laporan
├── routes/
│   ├── api.php                   # Endpoints API V1
│   └── web.php                   # Rute Antarmuka Web
├── yolo-service/                 # Microservice Computer Vision (YOLOv8 Python)
│   └── app.py                    # Server Flask/FastAPI untuk deteksi YOLOv8
└── vite.config.js                # Build tool frontend

```

---

## ⚙️ Panduan Instalasi & Konfigurasi

### 1. Prasyarat Sistem

* PHP >= 8.2
* Composer
* Node.js & NPM
* Python 3.9+ (Untuk `yolo-service`)
* Database Server (MySQL)

---

### 2. Instalasi Aplikasi Utama (Laravel POS)

1. **Clone Repositori**:
```bash
git clone https://github.com/username/pos-withABCandCVyolo8.git
cd pos-withABCandCVyolo8

```


2. **Instal Dependensi PHP & Node.js**:
```bash
composer install
npm install

```


3. **Konfigurasi Environment**:
Salin berkas `.env.example` menjadi `.env` dan atur konfigurasi database Anda:
```bash
cp .env.example .env
php artisan key:generate

```


4. **Migrasi & Seeding Database**:
```bash
php artisan migrate --seed

```


5. **Build Asset Frontend**:
```bash
npm run build
# Atau gunakan `npm run dev` untuk lingkungan pengembangan

```


6. **Jalankan Server Laravel**:
```bash
php artisan serve

```
7. **Link Storage**:
```bash
php artisan storage:link

```

Aplikasi web akan berjalan pada `http://127.0.0.1:8000`.

---

### 3. Setup Layanan Computer Vision (`yolo-service`)

1. **Masuk ke direktori YOLO Service**:
```bash
cd yolo-service

```


2. **Buat Virtual Environment Python**:
```bash
python -m venv venv
# Linux/macOS
source venv/bin/activate
# Windows
venv\Scripts\activate

```


3. **Instal Dependensi Python**:
```bash
pip install ultralytics opencv-python flask requests

```


4. **Jalankan Service YOLO**:
```bash
python app.py

```



---

## 📡 Integrasi API V1

Sistem menyediakan antarmuka REST API V1 yang terproteksi token Sanctum pada endpoint `/api/v1/`:

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `POST` | `/api/v1/login` | Authentikasi & Pengambilan Token |
| `GET` | `/api/v1/products` | Mengambil daftar produk |
| `POST` | `/api/v1/transactions` | Membuat transaksi baru |
| `GET` | `/api/v1/abc-analysis` | Mengambil hasil kalkulasi Analisis ABC |
| `POST` | `/api/v1/stock-adjustments` | Melakukan penyesuaian stok |

---

## 📊 Analisis ABC (Activity-Based Classification)

Sistem secara otomatis menghitung efisiensi pengelolaan stok berdasarkan hukum Pareto (80/20):

* **Kategori A**: Produk kontributor penjualan tertinggi (~80% nilai penjualan, ~20% total item).
* **Kategori B**: Produk kontributor penjualan menengah (~15% nilai penjualan, ~30% total item).
* **Kategori C**: Produk kontributor penjualan rendah (~5% nilai penjualan, ~50% total item).

Laporan analisis dapat diakses pada panel Admin melalui menu **ABC Analysis**.

---

## 📄 Lisensi

Pake aja lah bebas :v
