# PRD — Instagram Employee Engagement Tracking

## 1. Ringkasan Produk

**Nama proyek:** Instagram Employee Engagement Tracking

Sistem web internal untuk memantau performa konten Instagram milik organisasi dan aktivitas engagement karyawan pada konten tersebut.

Sistem dibangun sebagai **Laravel 13 monolith** dengan:
- Laravel 13
- Inertia.js
- React
- TypeScript
- Tailwind CSS
- PostgreSQL

**Catatan struktur:** folder `my-project` adalah **root project Laravel yang dapat langsung dijalankan**. Tidak ada wrapper/nested source project seperti `my-project/src`.

Frontend React/TypeScript berada di dalam struktur Laravel standar, terutama:
- `resources/js/`
- `resources/css/`

## 2. Tujuan

Sistem harus dapat:

1. Mengelola dua atau lebih akun Instagram organisasi.
2. Menyimpan identitas Instagram account berdasarkan Instagram User ID.
3. Melakukan sinkronisasi data akun, media, metrik, dan komentar melalui API resmi yang tersedia.
4. Mengelola data karyawan dan akun Instagram karyawan.
5. Mencocokkan commenter Instagram dengan karyawan berdasarkan Instagram User ID.
6. Mengatur reporting period/tenggat pelaporan.
7. Menampilkan statistik engagement berdasarkan periode, akun, media, dan karyawan.
8. Menampilkan leaderboard berdasarkan metrik yang dipilih.
9. Menyediakan export data.
10. Menyediakan sinkronisasi manual dan otomatis.

## 3. Batasan API

Sistem **tidak boleh mengasumsikan kemampuan API yang tidak tersedia secara resmi**.

MVP tidak menjanjikan pelacakan identitas pengguna yang memberikan Like atau Share/Repost secara individual.

Jika API resmi menyediakan metrik agregat, sistem dapat menyimpan metrik tersebut, tetapi metrik agregat tidak boleh dianggap sebagai daftar user yang melakukan engagement.

Aktivitas karyawan yang dapat diverifikasi melalui data commenter dapat digunakan untuk employee-level tracking.

Username Instagram dianggap atribut yang dapat berubah. **Instagram User ID menjadi identifier utama**.

## 4. Pengguna Sistem

### Admin
Dapat:
- mengelola akun Instagram organisasi;
- mengelola karyawan;
- mengatur Instagram User ID karyawan;
- membuat reporting period;
- menjalankan sinkronisasi;
- melihat dashboard;
- melihat statistik;
- melihat leaderboard;
- melakukan export.

### Viewer (opsional)
Dapat melihat data/report yang diizinkan, tetapi tidak mengubah konfigurasi.

## 5. Konsep Data Utama

### Organization Instagram Account
Akun Instagram organisasi yang dipantau.

Data penting:
- internal ID
- Instagram User ID
- username
- account name
- account type
- connection status
- access token terenkripsi
- token expiry
- last synced at

### Employee
Data karyawan yang dapat dihubungkan dengan akun Instagram.

Data penting:
- employee code
- name
- department
- Instagram User ID
- Instagram username
- active status

### Reporting Period
Periode pelaporan.

Data:
- name
- start datetime
- end datetime
- timezone
- status

Media masuk ke periode apabila:

`published_at >= start_at AND published_at <= end_at`

### Instagram Media
Konten Instagram organisasi.

Data:
- external media ID
- account
- media type
- caption
- permalink
- thumbnail
- published at

### Instagram Comment
Komentar pada media.

Data:
- external comment ID
- media
- commenter Instagram User ID
- commenter username
- matched employee
- comment text
- commented at

### Metric Snapshot
Snapshot metrik media pada waktu tertentu.

Contoh:
- likes
- comments
- shares
- saves
- reach
- impressions

Field dibuat nullable karena tidak semua metrik selalu tersedia dari API.

## 6. Functional Requirements

### FR-01 Authentication
Sistem menyediakan authentication dan authorization.

### FR-02 Instagram Account Management
Admin dapat:
- menambah akun Instagram organisasi;
- menyimpan Instagram User ID;
- menghubungkan credential/API access;
- melihat connection status;
- menjalankan sync manual;
- melihat last synced at.

