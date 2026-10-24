# Railway PHP2Shell audit — 2026-10-24 (Asia/Dhaka)

## Status and scope

Inspection and source changes are complete for the nine projects in the
**RK Projects** workspace. All six PHP services remain on their original
production deployments. Changes have not been pushed or deployed. Image builds
and post-deployment verification are pending. No production migrations ran.
HestiaCP and the separate Cosy Capital workspace are outside this audit.

Only production environments are listed; no staging environment is available.
Each PHP service has one active deployment/replica. Railway lists no separate
PHP worker, queue, scheduler, or cron service, and all service cron schedules
are empty. Process-name inspection found web processes, with no active queue
worker, scheduler, cron daemon, or supervisor. External schedulers cannot be
excluded by this inspection.

## Confirmed runtime inventory

Versions below came from private `railway ssh` execution, not repository pins.
FrankenPHP versions were corroborated by `frankenphp version`; Design's web
runtime was corroborated by `php-fpm -v` and process inspection. BABYZONE's
PID 1 is PHP CLI with the built-in `-S` HTTP server, despite its Apache base.
These source-built upstream runtimes have no verified vendor backport.

| Project / service | Running PHP | Web runtime / source | Prepared target | Live status |
| --- | --- | --- | --- | --- |
| Techsense / Techsense | 8.2.30 | FrankenPHP 1.12.2, Dockerfile | PHP 8.2.35 / FrankenPHP 1.13.2 | Confirmed exposed |
| BABYZONE / babyzone | 7.4.33 | PHP built-in HTTP server; official Apache Docker base | 7.4.33 rebuilt with official PHP security-team backport | Confirmed exposed |
| Design / design.newlifestylebd.com | 8.4.26 | nginx + PHP-FPM, official PHP Docker image | PHP 8.4.27 FPM | Confirmed exposed |
| Rayhan Trading / rayhantrading | 8.3.33 | FrankenPHP 1.12.7, Dockerfile | PHP 8.3.36 / FrankenPHP 1.13.2 | Confirmed exposed |
| HUTBAZAR / hutbazar-api | 8.2.33 | FrankenPHP 1.12.6, Railpack | PHP 8.2.35, selected through composer.json | Confirmed exposed |
| Kashful Super Shop / kashful-super-shop | 8.2.33 | FrankenPHP 1.12.6, Dockerfile | PHP 8.2.35 / FrankenPHP 1.13.2 | Confirmed exposed |

HUTBAZAR's deployment metadata reports a resolved package version of 8.2.31;
the actual PHP CLI and FrankenPHP binary both report 8.2.33. The running
binary evidence takes precedence.

The remaining services need no PHP update: DoozEX/ePharma (Node),
Invoice/sellyx-server (Node), creative-emotion/RemoteDeviceGO (Go),
creative-emotion/RemoteDeviceNext (Node/Next), six MySQL services, and
creative-emotion/Postgres. No PHP executable was found on PATH in inspected
non-PHP containers. Design/MySQL and creative-emotion/Postgres were sleeping
and could not accept SSH; their configured database-only images identify
them as non-PHP services. RemoteDeviceNext was checked after waking it with
a normal HTTP request.

## Production deployment evidence

| PHP service | Active deployment before changes |
| --- | --- |
| Techsense | 2d11b235-7f25-4623-a6f1-a6fb4982c92b |
| babyzone | e4ba786c-018e-41b5-9ee9-81d24f44d501 |
| design.newlifestylebd.com | 6e71f3a7-c8e6-46d7-8ed5-257640398428 |
| rayhantrading | a0e762df-0040-49c0-902e-117083e74396 |
| hutbazar-api | d85b9788-3963-4145-9cd1-46ecee9341b3 |
| kashful-super-shop | f706312e-06ae-4a9b-b40c-3f76fcd17cb7 |

Each active deployment matches the corresponding local HEAD before this
security change. Kashful has pre-existing local application edits and an
untracked Accounting archive; neither is part of the security commit.

## Changes

