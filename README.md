# Boston English Center Conversation Room Survey

This repository contains the Boston English Center Conversation Room feedback application. It gives students an anonymous, mobile-friendly survey and gives authorized administrators a protected workspace for reviewing results, managing the questionnaire, monitoring recurring concerns, and exporting reports.

The application is built with Laravel 13, PHP, MySQL, Blade, Tailwind CSS, Alpine.js, Chart.js, GSAP, Vite, and Dompdf. It is a single-organization application for Boston English Center; it is not a multi-tenant SaaS platform.

## What the application provides

- A public anonymous student survey at `/`.
- Four seeded survey sections and 14 numbered questions.
- Single-choice, multiple-choice, grouped-choice, and long-text answers.
- Back/Next navigation, progress feedback, validation, and a completion page.
- A protected administrator dashboard at `/dashboard`.
- Date filters, charts, response totals, recurring-answer rankings, and written feedback review.
- Draft-based question and section management with preview and publishing.
- Survey versioning so published changes do not rewrite historical responses.
- Feedback alerts evaluated by the Laravel scheduler.
- Filter-aware PDF analytics reports.
- Automated PHP and JavaScript tests.

## User flows

### Student flow

1. A student opens `/` without creating an account.
2. `ConversationRoomSurveyController` asks `SurveyDraftManager` for the active published survey.
3. The page displays the active sections and questions one step at a time.
4. The student selects answers or enters written feedback and moves using Back and Next.
5. Browser-side logic provides immediate navigation and selection feedback; the server remains the final authority for validation.
6. On submission, `StoreConversationRoomResponseRequest` verifies the survey version, required answers, option ownership, grouped answers, selection limits, and text length.
7. `ConversationRoomSurveyController` stores the response and its normalized answers inside one database transaction.
8. Analytics caches are invalidated, alert evaluation is deferred, and the student is redirected to `/conversation-room/thank-you`.

The public flow uses an encrypted cookie session configured by `UseAnonymousSurveySession`. It does not require or create a student account, and it does not intentionally collect student identity. Anonymous submissions are not proof of unique students: rate limiting reduces abuse, but the application does not currently prevent one person from submitting multiple responses.

### Administrator flow

1. An administrator opens `/login` and signs in with a verified user whose `is_admin` value is true.
2. `/dashboard` shows the selected reporting period, response totals, attendance patterns, barriers, preferred formats and times, attendance intentions, and recent written feedback.
3. `/admin/common-answers` provides detailed rankings, question and section filters, written-feedback search, theme review, and alert evidence.
4. `/admin/questions` opens the questionnaire manager. Administrators can create and edit sections, create or edit questions, manage options, duplicate, hide, delete, move, reorder, and preview draft content.
5. Publishing promotes the draft to the new active survey and archives the previous active version. Existing responses remain associated with their original version.
6. The notification bell displays stored survey alerts. Administrators can mark alerts as read or resolved.
7. “Download PDF” exports the analytics for the currently applied date filter using a session-scoped snapshot.
8. `/profile` manages the administrator’s profile and password.

Public registration is intentionally disabled. A normal visitor cannot create an administrator account through the website.

## Application architecture and data flow

The application follows the standard Laravel request lifecycle:

```text
Browser request
    -> route and middleware
    -> controller
    -> Form Request validation and authorization
    -> domain service or SurveyDraftManager
    -> Eloquent models and MySQL transaction
    -> Blade view or redirect
```

Survey definitions and submitted answers are database-driven. The public page does not contain a hardcoded list of live questions. The seeders create the initial questionnaire, while the administrator interface creates draft versions for later changes. Analytics and Common Answers use the same `SurveyAnalyticsService`, which keeps their filters and totals consistent. Public survey view data and analytics are cached for speed and invalidated when relevant data changes.

## Important routes

| Method and path | Purpose | Access |
| --- | --- | --- |
| `GET /` | Display the active student survey | Public |
| `POST /conversation-room/responses` | Validate and store a response | Public, rate-limited |
| `GET /conversation-room/thank-you` | Display completion confirmation | Public |
| `GET /login` | Administrator login | Public |
| `GET /dashboard` | Main survey analytics | Verified administrator |
| `GET /admin/common-answers` | Rankings and feedback review | Verified administrator |
| `GET /admin/questions` | Question and section management | Verified administrator |
| `GET /admin/survey-preview` | Preview the current draft | Verified administrator |
| `POST /admin/published-survey` | Publish the validated draft | Verified administrator |
| `POST /admin/reports/pdf` | Generate a filtered PDF report | Verified administrator, rate-limited |
| `GET /profile` | Administrator profile and password settings | Authenticated user |
| `GET /up` | Laravel health endpoint | Public/monitoring |

