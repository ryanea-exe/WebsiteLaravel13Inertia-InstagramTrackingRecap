# Development Tasks — Instagram Employee Engagement Tracking

## Cara Menggunakan Dokumen Ini

Implementasi dilakukan bertahap.

**Jangan langsung mengerjakan semua phase sekaligus.**

Setiap phase:
1. baca requirement terkait;
2. implementasikan;
3. jalankan test/validation;
4. review hasil;
5. baru lanjut ke phase berikutnya.

## Phase 0 — API Capability Verification

- [ ] Verifikasi dokumentasi API Instagram/Meta resmi yang akan digunakan.
- [ ] Verifikasi endpoint media.
- [ ] Verifikasi endpoint comments.
- [ ] Verifikasi metric yang tersedia.
- [ ] Verifikasi authentication dan permission/scopes.
- [ ] Catat hasil verifikasi.
- [ ] Jangan implementasi capability yang belum terbukti.

**Output:** API capability note.

---

## Phase 1 — Laravel 13 Bootstrap

- [ ] Pastikan `my-project` adalah root Laravel.
- [ ] Jangan membuat `my-project/src`.
- [ ] Install Laravel 13.
- [ ] Configure Inertia.
- [ ] Configure React.
- [ ] Configure TypeScript.
- [ ] Configure Tailwind.
- [ ] Configure PostgreSQL.
- [ ] Pastikan `php artisan serve` berjalan dari root.
- [ ] Pastikan Vite berjalan.

**Acceptance:**
```bash
php artisan serve
npm run dev
```
berjalan dari `my-project`.

---

## Phase 2 — Authentication

- [ ] Install/configure authentication.
- [ ] Login.
- [ ] Logout.
- [ ] Protected routes.
- [ ] Basic role authorization.

---

## Phase 3 — Database Schema

Task Sequence:
- 32C-1 Documentation Sync & Design Lock
- 32C-2 Database Migrations
- 32C-3 Eloquent Models & Relationships
- 32C-4 Factories / Seeders / Automated Tests
- 32C-QA Database Verification

Future phases:
- 32D Instagram Account Management
- 32E Meta API Connection
- 32F Media Sync
- 32G Comment Sync
- 32H Employee Instagram Linking
- 32I Ranking & Reporting
- 32J Dashboard / Statistics
- 32K Final Instagram Integration QA

Tambahkan:
- [ ] foreign keys
- [ ] unique constraints
- [ ] indexes
- [ ] casts
- [ ] relationships

Run:

```bash
php artisan migrate
```

---

## Phase 4 — Employee Management

- [ ] Employee list.
- [ ] Create employee.
- [ ] Edit employee.
- [ ] Deactivate employee.
- [ ] Instagram User ID field.
- [ ] Instagram username field.
- [ ] Validation.
- [ ] Prevent duplicate Instagram User ID.

---

## Phase 5 — Instagram Account Management

- [ ] Account list.
- [ ] Add organization account.
- [ ] Store Instagram User ID.
- [ ] Store username.
- [ ] Connection status.
- [ ] Secure token storage.
- [ ] Manual sync button placeholder.
- [ ] Last sync display.

---

## Phase 6 — Instagram API Client

- [ ] Implement official API client.
- [ ] Centralize HTTP requests.
- [ ] Handle authentication.
- [ ] Handle pagination.
- [ ] Normalize responses.
- [ ] Handle rate limit.
- [ ] Handle transient errors.
- [ ] Avoid token logging.
- [ ] Write API client tests using mocks/fakes.

---

## Phase 7 — Media Sync

- [ ] Fetch media.
- [ ] Paginate.
- [ ] Upsert media.
- [ ] Store external media ID.
- [ ] Store published time.
- [ ] Store permalink.
- [ ] Store caption where available.
- [ ] Record sync log.
- [ ] Ensure idempotency.

---

## Phase 8 — Comment Sync

- [ ] Fetch comments.
- [ ] Paginate.
- [ ] Upsert comments.
- [ ] Store commenter User ID where available.
- [ ] Store commenter username.
- [ ] Match employee by User ID.
- [ ] Record sync log.
- [ ] Ensure idempotency.

---

## Phase 9 — Reporting Period

- [ ] CRUD reporting period.
- [ ] Validate start <= end.
- [ ] Store timezone.
- [ ] Activate/deactivate period.
- [ ] Create reusable reporting filter/query service.
- [ ] Test exact boundary timestamps.

---

## Phase 10 — Metrics and Statistics

- [ ] Store metric snapshots.
- [ ] Define latest-snapshot selection.
- [ ] Aggregate media metrics.
- [ ] Aggregate employee comments.
- [ ] Count unique employee commenters.
- [ ] Count unique media commented.
- [ ] Filter by account.
- [ ] Filter by reporting period.

---

## Phase 11 — Leaderboard

- [ ] Metric selector.
- [ ] Total comments metric.
- [ ] Unique media commented metric.
- [ ] Deterministic ordering.
- [ ] Tie-breaker.
- [ ] Period filter.
- [ ] Account filter.

Do not implement subjective labels such as "best employee".

---

## Phase 12 — Dashboard

- [ ] Summary cards.
- [ ] Account comparison.
- [ ] Media performance.
- [ ] Employee activity.
- [ ] Period filter.
- [ ] Account filter.
- [ ] Charts where useful.
- [ ] Empty state.
- [ ] Loading state.
- [ ] Error state.

---

## Phase 13 — Media Detail

- [ ] Media information.
- [ ] Permalink.
- [ ] Metric summary.
- [ ] Metric history if available.
- [ ] Comments.
- [ ] Matched employees.
- [ ] Pagination.

---

## Phase 14 — Export

- [ ] Employee activity CSV.
- [ ] Media CSV.
- [ ] Comment CSV.
- [ ] Summary CSV.
- [ ] Respect current filters.

---

## Phase 15 — Scheduled Sync

- [ ] Create sync jobs.
- [ ] Configure Laravel scheduler.
- [ ] Configure queue.
- [ ] Add retry/backoff.
- [ ] Add sync log.
- [ ] Add manual sync.
- [ ] Prevent overlapping sync when necessary.

Target behavior:
- automatic periodic sync;
- manual sync available to Admin.

---

## Phase 16 — QA

Test:
- [ ] authentication;
- [ ] authorization;
- [ ] employee CRUD;
- [ ] duplicate Instagram User ID;
- [ ] media duplicate sync;
- [ ] comment duplicate sync;
- [ ] employee matching;
- [ ] username changes;
- [ ] reporting period boundary;
- [ ] empty dataset;
- [ ] API error;
- [ ] pagination;
- [ ] queue failure;
- [ ] export.

---

## Phase 17 — Documentation

- [ ] Update README.
- [ ] Update PRD if requirements changed.
- [ ] Update ARCHITECTURE if implementation differs.
- [ ] Update DATABASE if schema differs.
- [ ] Update `.gemini.md` when development rules change.

## Definition of Done

A task/phase is done when:
- implementation exists;
- tests/validation pass;
- no known critical error remains;
- security rules are satisfied;
- documentation is consistent;
- project still runs from root `my-project`.

## Important Stop Conditions

Stop and ask for clarification rather than guessing if:
- official API capability is unclear;
- permission/scope is unclear;
- API response differs materially from assumptions;
- database requirement conflicts with existing migration;
- requested feature requires unsupported user-level Instagram data.
