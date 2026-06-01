# AGENTS.md

Guidance for agents working in this repository. Scope: the whole project.

## Project Overview

This is a Laravel-based Daily Report System, branded in the UI as DailyPulse/DRS. It is an internal operations app for authenticated users to manage tasks, daily reports, attendance, schedules, on-call rotations, announcements, services, notifications, and internship evaluations.

The checked-in `README.md` is the stock Laravel README, so prefer this file plus the source code for project-specific context.

## Technical Specifications

- Backend: PHP `^8.2`, Laravel `^12.0`, Eloquent ORM, Blade views, Laravel scheduler, queue tables.
- Frontend/build tooling: Vite `^7`, Tailwind CSS `^4`, Laravel Vite plugin. The active shared layout currently links `public/css/app.css` directly with `asset('css/app.css')`; `resources/css/app.css` is the Vite/Tailwind input but is not loaded by the main layout unless the layout is changed to use `@vite`.
- JavaScript: vanilla JS in Blade/layout for menus, notifications, CKEditor setup, and calendar/editor widgets. `resources/js/app.js` and `resources/js/bootstrap.js` exist for Vite entry points.
- Testing: PHPUnit `^11.5`; `phpunit.xml` sets `APP_ENV=testing`, SQLite in-memory database, array cache/session/mail, and sync queues.
- Formatting: Laravel Pint is available through `vendor/bin/pint`.
- Localization: English and Korean message files live in `lang/en/messages.php` and `lang/ko/messages.php`.
- Storage/uploads: public disk is used for avatars, task/comment attachments, and CKEditor image uploads. Use `php artisan storage:link` in local setups when public uploads need to render.

## Architecture Map

- `routes/web.php` contains all user-facing routes. There are no API route files in use.
- `bootstrap/app.php` registers web middleware and aliases:
  - `admin` -> `App\Http\Middleware\AdminMiddleware`
  - `super_admin` -> `App\Http\Middleware\SuperAdminMiddleware`
  - `SetLocale` is appended to the web middleware stack.
- Controllers live in `app/Http/Controllers`. They currently own request validation, lightweight authorization checks, data loading, and response selection. No policy layer is currently established.
- Models live in `app/Models` and use Eloquent relationships/casts. Keep relationship names stable because views and controllers depend on them.
- Database structure is defined by `database/migrations`; seed data lives in `database/seeders`.
- Shared UI shell is `resources/views/layouts/app.blade.php`; domain views are grouped under `resources/views/{feature}`.
- App-specific helper logic currently lives in `app/Support`, for example `TextFormatter`.
- Scheduled commands are declared in `routes/console.php`; `oncall:auto-rotate` is implemented in `app/Console/Commands/AutoCreateOnCallRotation.php` and scheduled daily.

## Domain Model Notes

- Users have `role` values `user`, `admin`, and `super_admin`. `User::isAdmin()` returns true for both `admin` and `super_admin`.
- Users also have optional internship/team role fields:
  - `intern_role`: `senior_programmer`, `mid_programmer`, `translator`
  - `team_role`: `director`, `team_manager`, `team_member`
- Tasks are created/requested by users, can have many assignees through `task_user`, have comments, and support file/image/link attachments.
- Daily reports belong to users and can optionally reference a task.
- Attendance records track one user/date with check-in, check-out, and decimal total hours.
- On-call scheduling uses both dated `on_calls` and reusable `on_call_rotations`; rotations have ordered users through `on_call_rotation_users`.
- Evaluations support `self`, `peer`, and `manager` types. Rating item definitions and weighted score calculations live in `App\Models\Evaluation`.
- Services store operational service metadata and include sensitive credential fields. Treat service credentials as secrets.
- Notifications are app records, not Laravel's built-in notifications table.

## Safe Development Tools

Prefer these tools and habits:

- Search with `rg` / `rg --files`.
- Inspect routes with `php artisan route:list`.
- Inspect schema through migrations first, then models/controllers.
- Use `php artisan migrate:status` before changing migration state.
- Use focused tests with `php artisan test --filter Name` while iterating.
- Use `./vendor/bin/pint --test` to check style and `./vendor/bin/pint` to format PHP files.
- Keep `.env`, credentials, uploaded storage, `vendor/`, `node_modules/`, `public/build`, and `public/storage` out of commits.
- Treat `app.zip`, `drs.sql`, `php.ini`, and the `Internship` file as reference/deployment artifacts unless the user explicitly asks to modify them.
- Do not edit production-like database dumps for schema changes; add migrations instead.
- Never log or expose passwords, service admin/test credentials, `.env` values, session data, or uploaded private files.

