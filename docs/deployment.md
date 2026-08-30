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