- Techsense: Dockerfile pins FrankenPHP/PHP by release and digest; php.ini
  disables session upload progress; railway.toml explicitly clears migration
  hooks; RAILWAY_DEPLOY.md documents the migration-free security rollout.
- Rayhan Trading: Dockerfile, php.ini, railway.toml make the same changes,
  retaining PHP 8.3 and all installed extensions.
- Kashful: Dockerfile, docker/php-runtime.ini, railway.toml make the same
  changes, retaining PHP 8.2 and all installed extensions. RAILWAY_DEPLOY.md
  removes a committed database password and updates migration guidance.
- Design: Dockerfile pins PHP 8.4.27 FPM by digest and copies the added
  docker/php-security.ini. nginx, FPM ports, and extensions are preserved.
- HUTBAZAR: composer.json selects exactly 8.2.35 using Railpack's documented
  mechanism. composer.lock changes only the PHP platform requirement and
  Composer-generated content hash; all package records remain identical.
  start-container.sh retains Laravel initialization, disables session upload
  progress, and omits automatic migrations.
- BABYZONE: Dockerfile pins the original base by digest, installs build
  prerequisites, and rebuilds PHP core from its bundled source with the
  checksum-verified official patch. docker/rebuild-php-security.sh retains
  original configure options and compiler hardening flags, builds both CLI
  and Apache, and requires the backport's exported symbol in both binaries.
  docker/php74-php2shell.patch, docker/php-security.ini, .gitattributes, and
  RAILWAY.md document/preserve the patch and Linux script bytes. The existing
  entrypoint requires a new explicit migration opt-in as the existing Railway
  configuration enables its older migration switch.

No framework or package versions were upgraded. PHP 7.4 remains end-of-life;
the BABYZONE backport addresses these two CVEs and is not general PHP LTS.

## Validation completed

- Official PHP, Docker, FrankenPHP, and Railpack documentation checked.
- Docker Hub tags/digests exist for the selected bases, including Railpack's
  php8.2.35-trixie tag. This establishes availability, not a successful build.
- Official PHP 7.4 patch applies cleanly to upstream PHP 7.4.33 source with
  `git apply --check`; bundled patch SHA-256 is
  `5fe57ca6ee96d59b36c6e0ca842843c50884f177bef1e85234cf669b577f5e48`.
- Composer's locked dependency checks permit each chosen PHP 8 patch version.
- HUTBAZAR Composer validation passes; package and development-package
  records are unchanged in every modified PHP 8 repository.
- Shell syntax checks pass for the added/changed scripts.
- Existing local unit tests pass: Techsense 8 tests / 33 assertions;
  Rayhan 1 / 1; Design 1 / 1; HUTBAZAR 1 / 1. These ran on local PHP 8.3.28,
  not the prepared images. Kashful has no installed test dependencies;
  BABYZONE's PHP 7 dependencies cannot be validated on local PHP 8.3.
- Private, read-only database `SELECT 1` passes on all six live services.
- Techsense, BABYZONE, Kashful: configured health endpoint and login return 200.
  Rayhan: public health and login return 200. Design: Railway login returns
  200; custom-domain login returns 502 (existing route targets FPM port 9001
  while nginx listens on 9000). HUTBAZAR: public root returns 500 and sampled
  API paths return 400. These baseline failures predate any rollout.
- Last 200 deployment log lines per PHP service were reviewed with a limited
  indicator filter; no selected corruption/exploit/miner indicators matched.
  Application errors exist. This is not incident clearance or proof of no
  compromise. No exploits or memory-exhaustion tests were run.

## Required validation and deployment plan

1. Obtain a Linux Docker build runner or approve isolated Railway validation
   services. This Windows host has no Docker engine or installed WSL. Do not
   attach preview services to production databases or volumes. If no isolated
   test database/accounts are available, request those before business-flow
   tests. Keep every migration hook disabled.