Run `php artisan route:list --except-vendor` to see the complete route list.

## Important files and directories

### Routes and configuration

| Path | Responsibility |
| --- | --- |
| `routes/web.php` | Public survey, dashboard, Common Answers, reports, alerts, question management, and profile routes. |
| `routes/auth.php` | Login, password reset, password confirmation, and email verification routes. Registration is disabled. |
| `routes/console.php` | Schedules daily survey-alert evaluation without overlapping runs. |
| `config/survey.php` | Survey cache durations, PDF limits, alert thresholds, and related application settings. |
| `config/database.php` | Database and cache-database connection definitions. |
| `config/session.php` | Administrator session configuration; the public survey overrides this with its anonymous cookie session. |
| `.env.example` | Safe environment-variable template. Copy it to `.env`; never commit the populated `.env`. |

### Controllers

| Path | Responsibility |
| --- | --- |
| `app/Http/Controllers/ConversationRoomSurveyController.php` | Loads the active survey, stores anonymous responses atomically, and shows the thank-you page. |
| `app/Http/Controllers/DashboardController.php` | Builds the main administrator analytics page and its PDF snapshot token. |
| `app/Http/Controllers/Admin/AnalyticsDataController.php` | Returns filtered analytics data used by the dashboard. |
| `app/Http/Controllers/Admin/CommonAnswersController.php` | Builds detailed answer rankings, filters, and written-feedback results. |
| `app/Http/Controllers/Admin/AnalyticsReportController.php` | Generates protected, rate-limited PDF exports from analytics snapshots. |
| `app/Http/Controllers/Admin/FeedbackReviewController.php` | Saves administrator review categories for written answers. |
| `app/Http/Controllers/Admin/SurveyQuestionController.php` | Creates, updates, hides, and deletes draft questions. |
| `app/Http/Controllers/Admin/SurveySectionController.php` | Creates, updates, and deletes draft sections. |
| `app/Http/Controllers/Admin/SurveyQuestion*Controller.php` | Handles question duplication, movement, and full ordering. |
| `app/Http/Controllers/Admin/SurveySection*Controller.php` | Handles section movement and ordering. |
| `app/Http/Controllers/Admin/SurveyPreviewController.php` | Renders the draft survey using the student-page design. |
| `app/Http/Controllers/Admin/PublishedSurveyController.php` | Validates and publishes the current draft survey version. |
| `app/Http/Controllers/Admin/SurveyAlert*Controller.php` | Marks alert notifications as read, read-all, or resolved. |

### Services, validation, and middleware

| Path | Responsibility |
| --- | --- |
| `app/SurveyDraftManager.php` | Central survey-versioning service: loads public survey data, creates drafts, edits structure, previews, publishes, and protects historical versions. |
| `app/Services/SurveyAnalyticsService.php` | Calculates dashboard and Common Answers metrics from stored responses and manages analytics caching. |
| `app/Services/SurveyAlertService.php` | Evaluates answer thresholds and reviewed feedback, then creates or updates stored alerts. |
| `app/Services/PdfReportService.php` | Renders filtered analytics into a Dompdf document with local asset restrictions. |
| `app/Services/AnalyticsSnapshotService.php` | Stores short-lived, session-scoped analytics snapshots for consistent PDF exports. |
| `app/Support/AnalyticsFilter.php` | Represents today, last-seven-days, month, and custom reporting periods. |
| `app/Http/Requests/StoreConversationRoomResponseRequest.php` | Validates the complete student submission against active database questions and options. |
| `app/Http/Requests/StoreSurveyQuestionRequest.php` | Validates administrator-created questions, options, grouped answers, and selection limits. |
| `app/Http/Requests/Admin/AnalyticsFilterRequest.php` | Validates analytics, Common Answers, and report filters. |
| `app/Http/Middleware/EnsureUserIsAdmin.php` | Rejects authenticated users who are not administrators. |
| `app/Http/Middleware/UseAnonymousSurveySession.php` | Separates anonymous public survey sessions from administrator sessions. |
| `app/Http/Middleware/AddSecurityHeaders.php` | Adds browser security headers, including HSTS when HTTPS is active. |
| `app/Console/Commands/EvaluateSurveyAlerts.php` | Artisan command executed by the scheduler to evaluate survey alerts. |

### Models and database records

