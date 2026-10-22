# Aqar Exit — Progress

Environment: PHP 8.4.5 · Laravel 13.38 · Sanctum 4.3 · Tailwind CSS v4 (Blade) · Node 22 · PHPUnit 12 · SQLite local / MySQL production (`.env.example`).

## Architectural decisions (documented, reversible)
- **Blade + Tailwind kept** (repo stack) instead of adding Livewire — same server-rendered result, no rewrite of working pages.
- `listings` is the property table (public API name stays `/listings`); `deals` is the assignment-case table. Both predate this spec pass and renaming would break the mobile contract.
- Money: migrated to `decimal(15,2)`, all math via `bcmath` strings — never float. Reported vs verified financials stored separately; mismatch ⇒ `financial_status=discrepancy`, reported values never overwritten.
- Admin approval ≠ legal verification: API exposes `financial_status` only; legal transfer completes offline at the developer and is merely tracked by case stages.
- Case stages enforced by `DealTransitionService::TRANSITIONS`; every move writes `deal_events` (actor/from/to/reason) + audit log + notification (queued, no sensitive data in payloads).

## Done
- [x] Phase 1–2: domain, migrations, factories/seeders, Sanctum `/api/v1`, exit calculator
- [x] Phase 3: listings/financials/images/private documents, search filters + indexes, categories & locations (configurable), saved searches
- [x] Phase 4: interests → deals workflow with valid-transition enforcement, history, database notifications, saved-search match notifications
- [x] Phase 5: admin dashboard (Tailwind, real Heroicon SVGs, AR/EN + RTL, locale switch), verification + financial-verification panels, kanban, users, reports + CSV export, audit logs
- [x] Admin → Settings: **project name (EN/AR) + logo upload** (shown in sidebar/login, served to mobile via `GET /api/v1/branding`), support contacts, commission %, calculator default, default locale, feature flags
- [x] Security: policies, staff-only moderation, owner-only private downloads, rate-limited auth, suspended-account token revocation, audit logging
- [x] Docs: `STRUCTURE.md`, `docs/openapi.yaml`

## Hardening pass (done)
- [x] **12-step case workflow** (`CaseWorkflow` + `ListingTransitionService`): Draft → Submitted → Under Review → Additional Info Requested → Documents Resubmitted → Approved for Listing → Buyer Interest Received → Negotiation → Assignment Process → Completed (+ Rejected/Cancelled). Invalid jumps rejected; API exposes `case_step`/`case_workflow` on listings.
- [x] **Permissions matrix**: `permissions` + `permission_role` tables, 20-permission catalog (`config/permissions.php`), Gate per permission, admin matrix editor at `/admin/permissions` (admin cannot lose `permissions.manage`/`settings.manage`/`audit.view`); enforcement across moderation, financial verification, exports, settings, users, deals, audit.
- [x] **Signed document links**: 5-minute signed download URLs (`documents/{id}/signed-download`, `POST /api/v1/my-listings/{listing}/documents/{document}/signed-url`), owner/staff generation, tamper/unsigned rejected.
- [x] **Full Arabic**: all admin strings use the `admin.*` catalogue (EN/AR) incl. login, forms, documents, evaluations, deals, audit; RTL verified by tests.

## Continuation phases (done)
- [x] Phone OTP: hashed, expiring, single-use codes, attempts/cooldown/rate-limit, pluggable provider (`log` dev / generic `http` webhook), OTP login + phone verification endpoints
- [x] Email verification signed links (`/auth/verify-email/{id}/{hash}` + resend)
- [x] FCM push channel: notifications also push when user has a device token + push enabled; safe no-op without `FCM_SERVER_KEY`; payloads carry ids only
- [x] Scheduled matching: `aqar:notify-matching` hourly + on-publish, nightly `aqar:backup`, daily OTP pruning (`routes/console.php`)
- [x] Map discovery: `GET /listings/map` pins with viewport bounds
- [x] Documents: replace/resubmit endpoint resets review, `document_reviews` keeps full history
- [x] i18n: shared admin labels translated (dashboard/queue/deals/users), RTL verified by test
- [x] Phase 6: security headers middleware, `docs/DEPLOYMENT.md`, `docs/ADMIN_GUIDE.md`, backup command

## Public website + admin completion (done)
- [x] Public site at `/` (branding from Settings): hero, free calculator, categories, featured/latest, developers; `/listings` search; `/listings/{id}` detail with financials, map (Leaflet/OpenStreetMap — no API key), similar units; `/evaluate` calculator + free exit-report lead form (feeds Admin → Evaluations)
- [x] Admin listing create/edit on behalf of a seller (+ image uploads), listings CSV export (admin only), listings map page
- [x] Inquiry messaging: `interest_messages` thread (buyer/seller/staff, private per-thread authorization), API + staff reply from the listing review page
- [x] CI: `.github/workflows/tests.yml` (PHP 8.4 + Node 22, sqlite, Pint check)

## Pending / known limitations
- OTP/FCM go live only with real provider credentials in `.env` (by design — no secrets in code).
- Map *UI* (Flutter/admin widget) waits for a maps provider choice; the API is ready.
- Queue worker required in production for notifications (`QUEUE_CONNECTION=database`, run `php artisan queue:work`).

## Deployment checklist
1. `cp .env.example .env`, set MySQL creds, `APP_DEBUG=false`, HTTPS URL. 2. `composer install --no-dev && npm ci && npm run build` 3. `php artisan key:generate && php artisan migrate --seed && php artisan storage:link` 4. Run queue worker + scheduler via supervisor/cron. 5. Backups: nightly `mysqldump` + `storage/app` (private documents) sync; test restore quarterly.
