# NG Home Cleaners

Production Laravel application for NG Home Cleaners — a customer-facing cleaning website and lightweight internal CRM.

**Current status:** Public site (services, areas, reviews, move-in/move-out, one-page estimate with optional property photos), Filament CRM (leads, bookings, FAQs, legal pages, media), sitemap/SEO, and Plausible analytics after cookie consent. Walkthrough videos stay on WhatsApp — they are never uploaded to the app.

## Stack

- Laravel 13 · PHP 8.3+ · PostgreSQL
- Blade · Livewire 4 · Tailwind CSS 4 · Vite
- Filament 5 admin CRM at `/admin`
- Pest 4 · database queues

## Local installation

### Requirements

- PHP 8.3+
- Composer
- Node.js 18+
- PostgreSQL 15+

### Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`, and set `APP_URL` to the exact URL you use in the browser (for `php artisan serve` that is usually `http://127.0.0.1:8000`):

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ng_home_cleaners
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create the database, then:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
composer run dev
```

Object storage on Laravel Cloud: attach a bucket in the dashboard (injects `FILESYSTEM_DISK=s3` + AWS vars), then set `AWS_URL` to the bucket’s public URL. Details in `docs/deployment.md`.

### Local admin login (seeded in non-production)

- URL: http://127.0.0.1:8000/admin
- Email: `admin@nghomecleaners.co.uk`
- Password: `password`

### Health check

`GET /up` — configured in `bootstrap/app.php`

### CRM image uploads (Windows)

Livewire’s `TemporaryUploadedFile` is overridden in `app/Overrides/Livewire/` so uploads never call PHP `tmpfile()` (which fails on some Windows TEMP / antivirus setups). Composer maps that class instead of the vendor copy — run `composer dump-autoload` after pull. Temp placeholders still use `storage/app/tmp`.

### Mail

Production uses **Resend**: set `MAIL_MAILER=resend` and `RESEND_API_KEY` (see `.env.example`). The `resend/resend-php` package is required.

For local development without sending mail, use `MAIL_MAILER=log` (writes to `storage/logs/laravel.log`) or Mailpit via the commented SMTP block in `.env.example`.

## Tests

```bash
php artisan test
```

```bash
composer run lint
composer audit
npm run build
npm audit
```

## Documentation

- [Architecture](docs/architecture.md)
- [Testing](docs/testing.md)
- [Deployment](docs/deployment.md)
- [Reference audit](docs/reference-audit.md)
- [Reference pricing](docs/reference-pricing.md) (for future pricing engine)