| Model | Stored information |
| --- | --- |
| `SurveyVersion` | Draft, active, and archived questionnaire versions. |
| `SurveySection` | Ordered sections belonging to one survey version. |
| `SurveyQuestion` | Prompt, type, help text, position, required state, visibility, and stable identity. |
| `SurveyQuestionOption` | Selectable answer labels, icons, tones, order, and optional grouped-choice name. |
| `ConversationRoomResponse` | One anonymous survey submission and its submission timestamp. |
| `SurveyAnswer` | One normalized selected option or written answer attached to a response and question. |
| `ConversationRoomResponseChoice` | Compatibility records for multi-select analytics fields. |
| `FeedbackTheme` and `FeedbackThemeAssignment` | Administrator-defined review categories attached to written feedback. |
| `SurveyAlert` | Stored alert status, evidence, counts, read state, and resolution state. |
| `User` | Administrator authentication, password hash, verification timestamp, and `is_admin` permission. |

Database table definitions, indexes, foreign keys, and historical-data protections are in `database/migrations`. Model factories used by tests are in `database/factories`.

### Views, JavaScript, styles, and assets

| Path | Responsibility |
| --- | --- |
| `resources/views/conversation-room/create.blade.php` | Student survey shell, progress display, section track, form, and navigation. |
| `resources/views/conversation-room/partials/question.blade.php` | Renders each database question according to its answer type. |
| `resources/views/conversation-room/thank-you.blade.php` | Completion page after a successful submission. |
| `resources/views/dashboard.blade.php` | Main administrator dashboard, chart containers, filters, KPIs, and empty states. |
| `resources/views/admin/common-answers.blade.php` | Detailed rankings and written-feedback review interface. |
| `resources/views/admin/questions/index.blade.php` | Section/question editor, ordering tools, answer-option editor, preview controls, and publishing actions. |
| `resources/views/admin/reports/analytics.blade.php` | Printable server-rendered PDF layout. |
| `resources/views/auth/login.blade.php` | Branded administrator login page. |
| `resources/views/layouts/app.blade.php` | Authenticated administrator navigation and page frame. |
| `resources/views/layouts/guest.blade.php` | Guest/authentication page frame. |
| `resources/js/app.js` | Alpine startup, student survey behavior, dashboard behavior, and shared client interactions. |
| `resources/js/survey-answers.js` | Creates and restores answer-state objects for every question type. |
| `resources/js/survey-stepper.js` | Calculates responsive section-step visibility and density. |
| `resources/js/survey-question-manager.js` | Administrator editor state, API calls, ordering, option editing, preview, and publishing behavior. |
| `resources/css/app.css` | Tailwind entry point and the application’s custom responsive styles. |
| `public/images/conversation-room/` | Boston English Center logo and Conversation Room background image. |

### Seeders and tests

| Path | Responsibility |
| --- | --- |
| `database/seeders/DatabaseSeeder.php` | Runs the initial survey and feedback-theme seeders. |
| `database/seeders/SurveyQuestionSeeder.php` | Creates the four original sections, 14 questions, grouped time-zone answer, and answer options. |
| `database/seeders/FeedbackThemeSeeder.php` | Creates the default feedback review categories. |
| `database/seeders/DemoSurveyResponseSeeder.php` | Creates demonstration responses only when explicitly requested; it is not part of normal production seeding. |
| `tests/Feature/` | HTTP, authentication, security, survey, analytics, PDF, alerts, versioning, and question-management tests. |
| `tests/JavaScript/` | Answer-state and responsive stepper tests. |

## Database relationships

```text
SurveyVersion
    -> SurveySection
        -> SurveyQuestion
            -> SurveyQuestionOption

ConversationRoomResponse
    -> SurveyAnswer
        -> SurveyQuestion
        -> optional SurveyQuestionOption

SurveyAnswer
    -> FeedbackThemeAssignment
        -> FeedbackTheme

SurveyAlert
    -> stored evidence referencing relevant responses
```

Questions and sections use stable identifiers across survey versions. Publishing creates a new active definition rather than editing the old published definition in place. Foreign-key rules prevent questions referenced by historical answers from being silently removed.

## Requirements

- PHP 8.3 or newer; PHP 8.4 is the currently tested development runtime.
- Composer 2.
- Node.js 20.19+ or 22.12+ and npm.
- MySQL 8+ or MariaDB 10.6+.
- The PHP `pdo_mysql` extension.
- PHP extensions required by Laravel, MySQL, and Dompdf.
- A web server whose document root is the repository’s `public` directory.

## Database connection used by this project

