# Railway Deploy Guide (Laravel)

This project can run on Railway with:
- App service on Railway
- MySQL service on Railway

## 1) Required Repo Files

Already added:
- `railway.toml`
- `Procfile`

## 2) Manual Setup in Railway Dashboard

Even with `railway.toml`, verify these in service settings:

1. Service source: connect this GitHub repo and branch.
2. Builder: Railpack (default if no Dockerfile).
3. Build Command:
   - `npm run production`
4. Start Command:
   - `php -d variables_order=EGPCS artisan serve --host=0.0.0.0 --port=${PORT}`
5. Pre-Deploy Command:
   - `php artisan migrate --force`
6. Healthcheck path:
   - `/`

Important:
- Railway build image must support your PHP version. This repo now targets PHP `^8.2`.

## 3) Environment Variables (App Service)

Set these in Railway Variables:

- `APP_NAME=Ultimate POS`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<your-railway-domain>`
- `APP_KEY=<copy from your current working env>`
- `LOG_CHANNEL=stack`
- `CACHE_DRIVER=file`
- `SESSION_DRIVER=file`
- `QUEUE_CONNECTION=sync`

Database:
- `DB_CONNECTION=mysql`
- `DB_HOST=<Railway MySQL private host>`
- `DB_PORT=3306`
- `DB_DATABASE=railway`
- `DB_USERNAME=root`
- `DB_PASSWORD=<Railway MySQL password>`
- `DB_SOCKET=`
- `MYSQL_ATTR_SSL_CA=`

Railpack/Build controls (set manually in Railway service variables):
- `RAILPACK_PHP_EXTENSIONS=bcmath,gd,intl,zip,exif,pcntl`
- `RAILPACK_INSTALL_COMMAND=composer install --no-dev --optimize-autoloader --no-scripts --no-interaction`

## 4) First Deployment Checklist

1. Deploy app service.
2. Confirm build succeeds.
3. Confirm pre-deploy migration succeeds.
4. Open app URL and verify login page.
5. Verify DB-backed page loads data.

## 5) Optional Production Services

If you need async jobs/schedules:

- Worker service start command:
  - `php artisan queue:work --tries=3 --timeout=120`
- Scheduler service start command:
  - `php artisan schedule:work`

## 6) Troubleshooting

- If app fails to boot:
  - Check `APP_KEY`, DB vars, and start command.
- If build fails with `No version available for php 8.0` or `php 8.1`:
  - Ensure latest commit is deployed (this repo now uses `composer.json` -> `"php": "^8.2"`).
- If build fails at `install:composer` with exit code `2`:
  - Add `RAILPACK_PHP_EXTENSIONS=bcmath,gd,intl,zip,exif,pcntl` in Railway service variables.
  - Add `RAILPACK_INSTALL_COMMAND=composer install --no-dev --optimize-autoloader --no-scripts --no-interaction`.
  - Redeploy latest commit.
- If migration fails:
  - Run `php artisan migrate:status` in Railway shell.
- If static assets missing:
  - Ensure build command includes `npm run production`.
