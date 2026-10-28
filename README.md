# Boston English Center Conversation Room Survey

An anonymous, database-backed Conversation Room feedback survey with a protected administration area, analytics, PDF reports, feedback review, survey versioning, and alert monitoring.

## Requirements

- PHP 8.4 with the extensions required by Laravel and Dompdf
- Composer 2
- Node.js 20.19+ or 22.12+ and npm
- MySQL 8 or MariaDB 10.6+
- A web server whose document root is the `public` directory

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

The public survey is available at `/`; administration starts at `/login`. Public registration is intentionally disabled.

Create the first administrator in an interactive `php artisan tinker` session. Replace the sample values and use a unique password:

```php
$user = App\Models\User::create(['name' => 'Administrator', 'email' => 'admin@example.com', 'password' => 'replace-with-a-strong-password']);
$user->forceFill(['is_admin' => true, 'email_verified_at' => now()])->save();
```

## Quality checks

```bash
php artisan test --compact
npm test
npm run build
composer validate --strict
composer audit
npm audit
```

## Production deployment

Set production values in `.env`; never commit that file. At minimum, configure `APP_KEY`, `APP_URL`, the database, mail, and these safe defaults:

```dotenv
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true
SURVEY_PUBLIC_SESSION_DRIVER=cookie
SURVEY_PUBLIC_SESSION_COOKIE=bec-survey-anonymous
SURVEY_PUBLIC_CACHE_SECONDS=3600
SURVEY_ANALYTICS_CACHE_FRESH_SECONDS=15
SURVEY_ANALYTICS_CACHE_STALE_SECONDS=60
```

The public survey uses an encrypted cookie session so anonymous visitors are not added to the server-side `sessions` table. Admin sessions can continue to use the configured `SESSION_DRIVER`. For a multi-server deployment, use a shared Redis or database cache and session store.

Deploy each release with:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

Run Laravel's scheduler every minute so survey alerts are evaluated:

```cron
* * * * * cd /path/to/bec-survey && php artisan schedule:run >> /dev/null 2>&1
```

Before opening the survey to real users:

- Serve the site over HTTPS and redirect HTTP to HTTPS.
- Confirm the web server points only to `public/` and can write to `storage/` and `bootstrap/cache/`.
- Configure automated database backups and test a restore.
- Configure centralized error monitoring and log retention without recording survey answers.
- Smoke-test survey submission, admin login, analytics, and PDF export on the production domain.
- Monitor the `/up` health endpoint and database capacity.

After changing environment settings on a running server, rebuild Laravel's caches with `php artisan optimize`.
