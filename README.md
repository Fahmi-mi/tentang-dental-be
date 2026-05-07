# Tentang Dental Backend API

Backend API untuk aplikasi Tentang Dental. Proyek ini menggunakan Laravel 12 sebagai fondasi, dengan fokus pada:

- Konten publik website: promo, layanan, artikel, galeri, dokter, testimoni, FAQ.
- Panel admin berbasis role untuk operasional klinik.
- Manajemen reservasi pasien, data rekam medis ringkas, dan rontgen.
- Dashboard statistik operasional.

Dokumentasi ini disusun ulang berdasarkan implementasi aktual di repository (route, controller, model, request validation, resource response, middleware, seeder, dan config), sehingga isi README ini merepresentasikan kondisi proyek saat ini.

## 1) Teknologi dan Dependensi

### Backend utama

- PHP: ^8.2
- Laravel Framework: ^12.0
- Laravel Sanctum: ^4.0 (token authentication)
- DomPDF (barryvdh/laravel-dompdf): export PDF data pasien
- Intervention Image: utilitas pengolahan/upload gambar

### Dokumentasi API

- dedoc/scramble (dev dependency)
- Export OpenAPI ke file: api.json
- UI docs tersedia di endpoint docs/api

### Testing dan tooling

- Pest + pest-plugin-laravel
- Laravel Pint
- Vite (asset build)

## 2) Struktur Domain Utama

Entitas inti yang dipakai aplikasi:

- Admin: autentikasi dan otorisasi panel admin (role registration/rontgen).
- Patient: data pasien master.
- PatientMedicalHistory: riwayat medis pasien (hasOne ke Patient).
- PatientDentalHistory: riwayat gigi pasien (hasOne ke Patient).
- Doctor: data dokter + jadwal praktik (schedule disimpan sebagai array/JSON).
- Service: layanan klinik.
- Reservation: transaksi reservasi pasien ke dokter dan layanan.
- ReservationService: pivot many-to-many reservasi-layanan.
- Rontgen: data pemeriksaan rontgen pasien.
- ExaminationImage: kumpulan gambar per rontgen.
- Tag: tag temuan rontgen.
- ExamminationTag: pivot rontgen-tag.
- Article, Promo, Gallery, Testimonial, Faq: konten CMS.
- AdminNotification: notifikasi operasional admin.

## 3) Kontrak Response API

Sebagian besar endpoint API menggunakan helper format response berikut:

```json
{
    "success": true,
    "data": { "...": "..." },
    "message": "..."
}
```

Detail perilaku helper:

- success selalu ada.
- data hanya muncul jika nilainya tidak null.
- message hanya muncul jika string tidak kosong.

Untuk endpoint list dengan pagination, payload data umumnya berbentuk:

```json
{
    "success": true,
    "data": {
        "<entity_plural>": [],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 10,
            "total": 0
        }
    },
    "message": "..."
}
```

## 4) Autentikasi dan Otorisasi

### Autentikasi

- Admin login menghasilkan personal access token Sanctum.
- Endpoint privat menggunakan middleware auth:sanctum.
- Token dikelola via endpoint logout dan refresh.

### Role admin

- registration
    - Akses penuh CMS + dashboard + manajemen reservasi + update/hapus pasien.
- rontgen
    - Akses tulis modul rontgen.
- registration,rontgen (shared)
    - Akses baca data pasien/rontgen dan fitur download.

### Middleware role

- Alias middleware role terdaftar di bootstrap.
- Jika belum login: 401.
- Jika role tidak sesuai: 403.

## 5) Alur Bisnis (Flow)

### A. Flow konten publik

1. Frontend memanggil endpoint public (promo, layanan, artikel, galeri, dokter, testimoni, faq).
2. Controller public mengambil data terpilih dari model.
3. Resource public membentuk payload siap konsumsi frontend (termasuk URL aset).

### B. Flow autentikasi admin

1. Admin login dengan email + password.
2. Sistem validasi kredensial dan membuat token Sanctum.
3. Token dipakai pada request protected berikutnya (Bearer token).
4. Endpoint me mengembalikan profil admin aktif.
5. Endpoint refresh mengganti token lama dengan token baru.

### C. Flow reservasi admin

1. Pasien membuat reservasi dari endpoint public.
2. Validasi pasien berdasarkan kategori:
    - existing: cari pasien berdasar name + phone (+ optional birth_date).
    - new: buat pasien baru (phone harus unik).
3. Validasi slot jadwal dokter berdasarkan schedule per hari dan appointment_time.
4. Simpan reservation status awal pending.
5. Attach maksimal 3 layanan via tabel pivot reservation_service.
6. Admin mengelola data reservasi (list/detail/update/delete) dari endpoint admin.

### D. Flow rontgen