The application uses **MySQL** as its normal local and production database. The active driver is selected by `DB_CONNECTION=mysql` in `.env`. The repository also supports Laravel’s other configured drivers, and the isolated PHP tests use SQLite in memory, but SQLite is not the documented production database for this project.

The supplied `.env.example` contains local XAMPP-style development defaults:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bec_survey
DB_USERNAME=root
DB_PASSWORD=
```

These are examples for a developer’s local computer, not safe hosting credentials. On the destination server, the tech lead or hosting provider must create a dedicated database and database user, grant that user access to the database, and replace `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` with the values supplied by the host. Do not use the MySQL `root` account or a blank password in production.

Some managed hosting platforms provide one `DB_URL` value instead of separate connection fields. Laravel’s MySQL configuration supports that value. If the provider requires an encrypted MySQL connection, configure its certificate path through `MYSQL_ATTR_SSL_CA` as instructed by the provider.

This project also defaults to database-backed administrator sessions, application cache, and queues:

```dotenv
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

The standard migrations create the required `sessions`, `cache`, and `jobs` tables. For multiple application servers, a shared database or Redis service must be used so all servers see the same sessions and cache. The anonymous student survey uses its separate encrypted cookie session and does not create a database session for each student.

After configuring `.env`, verify the selected connection without exposing credentials:

```bash
php artisan config:show database.default
php artisan migrate:status
```

If environment values change after Laravel configuration has been cached, run `php artisan optimize:clear`, verify the connection, and rebuild the production caches with `php artisan optimize`.

## Local installation

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create the environment file

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure at least the following MySQL values in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bec_survey
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Do not commit `.env` or share its secrets.

### 3. Create the schema and original survey

```bash
php artisan migrate --seed
```

Normal seeding creates the original survey and feedback themes. It does not create demo responses or an administrator account.

If an existing local database has an active survey version but displays zero questions, run the idempotent seeders and clear the stale public-survey cache:

```bash
php artisan db:seed
php artisan cache:clear
```

Do not run `migrate:fresh`, `db:wipe`, or destructive database commands against a database containing responses.

### 4. Create the first administrator

Public registration is disabled. Create the first administrator from an interactive Tinker session:

```bash
php artisan tinker
```

Then run the following PHP, replacing the sample values with the real administrator details and a unique password:

```php
$user = App\Models\User::create([
    'name' => 'Administrator',
    'email' => 'admin@example.com',
    'password' => 'replace-with-a-strong-unique-password',
]);

$user->forceFill([
    'is_admin' => true,
    'email_verified_at' => now(),
])->save();
```

The `User` model hashes the password automatically. Exit Tinker after the account is created. Never place a real password in this README, Git history, a seeder, or a shared command log.

### 5. Build and run the application

For a production-style frontend build:

```bash
npm run build
php artisan serve
```

For active frontend development, use two terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

Open:

- Student survey: `http://127.0.0.1:8000/`
- Administrator login: `http://127.0.0.1:8000/login`

## Development and quality checks

Run the PHP suite:

```bash
php artisan test --compact
```

Run the JavaScript suite:

```bash
npm test
```

Build production frontend assets:

```bash
npm run build
```

Validate and audit dependencies:

```bash
composer validate --strict
composer audit
npm audit
```

When PHP files have changed, format them before committing:

```bash
vendor/bin/pint --dirty --format agent
```

The PHP test configuration uses an isolated in-memory SQLite database. Tests do not need or modify the configured local MySQL survey database. The last handoff verification completed 75 PHP tests with 325 assertions, 12 JavaScript tests, a successful Vite production build, and dependency audits with no reported vulnerabilities. Run the commands again on the receiving computer because dependency and environment state can change.

## Production deployment

### Required environment configuration

Create a production `.env` on the server and never commit it. Configure the application key, public URL, database, mail transport, cache, sessions, logging, and these safe defaults:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://survey.example.com
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true
SURVEY_PUBLIC_SESSION_DRIVER=cookie
SURVEY_PUBLIC_SESSION_COOKIE=bec-survey-anonymous
SURVEY_PUBLIC_CACHE_SECONDS=3600
SURVEY_ANALYTICS_CACHE_FRESH_SECONDS=15
SURVEY_ANALYTICS_CACHE_STALE_SECONDS=60
```

Use a unique production `APP_KEY`; do not copy a key from another application. For multi-server deployment, use shared Redis or database-backed cache and administrator sessions.

The production database block must use the credentials created in the hosting control panel or managed database service, for example:

```dotenv
DB_CONNECTION=mysql
DB_HOST=database-host-provided-by-the-server
DB_PORT=3306
DB_DATABASE=production-database-name
DB_USERNAME=dedicated-production-user
DB_PASSWORD=strong-private-production-password
```

Do not copy the local `root`/blank-password values to the server. Confirm that the production database is empty or backed up before the first migration, then run `php artisan migrate --force` and `php artisan db:seed --force`. Later deployments should run migrations but should not need to reseed the original questionnaire.

### First deployment

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan optimize
```

