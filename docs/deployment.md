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
- Resend mail: `MAIL_MAILER=resend` and `RESEND_API_KEY` (verified domain in Resend matching `MAIL_FROM_ADDRESS`)
- `SESSION_SECURE_COOKIE=true` (or leave unset — defaults to secure when `APP_ENV=production`)
- Optional: `ANALYTICS_ENABLED` / `ANALYTICS_DRIVER` only if a tracker is configured

## Object storage (Laravel Cloud)

Laravel Cloud containers are ephemeral — do **not** rely on `storage/app/public` in production.

1. Install is already done: `league/flysystem-aws-s3-v3` is in `composer.json`.
2. In Laravel Cloud, create an **Object Storage** bucket and attach it to the environment.
3. Cloud injects `FILESYSTEM_DISK=s3` plus `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, and `AWS_ENDPOINT`.
4. For public image URLs (logos, gallery, service photos), copy the bucket’s public base URL from the bucket settings page into a custom env var:

```env
AWS_URL=https://your-bucket-public-url
```

5. CRM uploads use the configured `media` disk (`config/filesystems.php`), which resolves to `s3` when `FILESYSTEM_DISK=s3`.
6. Keep Livewire temporary uploads on `local` (`LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local`) — they only need to survive the upload request; permanent files go to S3.
7. `php artisan storage:link` is only needed for local/public disk development, not for Cloud S3.

Local development stays on the `public` disk (`FILESYSTEM_DISK=local`). Run `php artisan storage:link` once locally.

## Health check

Monitor `GET /up` — returns 200 when the application is healthy.

## Queue worker

```bash
php artisan queue:work --tries=3
```

Quote acknowledgement and internal lead mail are queued; failures are logged and reported without undoing the saved lead.

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
