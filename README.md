# Instagram Employee Engagement Tracking

Internal web application for monitoring Instagram organization accounts and employee engagement activity.

## Stack

- Laravel 13
- Inertia.js
- React
- TypeScript
- Tailwind CSS
- PostgreSQL

## Project Root

**This folder (`my-project`) is the Laravel root project.**

Do not create another Laravel project inside `my-project/src`.

Standard Laravel structure is directly under this folder.

## Documentation

Read:

```text
.docs/
├── PRD.md
├── ARCHITECTURE.md
├── TASKS.md
└── DATABASE.md
```

Agent instructions:

```text
.gemini.md
```

## Local Setup

From this directory:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure PostgreSQL in `.env`.

Example:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=instagram_tracking
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create the database in PostgreSQL/DBeaver:

```sql
CREATE DATABASE instagram_tracking;
```

Then:

```bash
php artisan migrate
```

Run the application:

```bash
php artisan serve
```

In another terminal:

```bash
npm run dev
```

The project remains rooted at `my-project`.

## Queue

When sync jobs are implemented:

```bash
php artisan queue:work
```

## Important API Boundary

The application must use official Instagram/Meta APIs.

Do not use scraping or private endpoints.

User-level employee tracking should only be implemented when the official API exposes the required identity.

Instagram User ID is treated as the stable identity; username is mutable.