1. Admin rontgen menambahkan data rontgen untuk pasien.
2. Jika doctor_id tidak dikirim, sistem ambil dokter dari reservasi terakhir pasien.
3. Upload 1..n gambar pemeriksaan ke storage public folder rontgen.
4. Simpan file ke tabel examination_images.
5. Opsional sync tag_ids ke pivot exammination_tags.
6. Endpoint detail menampilkan patient + doctor + images + tags.
7. Endpoint download mengunduh primary image terbaru.

### E. Flow dashboard

Dashboard menyajikan:

- Statistik harian status reservasi hari ini.
- Total data agregat (patients, reservations, rontgens, pending).
- Analitik layanan bulanan (berdasarkan join reservations + reservation_service + services).
- Daftar reservasi terbaru.

## 6) Daftar Endpoint API Aktif

Base URL default lokal:

- http://localhost:8000/api

### 6.1 Public API (tanpa auth)

| Method | Endpoint         | Keterangan               |
| ------ | ---------------- | ------------------------ |
| GET    | /promos          | List promo               |
| GET    | /promos/{id}     | Detail promo             |
| GET    | /services        | List layanan             |
| GET    | /services/{id}   | Detail layanan           |
| GET    | /articles        | List artikel (paginated) |
| GET    | /articles/{slug} | Detail artikel by slug   |
| GET    | /galleries       | List galeri              |
| GET    | /doctors         | List dokter              |
| GET    | /doctors/{id}    | Detail dokter            |
| GET    | /testimonials    | List testimoni           |
| GET    | /faqs            | List FAQ                 |
| POST   | /reservations    | Buat reservasi pasien    |

### 6.2 Admin Auth API

| Method | Endpoint               | Auth    | Keterangan                                 |
| ------ | ---------------------- | ------- | ------------------------------------------ |
| POST   | /admin/login           | No      | Login admin                                |
| POST   | /admin/register        | No      | Register admin baru (role default rontgen) |
| POST   | /admin/logout          | Sanctum | Logout (hapus current token)               |
| POST   | /admin/refresh         | Sanctum | Refresh token                              |
| GET    | /admin/me              | Sanctum | Profil admin saat ini                      |
| PUT    | /admin/profile         | Sanctum | Update nama/foto profil                    |
| PUT    | /admin/change-email    | Sanctum | Ubah email (dengan current_password)       |
| PUT    | /admin/change-password | Sanctum | Ubah password (dengan current_password)    |

### 6.3 Admin API - Role registration

#### CMS resources (REST)

Semua endpoint berikut berada di bawah /admin dan butuh Sanctum + role:registration.

| Resource     | Endpoints                                                                                                              |
| ------------ | ---------------------------------------------------------------------------------------------------------------------- |
| promos       | GET /promos, POST /promos, GET /promos/{id}, PUT/PATCH /promos/{id}, DELETE /promos/{id}                               |
| services     | GET /services, POST /services, GET /services/{id}, PUT/PATCH /services/{id}, DELETE /services/{id}                     |
| articles     | GET /articles, POST /articles, GET /articles/{id}, PUT/PATCH /articles/{id}, DELETE /articles/{id}                     |
| galleries    | GET /galleries, POST /galleries, GET /galleries/{id}, PUT/PATCH /galleries/{id}, DELETE /galleries/{id}                |
| doctors      | GET /doctors, POST /doctors, GET /doctors/{id}, PUT/PATCH /doctors/{id}, DELETE /doctors/{id}                          |
| testimonials | GET /testimonials, POST /testimonials, GET /testimonials/{id}, PUT/PATCH /testimonials/{id}, DELETE /testimonials/{id} |
| faqs         | GET /faqs, POST /faqs, GET /faqs/{id}, PUT/PATCH /faqs/{id}, DELETE /faqs/{id}                                         |

#### Dashboard

| Method | Endpoint                           | Keterangan                           |
| ------ | ---------------------------------- | ------------------------------------ |
| GET    | /admin/dashboard                   | Ringkasan dashboard                  |
| GET    | /admin/dashboard/reservation-stats | Statistik reservasi (by date/status) |
| GET    | /admin/dashboard/service-analytics | Analitik layanan                     |

#### Reservation management

| Method | Endpoint                                 | Keterangan                                 |
| ------ | ---------------------------------------- | ------------------------------------------ |
| POST   | /admin/reservations                      | Buat reservasi dari panel admin            |
| GET    | /admin/reservations                      | List reservasi                             |
| GET    | /admin/reservations/{id}                 | Detail reservasi                           |
| PUT    | /admin/reservations/{id}                 | Update status reservasi                    |
| PUT    | /admin/reservations/{id}/patient-details | Simpan/update detail pasien pada reservasi |
| DELETE | /admin/reservations/{id}                 | Hapus reservasi                            |

#### Patient write