After the first deployment, create the production administrator securely using Tinker or another approved server-side account-provisioning process. Do not seed demo responses in production.

### Later releases

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

### Scheduler

The server must execute Laravel’s scheduler every minute. Laravel then runs `survey:evaluate-alerts` daily and prevents overlapping evaluations:

```cron
* * * * * cd /path/to/bec-survey && php artisan schedule:run >> /dev/null 2>&1
```

Use the hosting provider’s scheduler interface if traditional cron is unavailable.

### Web server and permissions

- Point the domain’s document root to `/path/to/bec-survey/public`, never the repository root.
- Redirect HTTP to HTTPS.
- Allow the web-server user to write to `storage/` and `bootstrap/cache/`.
- Do not expose `.env`, `vendor`, database backups, logs, or source directories through the web server.
- Keep PHP, Composer dependencies, and Node-built assets updated through reviewed deployments.

### Production readiness checklist

- Confirm `APP_ENV=production` and `APP_DEBUG=false`.
- Confirm HTTPS, secure cookies, and the correct `APP_URL`.
- Confirm the active survey contains four sections and 14 questions before sharing its link.
- Confirm administrator login and password-reset email delivery.
- Submit a complete test response and verify it appears in Dashboard and Common Answers.
- Verify custom date filters and PDF download.
- Run `php artisan schedule:list` and confirm the server scheduler is active.
- Configure automated MySQL backups and test a restore.
- Configure centralized error monitoring and appropriate log retention without logging survey-answer contents.
- Monitor `/up`, database storage, cache health, job/scheduler failures, and server capacity.
- Change any password that has appeared in screenshots, chat messages, shell history, or documentation.

## Operational notes and known boundaries

- The application meets the original functional requirement of a student survey and an administrator results page.
- The original questionnaire contains 14 numbered questions. Preferred time and time zone are stored as two required groups inside the grouped-choice schedule question.
- Dashboard totals count completed, non-demo submissions. They do not prove the number of unique students.
- Demo responses are excluded from normal analytics and must never be presented as real student feedback.
- The dashboard and Common Answers show empty states until valid responses exist.
- Question changes are made in a draft. Administrators must preview and publish the draft before students see those changes.
- Publishing preserves prior survey versions and their historical answers.
- The question editor directly creates single-choice, multiple-choice, and long-text questions. The seeded grouped-choice scheduling question can be edited as an advanced existing question.
- Alerts require the scheduler; without `schedule:run`, automatic daily evaluation will not occur.
- PDF exports are private, rate-limited, and based on the administrator’s current filter snapshot.
- Production readiness depends on server configuration, HTTPS, backups, mail, monitoring, and a final smoke test; those responsibilities are not completed by copying the repository alone.

## Troubleshooting

### The student page shows no questions

1. Confirm MySQL is running and `.env` points to the intended database.
2. Run `php artisan migrate:status`.
3. Run `php artisan db:seed` to restore missing initial survey records.
4. Run `php artisan cache:clear` to remove stale public-survey data.
5. Reload `/` and confirm it shows “Question 1 of 14.”

### Administrator credentials do not match

Confirm the user exists, `is_admin` is true, and `email_verified_at` is not null. If no administrator exists, create one using the Tinker procedure above. Do not enable public registration to solve this problem.

### Frontend changes are not visible

During development, run `npm run dev`. For a production-style check, run `npm run build` and reload the page. If Laravel reports a missing Vite manifest, confirm `public/build/manifest.json` was generated and deployed.

### Dashboard numbers are empty

Submit a valid, non-demo survey response and ensure the selected dashboard reporting period includes its submission date. Empty analytics are expected when the database contains no completed responses.

## Handoff summary

A receiving developer should be able to prepare the project by installing Composer and npm dependencies, creating `.env`, configuring MySQL, running migrations and seeders, creating an administrator, building assets, and starting Laravel. They should then verify the public survey, submit one response, sign in, confirm Dashboard and Common Answers, test question preview/publishing in a safe environment, generate a PDF, and verify the scheduler before approving production deployment.
