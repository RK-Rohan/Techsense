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
2. Builder: Dockerfile. The repository Dockerfile builds PHP dependencies and frontend assets.
3. Start Command: leave blank so Railway uses the Dockerfile `CMD`.
4. Pre-Deploy Command:
   - `php artisan migrate --force`
5. Healthcheck path:
   - `/health`

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
- `DB_HOST=${{MySQL.MYSQLHOST}}`
- `DB_PORT=${{MySQL.MYSQLPORT}}`
- `DB_DATABASE=${{MySQL.MYSQLDATABASE}}`
- `DB_USERNAME=${{MySQL.MYSQLUSER}}`
- `DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}`
- `DB_SOCKET=`
- `MYSQL_ATTR_SSL_CA=`

`MySQL` in these references must exactly match the database service name in the
Railway project. If it has another name, replace `MySQL` in every reference.
The application also accepts Railway's native `MYSQLHOST`, `MYSQLPORT`,
`MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`, and `MYSQL_URL` names when they
are exposed to the app service.

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
- If the Docker build fails:
  - Ensure the latest commit is deployed and Railway is using the repository Dockerfile.
  - Do not override the Dockerfile build or start commands with old Railpack commands.
- If migration fails:
  - Inspect the resolved variables on the app service (not only on the MySQL service).
  - An error containing `mysql:host=;` means the database references resolved to empty values.
  - Confirm the service name in `${{MySQL.MYSQLHOST}}` exactly matches the MySQL service.
  - Run `php artisan migrate:status` in Railway shell.
- If static assets missing:
  - Check the Docker build's `assets` stage; it runs `npm run production`.