2. Build each Docker repository from the security commit (`docker build
   --pull -t <service>:php2shell .`). For HUTBAZAR use Railway/Railpack with
   its committed composer.json and custom startup script. Confirm the build
   plan selects PHP 8.2.35 and the available FrankenPHP image. Prevent cached
   application config or local secrets from entering build context.
3. Verify image `php -v`, `php -m`, effective session upload-progress setting,
   and `composer check-platform-reqs --no-dev` where Composer is available.
   Compare extensions with the before inventory: preserve bcmath, exif,
   fileinfo, gd, intl, mbstring, pcntl, pdo_mysql, zip where previously present;
   preserve HUTBAZAR's redis extension too. Check `frankenphp version` or
   `php-fpm -v`. BABYZONE must pass symbol checks in the rebuild script and
   retain its patch build record; version 7.4.33 alone is insufficient proof.
4. In isolation, validate health, database connectivity, authenticated login,
   uploads and persistence, POS sales/purchases/invoices, PDF exports, and
   HUTBAZAR product/API flows. Exercise queues and scheduled jobs if configured
   in that environment; do not enqueue real production jobs or run schedules
   merely as a smoke test.
5. Resolve or explicitly accept the existing Design/HUTBAZAR baseline issues.
   Confirm upload persistence/volume coverage before replacing containers.
   No authenticated business-flow validation has been performed yet.
6. Obtain production rollout approval for the concrete commits and successful
   image validation. Deployment guides describe procedures, but no standing
   authorization for this production rollout was found. All six production
   services track main, so pushing there can trigger deployment; do not push
   these commits to main before approval.
7. Deploy one service at a time, starting with Techsense, then Kashful, Rayhan,
   Design, HUTBAZAR, and finally BABYZONE's more involved rebuild. Deploy from
   the security commits, excluding Kashful's unrelated working-tree changes.
   Explicitly clear any dashboard pre-deploy migration command as well.
   Verify BABYZONE's new migration opt-in is absent/disabled.
8. Verify a new healthy active deployment for each service and each replica;
   old processes must terminate. Use private SSH to repeat version, extension,
   effective ini, binary/process, DB, and health checks. Inspect private logs.
   Check embedded FrankenPHP/FPM as well as CLI. Re-inventory any newly added
   worker/cron/scheduler services and redeploy those from the same fixed source.
   Do not expose phpinfo or a public diagnostic route.

## Recovery and remaining actions

Keep the last healthy application source but rebuild it on these patched
runtime bases if application rollback is necessary. Do not restore the old
Railway image/deployment, old PHP processes, or old unpatched Docker bases.
Keep migrations disabled; no schema rollback is required for this update.
Use existing approved data/volume backups only if independently necessary.

Rotate the database credential found in Kashful's tracked deployment guide,
update dependent Railway secrets together, and arrange Git-history cleanup
with repository collaborators. The current document is scrubbed; old commits
and the earlier tool output must still be treated as containing that secret.
A GitHub-generated short-lived raw download URL also appeared in tool output;
it is not reproduced in these artifacts.

If later evidence suggests compromise, preserve private deployment/access
logs and container artifacts before replacing them, investigate persistence,
and rotate affected credentials. A patched runtime does not establish that a
previously compromised application or its data is clean.

## Primary sources

- PHP advisory and October 23 follow-up: https://www.php.net/archive/2026.php
- PHP changelog: https://www.php.net/ChangeLog-8.php
- PHP support: https://www.php.net/supported-versions.php
- Official PHP 7.4 backport: https://github.com/php/php-src/commit/712e60f9edfd03f0960fc4fc3c2c375f42b90aa2
- FrankenPHP security release: https://github.com/php/frankenphp/releases/tag/v1.13.2
- Docker advisory: https://www.docker.com/blog/security-update-on-php2shell-impact-for-docker-customers/
- Railpack PHP selection/startup: https://railpack.com/languages/php/
- Railpack deployed-version provider: https://github.com/railwayapp/railpack/blob/v0.39.0/core/providers/php/php.go
- Vendor EOL alternative reviewed: https://www.freexian.com/lts/extended/updates/ela-1853-1-php7.4/
