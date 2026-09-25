# Database Design — Instagram Employee Engagement Tracking

## 1. Database

Database utama: **PostgreSQL**

Laravel menggunakan migration sebagai source of truth.

Database dapat dibuat melalui DBeaver/psql, tetapi tabel dibuat oleh Laravel migration.

Contoh:

```sql
CREATE DATABASE instagram_tracking;
```

Setelah itu:

```bash
php artisan migrate
```

## 2. Tables

### users

Authentication user Laravel.

Kolom mengikuti kebutuhan authentication Laravel.

---

### employees

Menyimpan data karyawan.

```text
id
employee_code              unique
name
department                 nullable
instagram_user_id          nullable, unique
instagram_username         nullable
is_active
created_at
updated_at
deleted_at                 nullable
```

Catatan:
- `instagram_user_id` adalah identity utama untuk matching.
- `instagram_username` adalah data yang dapat berubah.

---

### instagram_accounts

Menyimpan akun Instagram organisasi.

```text
id
name
instagram_user_id          unique
username
account_type               nullable
connection_status
access_token_encrypted
token_expires_at            nullable
last_synced_at              nullable
created_at
updated_at
```

Access token wajib dienkripsi.

---

### reporting_periods

Menyimpan periode pelaporan.

```text
id
name
start_at
end_at
timezone
status
created_at
updated_at
```

Constraint:

```text
start_at <= end_at
```

---

### instagram_media

Menyimpan media Instagram.

```text
id
instagram_account_id       FK
external_media_id           unique
media_type                  nullable
product_type                nullable
caption                     nullable
permalink                   nullable
thumbnail_url               nullable
published_at
raw_payload                 nullable
created_at
updated_at
```

Index:
- `instagram_account_id`
- `published_at`
- `external_media_id` unique

---

### instagram_media_metric_snapshots

Menyimpan snapshot metric.

```text
id
instagram_media_id          FK
captured_at
likes                       nullable
comments_count              nullable
shares                      nullable
saves                       nullable
reach                       nullable
impressions                 nullable
raw_payload                 nullable
created_at
updated_at
```

Metric bersifat nullable karena capability API dapat berbeda.

Index:
- `(instagram_media_id, captured_at)`

---

### instagram_comments

Menyimpan komentar.

```text
id
instagram_media_id                    FK
external_comment_id                    unique
commenter_instagram_user_id            nullable
commenter_username                     nullable
matched_employee_id                    nullable FK
text
commented_at
raw_payload                            nullable
created_at
updated_at
```

Index:
- `instagram_media_id`
- `commenter_instagram_user_id`
- `matched_employee_id`
- `commented_at`
- `external_comment_id` unique

`matched_employee_id` adalah hasil matching terhadap employee pada saat data diproses.

---

### sync_logs

Mencatat aktivitas sinkronisasi.

```text
id
instagram_account_id       nullable FK
sync_type
status
started_at
finished_at                nullable
records_processed
error_message              nullable
metadata                   nullable
created_at
updated_at
```

Index:
- `instagram_account_id`
- `sync_type`
- `status`
- `started_at`

## 3. Relationships

```text
InstagramAccount
  ├── hasMany InstagramMedia
  └── hasMany SyncLog

InstagramMedia
  ├── belongsTo InstagramAccount
  ├── hasMany InstagramComment
  └── hasMany MetricSnapshot

InstagramComment
  ├── belongsTo InstagramMedia
  └── belongsTo Employee (nullable)

Employee
  └── hasMany InstagramComment

ReportingPeriod
  └── digunakan sebagai filter berdasarkan published_at
```

## 4. Reporting Period Query

Tidak perlu membuat pivot `reporting_period_media` untuk MVP.

Media ditentukan secara dinamis:

```sql
WHERE published_at >= :start_at
  AND published_at <= :end_at
```

Jika timezone periode berbeda dengan timezone database, boundary harus dikonversi dengan benar sebelum query.

## 5. Employee Activity Query

Konsep:

```sql
SELECT
    matched_employee_id,
    COUNT(*) AS total_comments,
    COUNT(DISTINCT instagram_media_id) AS unique_media_commented
FROM instagram_comments
JOIN instagram_media
  ON instagram_media.id = instagram_comments.instagram_media_id
WHERE matched_employee_id IS NOT NULL
  AND instagram_media.published_at >= :start_at
  AND instagram_media.published_at <= :end_at
GROUP BY matched_employee_id;
```

## 6. Aggregate Media Metrics

Metric snapshot harus diambil berdasarkan snapshot yang relevan untuk report.

MVP dapat menggunakan snapshot terbaru dalam reporting context, dengan aturan yang ditetapkan pada service/report query.

Jangan menjumlahkan snapshot historis dari waktu yang berbeda karena dapat menyebabkan double counting.

## 7. Uniqueness and Idempotency

External identity wajib unique:

```text
instagram_accounts.instagram_user_id
instagram_media.external_media_id
instagram_comments.external_comment_id
```

Sync menggunakan upsert sehingga menjalankan sync dua kali tidak membuat duplicate record.

## 8. Username Changes

Username tidak menjadi primary identity.

Jika username berubah:
- update `instagram_username`;
- `instagram_user_id` tetap;
- employee tetap sama;
- comment lama tidak dipindahkan ke employee baru.

Untuk kebutuhan audit yang lebih lanjut, dapat ditambahkan:

```text
employee_instagram_identity_histories
```

tetapi tabel tersebut bukan kebutuhan MVP.

## 9. Soft Delete

Employee dapat menggunakan `deleted_at` agar history activity tetap tersedia.

Jangan hard-delete employee jika masih direferensikan oleh comment history kecuali ada aturan data-retention yang jelas.

## 10. Migration Order

Disarankan:

1. users/authentication
2. employees
3. instagram_accounts
4. reporting_periods
5. instagram_media
6. instagram_media_metric_snapshots
7. instagram_comments
8. sync_logs

## 11. Database Rules

- Gunakan UUID atau BIGINT sesuai standar Laravel project; pilih satu dan konsisten.
- Foreign key harus memiliki constraint.
- External ID wajib unique.
- Nullable hanya jika API/data memang dapat tidak menyediakan nilai.
- Jangan menyimpan access token plaintext.
- Raw payload harus dipertimbangkan dari sisi ukuran, retention, dan privacy.