### FR-03 Employee Management
Admin dapat:
- tambah/edit/nonaktifkan karyawan;
- memasukkan Instagram User ID;
- menyimpan username sebagai informasi tampilan;
- memperbarui username tanpa mengganti identity utama.

### FR-04 Reporting Period
Admin dapat:
- membuat periode;
- menentukan start/end datetime;
- menentukan timezone;
- mengaktifkan/nonaktifkan periode;
- memilih periode pada dashboard/report.

### FR-05 Media Sync
Sistem dapat:
- mengambil media melalui API resmi;
- melakukan pagination;
- melakukan upsert berdasarkan external media ID;
- menyimpan waktu publikasi;
- menyimpan data yang diperlukan untuk reporting.

### FR-06 Comment Sync
Sistem dapat:
- mengambil komentar;
- melakukan pagination;
- melakukan upsert berdasarkan external comment ID;
- menyimpan commenter user ID;
- melakukan employee matching berdasarkan Instagram User ID.

### FR-07 Metric Sync
Sistem dapat menyimpan snapshot metrik media.

Jika API menyediakan nilai agregat:
- likes
- comments
- shares
- saves
- reach
- impressions

maka nilai dapat disimpan sesuai capability API.

### FR-08 Dashboard
Dashboard menampilkan minimal:
- total media;
- total likes;
- total comments;
- total shares jika tersedia;
- total employee comments;
- unique employee commenters;
- statistik per akun;
- statistik per media;
- statistik per employee.

### FR-09 Employee Statistics
Untuk setiap employee:
- total comments;
- unique media commented;
- media yang dikomentari;
- periode aktivitas.

### FR-10 Leaderboard
Leaderboard dapat difilter berdasarkan:
- reporting period;
- Instagram account;
- metric.

Contoh metric:
- total comments;
- unique media commented.

Urutan leaderboard harus ditentukan secara eksplisit oleh metric yang dipilih, bukan oleh label subjektif.

### FR-11 Media Detail
Detail media menampilkan:
- caption;
- publication time;
- permalink;
- metric snapshot;
- komentar;
- commenter;
- matched employee.

### FR-12 Export
Minimal CSV untuk:
- employee activity;
- media;
- comments;
- summary report.

### FR-13 Sync Logs
Sistem mencatat:
- sync type;
- target;
- start/end;
- status;
- records processed;
- error message jika gagal.

### FR-14 Scheduled Sync
Sinkronisasi dapat dijalankan melalui Laravel Scheduler/Queue.

MVP menargetkan sinkronisasi otomatis berkala dan tombol manual.

## 7. Non-Functional Requirements

### Security
- access token terenkripsi;
- token tidak ditampilkan di UI/log;
- authorization menggunakan policy/gate;
- validasi input;
- CSRF protection;
- secret hanya di `.env`.

### Reliability
- sync idempotent;
- pagination wajib;
- retry untuk error sementara;
- sync log;
- tidak menggandakan media/comment.

### Performance
- gunakan index untuk foreign key dan external ID;
- aggregation menggunakan query database;
- sync berat dijalankan melalui queue.

### Maintainability
- controller tipis;
- business logic pada service/action;
- API client terisolasi;
- job untuk proses asynchronous;
- migration menjadi source of truth database.

## 8. Acceptance Criteria MVP

MVP dianggap berhasil apabila:

1. Laravel project berjalan dari folder `my-project`.
2. PostgreSQL terhubung.
3. Admin dapat login.
4. Admin dapat CRUD employee.
5. Admin dapat menyimpan Instagram User ID employee.
6. Admin dapat mengelola akun Instagram organisasi.
7. Reporting period dapat dibuat.
8. Media dapat disimpan melalui API resmi.
9. Comment dapat disimpan melalui API resmi.
10. Commenter dapat dicocokkan dengan employee berdasarkan User ID.
11. Dashboard dapat menghitung statistik.
12. Leaderboard dapat menampilkan ranking berdasarkan metric yang dipilih.
13. Sync bersifat idempotent.
14. Sync log tersedia.
15. Tidak ada scraping atau penggunaan private endpoint.

## 9. Future Enhancement

- historical username tracking;
- approval workflow;
- richer reports;
- scheduled report delivery;
- multi-organization tenancy;
- audit trail yang lebih lengkap;
- additional API capabilities apabila Meta menyediakan endpoint resmi yang relevan.
