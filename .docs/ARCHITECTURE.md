# Architecture — Instagram Employee Engagement Tracking

## 1. Architectural Decision

Aplikasi menggunakan **Laravel 13 sebagai root project** dan **modular monolith**.

Tidak menggunakan:
- `my-project/src` sebagai root source;
- nested Laravel project;
- microservice untuk MVP.

Struktur `my-project` sendiri adalah project Laravel yang dapat langsung dijalankan.

## 2. Project Structure

```text
my-project/
├── .docs/
│   ├── PRD.md
│   ├── ARCHITECTURE.md
│   ├── TASKS.md
│   └── DATABASE.md
├── .gemini.md
├── app/
│   ├── Actions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Jobs/
│   ├── Models/
│   ├── Policies/
│   ├── Services/
│   │   └── Instagram/
│   └── Support/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   │   ├── components/
│   │   ├── layouts/
│   │   ├── pages/
│   │   ├── types/
│   │   └── app.tsx
│   └── views/
├── routes/
│   ├── web.php
│   └── console.php
├── storage/
├── tests/
├── .env
├── .env.example
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.ts
└── README.md
```

`app/Actions`, `app/Services`, dan folder lain di dalam `app` hanya dibuat ketika benar-benar diperlukan.

## 3. Runtime

Dari root project:

```bash
cd my-project
php artisan serve
```

Frontend asset:

```bash
npm run dev
```

Build production:

```bash
npm run build
```

Migration:

```bash
php artisan migrate
```

Queue worker:

```bash
php artisan queue:work
```

Project root tetap `my-project`; Laravel entry point HTTP berada pada `public/`.

## 4. Layer Architecture

### Presentation
- Inertia pages
- React
- TypeScript
- Tailwind

Lokasi:
`resources/js/`

### HTTP
- Controllers
- Form Requests
- Middleware

Lokasi:
`app/Http/`

Controller tidak berisi business logic berat.

### Application
- Actions
- Jobs

Lokasi:
`app/Actions/`
`app/Jobs/`

### Domain/Application Services
- Instagram API service
- reporting service
- matching service

Lokasi:
`app/Services/`

### Persistence
- Eloquent Models
- Migrations
- Query/aggregation

Lokasi:
`app/Models/`
`database/migrations/`

## 5. Instagram Integration

Buat abstraction agar controller tidak bergantung langsung pada HTTP API.

Contoh:

```text
app/Services/Instagram/
├── InstagramClient.php
├── InstagramSyncService.php
├── InstagramMediaSyncService.php
├── InstagramCommentSyncService.php
├── InstagramMetricSyncService.php
├── EmployeeInstagramLinkService.php
├── RankingService.php
└── DTO/
```

Nama dan pembagian class dapat disesuaikan saat implementasi.

Prinsip:
- API request hanya melalui Instagram service/client.
- Pagination ditangani di service.
- Response API dinormalisasi sebelum masuk model.
- External ID digunakan sebagai identity.
- Raw payload dapat disimpan (JSONB) dengan policy retention (misal 90 hari) untuk audit.
- API capability harus diverifikasi terhadap dokumentasi resmi sebelum implementasi.
- Webhook signature (`X-Hub-Signature-256`) harus diverifikasi menggunakan Meta App Secret.

## 6. Data Flow

```text
Instagram API
     ↓
InstagramClient
     ↓
Sync Service / Job
     ↓
Normalize API Response
     ↓
Eloquent Model
     ↓
PostgreSQL
     ↓
Reporting / Aggregation
     ↓
Laravel Controller
     ↓
Inertia
     ↓
React + TypeScript
```

## 7. Sync Strategy

### Media
- fetch account media;
- pagination;
- upsert by `external_media_id`;
- update mutable metadata;
- save `published_at`.

### Comments
- fetch comments per media;
- pagination;
- upsert by `external_comment_id`;
- store commenter Instagram User ID;
- match employee by User ID.

### Metrics
Gunakan snapshot agar perubahan nilai metrik dapat dilacak.

Jangan menganggap metric snapshot sebagai user-level activity.

## 8. Employee Matching

Identity utama:

```text
employee.instagram_user_id
        =
instagram_comments.commenter_instagram_user_id
```

Username hanya atribut display/search.

Proses Auto-Link via Username DILARANG. Admin harus melakukan **Verifikasi Manual** melalui UI untuk menautkan (LINK) employee yang berstatus UNLINKED dengan komentar `unmatched` dari calon pegawai.

Jika username berubah:
- employee tetap sama;
- comment history tetap terhubung;
- username terbaru dapat diperbarui;
- tidak boleh membuat employee baru hanya karena username berubah.

## 9. Reporting Period

Komentar (dan interaksi) masuk ke periode jika menggunakan Half-Open Interval:

```text
commented_at >= start_at
AND
commented_at < end_at
```

Timezone reporting harus diperhitungkan secara konsisten.

Rekomendasi:
- simpan timestamp dalam UTC pada database;
- simpan timezone periode sebagai metadata;
- konversi boundary periode dengan benar saat query.

## 10. Dashboard Architecture

Dashboard menerima filter:
- reporting period;
- Instagram account;
- optional date range;
- metric.

Backend menghitung aggregate menggunakan SQL/Eloquent query.

Contoh statistik:
- media count;
- likes;
- comments;
- shares;
- employee comments;
- unique employees;
- comments per employee;
- comments per media.

## 11. Leaderboard

Leaderboard bukan evaluasi subjektif.

Contoh:

```text
metric = total_comments
ORDER BY total_comments DESC
```

atau:

```text
metric = unique_media_commented
ORDER BY unique_media_commented DESC
```

Tie-breaker dapat menggunakan employee name ASC.

## 12. Queue and Scheduler

Sync berat dijalankan melalui queue.

Contoh job:
- SyncInstagramAccount
- SyncInstagramMedia
- SyncInstagramComments
- SyncInstagramMetrics

Scheduler menjalankan sync otomatis sesuai interval yang dikonfigurasi.

Manual sync menggunakan endpoint yang dispatch job atau menjalankan sync sesuai kebutuhan UX.

## 13. Authorization

Gunakan:
- Laravel authentication;
- middleware;
- policies/gates.

Admin memiliki akses konfigurasi.

Viewer, jika diimplementasikan, hanya memiliki akses read-only yang ditentukan.

## 14. Error Handling

Setiap sync:
- menghasilkan sync log;
- menangkap API errors;
- menyimpan error message yang aman;
- melakukan retry untuk transient error;
- tidak mencatat access token.

Jika satu halaman API gagal, jangan menganggap seluruh dataset berhasil.

## 15. Testing

Minimal:
- feature test authentication;
- employee CRUD;
- reporting period boundary;
- media upsert;
- comment upsert;
- employee matching;
- duplicate sync;
- API error handling;
- username change;
- authorization;
- dashboard aggregation.

## 16. Deployment

Production minimal membutuhkan:
- PHP sesuai Laravel 13;
- Composer;
- Node.js untuk build;
- PostgreSQL;
- web server;
- queue worker;
- scheduler/cron.

`public/` menjadi document root web server.

## 17. Architectural Rules

1. `my-project` adalah root Laravel.
2. Jangan membuat `my-project/src` untuk source Laravel.
3. React/TypeScript berada di `resources/js`.
4. Jangan memasukkan business logic berat ke controller.
5. Jangan menggunakan scraping/private endpoint.
6. Jangan mengarang capability API.
7. Jangan menggunakan username sebagai identity utama.
8. Migration adalah source of truth database.
9. Sync harus idempotent.
10. Semua API integration melalui service/client.
