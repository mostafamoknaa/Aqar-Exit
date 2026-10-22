# Deployment — Aqar Exit

## Local development
```bash
composer install && npm install
cp .env.example .env   # local: set DB_CONNECTION=sqlite
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run build          # or npm run dev
php artisan serve      # http://localhost:8000/admin/login
php artisan queue:work # notifications (separate terminal)
```

## Staging / Production (Linux, MySQL/MariaDB, PHP 8.3+)
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env   # DB_CONNECTION=mysql, real creds, APP_ENV=production, APP_DEBUG=false, APP_URL=https://...
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
- Web server docroot = `public/`, HTTPS enforced (HSTS header activates in production).
- Queue: supervisor/systemd unit running `php artisan queue:work --tries=3 --timeout=90`.
- Scheduler cron: `* * * * * php artisan schedule:run` (hourly saved-search matching, 02:30 backup, OTP prune).
- Integrations only via env: `OTP_PROVIDER=http + OTP_HTTP_ENDPOINT + OTP_API_KEY`, `FCM_SERVER_KEY` (no keys in code/DB).

## Backups & restore
- `php artisan aqar:backup` (also nightly via scheduler) → `storage/app/backups/`. Sync that folder + private documents (`storage/app/private` / document disk) off-site.
- MySQL restore: `mysql -u <user> -p aqar_exit < database-YYYYMMDD.sql`; SQLite restore: replace `database/database.sqlite` with the backup file, then `php artisan migrate --force`.
- Test restoration quarterly on staging; a backup that was never restored is not a backup.

## Rollback
`php artisan migrate:rollback --step=1` for last batch (test on staging first), redeploy previous release build, clear caches (`config:clear route:clear view:clear`).