## Setup And Local Commands

Initial setup:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Run the app:

```bash
php artisan serve
```

For larger uploads during local testing:

```bash
./serve.sh
```

Run the combined Laravel dev stack if Node/npm dependencies are installed:

```bash
composer run dev
```

This starts the Laravel server, queue listener, log tailing with Pail, and Vite via `concurrently`.

Frontend-only commands:

```bash
npm run dev
npm run build
```

Database commands:

```bash
php artisan migrate
php artisan migrate:status
php artisan db:seed
```

Use this only on a disposable local database:

```bash
php artisan migrate:fresh --seed
```

## Tests And Verification

Full PHP test suite:

```bash
composer test
```

Equivalent direct command:

```bash
php artisan test
```

Focused examples:

```bash
php artisan test --filter TextFormatterTest
php artisan test tests/Feature/ExampleTest.php
```

Style checks:

```bash
./vendor/bin/pint --test
```

Before handing off backend changes, run the narrowest relevant tests plus `composer test` when the change touches routing, models, middleware, migrations, auth, or shared helpers.

Before handing off frontend or Blade changes, verify the affected route manually in a browser when possible and run `npm run build` if Vite-managed assets were changed.

## Preferred Implementation Patterns

- Follow existing Laravel MVC boundaries. Add controller methods, model relationships/scopes, Blade partials, migrations, and tests in the established locations.
- Keep validation close to the controller action unless the codebase establishes FormRequest classes for that feature.
- Keep authorization consistent with current middleware/controller checks. If introducing policies, do it deliberately and wire the affected feature fully.
- Use Eloquent relationships and query builders instead of manual SQL unless there is a clear reason.
- Add casts for dates, arrays, booleans, and numeric values on models when new columns need typed access.
- Use route names in redirects and links, as existing Blade views do.
- Prefer small Blade partials for repeated form controls or UI fragments. Existing examples include `resources/views/evaluations/partials`.
- When adding user-facing strings, update both `lang/en/messages.php` and `lang/ko/messages.php` or keep the text intentionally non-localized only when the surrounding view already does so.
- For rich text, preserve the existing CKEditor pattern: views opt in with `@section('ckeditor')`, fields use `.ckeditor-field`, and uploads go through the named `editor.upload` route.
- When rendering user-provided plain text with links, prefer `App\Support\TextFormatter::linkifyUrls()` instead of ad hoc linkification.
- For attachments and uploads, validate file type/size, store through Laravel disks, and delete old files when replacing or destroying records.
- Keep status/role enum strings aligned with migrations, views, CSS badge classes, and model helpers.
- Add migrations for schema changes. Do not rewrite old migrations after they may have been shared unless this is clearly a fresh local-only change.
- Feature tests that touch the database should use Laravel testing helpers such as `RefreshDatabase`; remember `phpunit.xml` already points tests at SQLite `:memory:`.

## UI And Asset Patterns

- The main UI is dense, work-focused, and built from shared classes in `public/css/app.css`: `.container`, `.card`, `.page-header`, `.btn`, `.form-control`, `.table-wrapper`, `.badge-*`, and feature-specific sections.
- Keep new Blade screens consistent with the existing layout: extend `layouts.app`, set `@section('title')`, render flash messages through the layout, and use the existing navbar/route names.
- If changing global styling, edit `public/css/app.css` for immediately visible app styles. Edit `resources/css/app.css` only for Vite/Tailwind changes and make sure the layout/build path actually uses it.
- Avoid introducing a new frontend framework unless the user asks for it. The current app is Blade plus vanilla JavaScript.
- The layout includes notification polling and optional CKEditor CDN setup. Be careful when modifying `resources/views/layouts/app.blade.php`; many pages inherit that behavior.

## Security And Data Safety

- `.env` is local and ignored. Do not print or copy secrets from it.
- Service credential fields, account passwords, and uploaded files should be treated as sensitive even when stored in development data.
- Preserve CSRF protection on forms and AJAX requests. Existing upload JS sends `X-CSRF-TOKEN`.
- Use Laravel validation for all request input and route model binding for model lookup.
- Do not bypass `auth`, `admin`, or `super_admin` middleware when adding routes.
- Be cautious with `migrate:fresh`, manual SQL, queue workers, and scheduled commands; confirm the target environment/database first.