| Method | Endpoint             | Keterangan                                      |
| ------ | -------------------- | ----------------------------------------------- |
| PUT    | /admin/patients/{id} | Update pasien                                   |
| DELETE | /admin/patients/{id} | Hapus pasien (ditolak jika ada reservasi aktif) |

### 6.4 Admin API - Role registration atau rontgen (shared read/download)

Butuh Sanctum + role:registration,rontgen.

| Method | Endpoint                          | Keterangan               |
| ------ | --------------------------------- | ------------------------ |
| GET    | /admin/patients                   | List pasien              |
| GET    | /admin/patients/{id}              | Detail pasien            |
| GET    | /admin/patients/{id}/rontgens     | List rontgen per pasien  |
| GET    | /admin/patients/{id}/download-pdf | Download PDF data pasien |
| GET    | /admin/rontgens                   | List rontgen             |
| GET    | /admin/rontgens/{id}              | Detail rontgen           |
| GET    | /admin/rontgens/{id}/download     | Download file rontgen    |

### 6.5 Admin API - Role rontgen (write)

Butuh Sanctum + role:rontgen.

| Method | Endpoint             | Keterangan     |
| ------ | -------------------- | -------------- |
| POST   | /admin/rontgens      | Tambah rontgen |
| PUT    | /admin/rontgens/{id} | Update rontgen |
| DELETE | /admin/rontgens/{id} | Hapus rontgen  |

### 6.6 Endpoint dokumentasi API

| Method | Endpoint       | Keterangan              |
| ------ | -------------- | ----------------------- |
| GET    | /docs/api      | UI dokumentasi Scramble |
| GET    | /docs/api.json | OpenAPI JSON            |

## 7) Validasi Input Penting

Beberapa aturan penting yang diterapkan di FormRequest:

- Reservasi:
    - reservation_date harus >= hari ini.
    - appointment_time format HH:MM.
    - service_ids minimal 1 dan maksimal 3 item.
    - status hanya pending/validated/completed/cancelled.
- Promo:
    - promo_price harus lebih kecil dari original_price.
- Upload gambar:
    - ekstensi dibatasi per modul (jpeg/jpg/png/webp, beberapa endpoint mendukung svg).
    - ukuran file dibatasi (umumnya 1MB-5MB tergantung endpoint).
- Rontgen:
    - images wajib array dengan minimal 1 file.

## 8) Setup Proyek

### Prasyarat

- PHP 8.2+
- Composer
- MySQL/MariaDB
- Node.js + npm

### Instalasi cepat

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di .env, lalu:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

Jalankan server:

```bash
php artisan serve
```

Opsional frontend asset/dev tooling:

```bash
npm install
npm run dev
```

### Alternatif script composer

Tersedia juga script:

- composer run setup
- composer run dev
- composer run test

## 9) Skema Database (ringkas)

Berikut tabel utama yang dibuat dari migration proyek:

- users
- cache, cache_locks
- jobs, job_batches, failed_jobs
- personal_access_tokens
- admins
- patients
- patient_medical_histories
- patient_dental_histories
- doctors
- reservations
- reservation_service
- rontgen
- examination_images
- tags
- exammination_tags
- promos
- services
- articles
- galleries
- testimonials
- faqs
- notification

## 10) Seed Data Default

Seeder utama: ProjectSeeder (dipanggil dari DatabaseSeeder).

Akun admin default:

1. Admin Registration
    - email: admin@tentangdental.com
    - password: password
    - role: registration
2. Admin Rontgen
    - email: rontgen@tentangdental.com
    - password: password
    - role: rontgen

Seeder juga menghasilkan sample data untuk doctor/service/promo/article/gallery/testimonial/faq/patient/reservation/rontgen/tag/notifikasi.

## 11) Catatan Implementasi Penting

- upload file disimpan ke storage disk public via helper FileHelper::uploadImage.
- URL aset dibentuk dari resource, umumnya mengarah ke asset(storage/...).
- beberapa modul rontgen melakukan fallback path antara folder rontgen dan rontgens saat baca/hapus file.
- endpoint reservasi publik aktif di POST /api/reservations.
- auth guard di auth.php default masih web; autentikasi API private tetap bekerja via auth:sanctum pada route group admin.

## 12) Testing

Jalankan test:

```bash
php artisan test
```

Atau:

```bash
vendor/bin/pest
```

## 13) Update Dokumentasi API

Untuk regenerasi file OpenAPI `api.json` dari kode terbaru:

```bash
php artisan scramble:export --path=api.json
```

## 14) Ringkasan Modul

- Public module: konsumsi konten website.
- Admin Registration module: CMS + dashboard + reservation + patient write.
- Admin Rontgen module: manajemen rontgen.
- Shared admin module: akses baca pasien/rontgen + export/download.
