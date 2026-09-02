# Deployment

## Requirements

- PHP 8.3+
- PostgreSQL
- Node.js 18+ (build step only)
- Process manager for `php artisan queue:work`

## Build

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci && npm run build
```

## Environment

Set in production `.env` (never commit secrets):

- `APP_ENV=production`
- `APP_DEBUG=false` — customers must never see stack traces
- `APP_URL=https://…` (HTTPS)
- PostgreSQL credentials
- `QUEUE_CONNECTION=database` (or Redis when available)
- `CACHE_STORE=redis` in production when Redis is attached (Laravel Cloud cache / Redis). Database cache works but adds extra PostgreSQL load for settings and pricing.
- Resend mail: `MAIL_MAILER=resend`, `RESEND_API_KEY`, and `MAIL_FROM_ADDRESS` on a **verified Resend domain** (e.g. `hello@nghomecleaners.co.uk`). `hello@example.com` will be rejected.
- `SESSION_SECURE_COOKIE=true` (or leave unset — defaults to secure when `APP_ENV=production`)
- Optional analytics: `ANALYTICS_ENABLED=true`, `ANALYTICS_DRIVER=plausible`, and `PLAUSIBLE_DOMAIN=your-domain`. The Plausible script loads only after cookie consent.

## Object storage (Laravel Cloud)

Laravel Cloud containers are ephemeral — do **not** rely on `storage/app/public` in production.

1. Install is already done: `league/flysystem-aws-s3-v3` is in `composer.json`.
2. In Laravel Cloud, create an **Object Storage** bucket and attach it to the environment.
3. Cloud injects `FILESYSTEM_DISK=private` (or similar), `LARAVEL_CLOUD_DISK_CONFIG` (bucket credentials), and related values. **Do not copy `AWS_BUCKET`, `AWS_ENDPOINT`, or `AWS_ACCESS_KEY_ID` into custom env vars** — that makes the app target the static `s3` disk in `config/filesystems.php`, which has no credentials on Cloud. Uploads then fail silently and the bucket stays empty.
4. The only storage-related custom env var you usually need is the public URL for images:

```env
AWS_URL=https://your-bucket-public-url.laravel.cloud
```

5. CRM uploads use `App\Support\Media::diskName()`, which resolves to Cloud’s injected default object storage disk (commonly named `private`). **Do not set `MEDIA_DISK=public` on Cloud** — uploads would land on the ephemeral container and disappear.
6. After attaching a bucket or changing storage env vars, run **`php artisan config:clear`** (or redeploy) so config cache picks up the new values.
7. Keep Livewire temporary uploads on `local` (`LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local`) — they only need to survive the upload request; permanent files go to S3.
8. `php artisan storage:link` is only needed for local/public disk development, not for Cloud S3.

Local development stays on the `public` disk (`FILESYSTEM_DISK=local`). Run `php artisan storage:link` once locally.

## Health check

Monitor `GET /up` — returns 200 when the application is healthy.

## Laravel Cloud processes

You do **not** need a custom forever-running Artisan command for new-lead emails. Acknowledgement and internal lead emails are sent **immediately** during the HTTP request (`Mail::send`), so they succeed or fail in the same request.

### Required for delayed mail (follow-ups / review requests)

Pick one queue option in the Cloud environment:

1. **Managed Queues** (preferred) — create a managed queue in the environment dashboard, **or**
2. **Background process** on the App compute cluster:

```bash
php artisan queue:work --tries=3
```

Without a queue worker, 24-hour lead follow-ups and post-booking review emails will sit in the `jobs` table and never send.

### Required for the scheduler

In the environment **App** (or Worker) compute cluster, enable the **Scheduler** toggle and redeploy. Cloud then runs `php artisan schedule:run` for you.

That drives `SendQuoteFollowUpJob` (hourly in `routes/console.php`).

### Not required as custom commands

- Do **not** add `php artisan schedule:work` as a background process on Cloud (use the Scheduler toggle instead).
- Do **not** expect a queue worker to fix Resend sandbox errors — those fail during send, not because a worker is missing.

### Resend sandbox reminder

Until a domain is verified at resend.com/domains:

- `MAIL_FROM_ADDRESS=onboarding@resend.dev`
- Site settings **Lead notification emails** must be only your Resend account email
- Customer/lead email must also be that same address for test sends

After deploy, run `php artisan migrate --force` so Cloud picks up schema changes (`email_templates`, `property_photo_paths`, follow-up timestamps, etc.).

## Admin users

Do not rely on `AdminUserSeeder` in production. Create admin users manually or via a secure one-off command.

## Quality gates before release

```bash
composer run format   # or: vendor/bin/pint
composer run lint     # pint --test
php artisan test
npm run build
composer audit
npm audit
```
