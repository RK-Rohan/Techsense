# Railway PHP2Shell rollout — 2026-10-24

This report supersedes the original inspection-only report. The user authorized
production rollout of the five remaining PHP security changes with “update”.
HestiaCP and the separate Cosy Capital workspace remain outside scope.

## Verified runtime inventory

Versions were checked privately in deployed containers, including the actual
FrankenPHP binary or PHP-FPM master and its forked workers. Repository pins alone
were not used as production evidence. These are upstream fixed releases; no
vendor backport is needed for these five services.

| Project / service | Before PHP | Verified PHP | Laravel (unchanged) | Web runtime | Patch status |
| --- | --- | --- | --- | --- | --- |
| Techsense / Techsense | 8.2.30 | 8.2.35 | 9.52.16 | FrankenPHP | Confirmed patched |
| Rayhan / rayhantrading | 8.3.33 | 8.3.36 | 10.50.2 | FrankenPHP | Confirmed patched |
| Kashful / kashful-super-shop | 8.2.33 | 8.2.35 | 9.52.4 | FrankenPHP | Confirmed patched |
| Design / design.newlifestylebd.com | 8.4.26 | 8.4.27 | 12.64.0 | nginx + PHP-FPM | Confirmed patched |
| HUTBAZAR / hutbazar-api | 8.2.33 | 8.2.35 | 11.41.3 | FrankenPHP | Confirmed patched |

BABYZONE was completed separately before this rollout: PHP 7.4.33 / Laravel 5.8
was replaced by PHP 8.3.36 / Laravel 13.38.0. The original proposed PHP 7.4
backport was superseded by that upgrade and is not its active runtime.

Only production environments exist in the five projects. Each has one PHP
application service and one MySQL service. Railway lists no separate PHP queue,
worker, scheduler, or cron service, and no cron schedules. Process inspection
found the web processes and the temporary verification CLI, with no active queue
worker, scheduler, cron daemon, or supervisor. External schedulers are unverified.

The initial workspace inventory also identified DoozEX/ePharma (Node),
Invoice/sellyx-server (Node), creative-emotion/RemoteDeviceGO (Go),
creative-emotion/RemoteDeviceNext (Node/Next), MySQL and Postgres as non-PHP.
Those services required no PHP runtime changes.

## Deployment evidence

| Service | Deployment at verification | Main commit | Railway status |
| --- | --- | --- | --- |
| Techsense | `765ad98d-c2d4-496e-b7b2-19e144805bfd` | `6af36542b9092fab65e3544dfbe66d051a4fc5e5` | SUCCESS |
| Rayhan | `cea95a36-9ba4-4a8d-8bea-0f5e7ff31049` | `577b1367fd95f685e75f77b2cb88f64702b2e121` | SUCCESS |
| Kashful | `dd651b1e-c43f-47b6-a2b9-1158a40e7d92` | `a4010f28c0a7bac230779bbb4640e960be89bb89` | SUCCESS |
| Design | `9c075faf-0743-4dbc-b491-fa5efbd9ca24` | `11abea05debe6d6a1c8f9b05cd102eb7a77181ff` | SUCCESS |
| HUTBAZAR | `2d529f9d-847f-409d-acf8-26dcaaf09d9f` | `43a962ef340156b848d80f618ce7d0603b74d2d1` | SUCCESS |

Private evidence, original settings, logs, upload archives and SHA-256 manifests
are retained outside Git in the restricted remaining-php2shell backup directory.
No environment-variable values or credentials are included in this report.

## Implementation and validation

- Docker builds pin the selected PHP/FrankenPHP release and image digest.
  Design retains nginx and PHP-FPM. Existing extensions and application
  configuration are preserved.
- HUTBAZAR uses Railpack's documented Composer version requirement for exactly
  PHP 8.2.35, with explicit pdo_mysql and redis extension requirements.
  Composer package versions remain unchanged; only platform requirements and
  their content hash changed.
- Session upload progress is disabled before request parsing, including the
  PHP security team's October 23 follow-up precaution.
- All five exact security commits passed GitHub Actions Linux image builds and
  runtime, extension and Composer platform checks. This Windows host has no
  local Docker/WSL engine. No staging environment was available.
- Existing local unit checks from the original audit passed: Techsense 8 tests /
  33 assertions, Rayhan 1 / 1, Design 1 / 1, HUTBAZAR 1 / 1. Those local checks
  used PHP 8.3.28; the separate image checks used each selected target runtime.
  Kashful has no installed local test dependencies.
- Techsense, Rayhan, Kashful and Design were placed in maintenance, their local
  uploads archived, and dedicated Railway volumes mounted at the existing
  upload paths. Archives were checksum-verified during restoration. Original
  file counts and bytes were preserved through the final deployment.
- HUTBAZAR had no local upload files to transfer and keeps its existing external
  storage configuration.
- Private SELECT 1, database identity, application-key identity, required
  extension availability, effective security INI and production PHP versions
  passed. No database move, framework upgrade or package upgrade was made to
  these five applications. No production migration ran.
- Login pages and dashboard, products and sales page rendering in existing
  admin contexts returned 200 for all four POS applications. Public login CSS
  and JavaScript checks passed on their working application/Railway hosts.
  These checks did not create a real sale, refund, payment or customer upload,
  submit a password login form, execute business jobs or send messages.
- No queue jobs or schedules were executed as a production smoke test. Their
  business execution remains outside this read-only validation.
- Kashful's first volume deployment failed after the build without application
  logs; retry succeeded. One initial DB SELECT failed with a dropped connection;
  retry passed. The original uploads remained protected throughout recovery.
- No exploit, memory-exhaustion test, public phpinfo or diagnostic route was used.
  Limited initial log-indicator review found no selected exploit/miner matches;
  this is not evidence that a previously compromised application is clean.

### Data checks

- Techsense: before transactions 5538, products 6903, contacts 563; verified transactions 5538, products 6903, contacts 563. Uploads 17 files / 403075 bytes.
- Rayhan: before transactions 4113, products 1540, contacts 494; verified transactions 4113, products 1540, contacts 494. Uploads 2 files / 6304 bytes.
- Kashful: before transactions 58180, products 18421, contacts 1096; verified transactions 58180, products 18421, contacts 1096. Uploads 13 files / 2810661 bytes.
- Design: before transactions 554, products 173, contacts 1; verified transactions 554, products 173, contacts 1. Uploads 1 files / 0 bytes.
- HUTBAZAR: before transactions 52, products 359, orders 68; verified transactions 52, products 359, orders 68. Uploads 0 files / 0 bytes.

## Remaining application and security actions

- Design's Railway hostname works, but its custom domain still returns 502.
  The existing target port is 9001 (FPM), while nginx listens on 9000. This
  routing issue was present before the runtime change and remains unresolved.
- HUTBAZAR's root/login HTTP 500 and sampled API HTTP 400 responses existed
  before rollout. Runtime, extensions and DB checks now pass, but application
  business flows remain unverified until those errors are resolved.
- Rayhan's POS hostname and Railway hostname pass HTTPS and asset checks.
  Its additional apex hostname timed out during this verification and remains
  unverified; no claim is made about when that problem began.
- A database credential previously committed in Kashful's deployment guide was
  removed from the current file. Coordinated credential rotation and history
  cleanup remain required; runtime patching does not complete that work.
- PHP 8.2 remains supported only through 2026-12-31. Plan a separate compatible
  upgrade before then. Laravel major upgrades for these five projects were not
  part of this runtime security rollout.

## Recovery

Keep the patched PHP pins and disabled upload-progress setting. If application
code must be rolled back, rebuild the previous compatible application code on
these patched bases; do not select the old vulnerable Railway deployment/image.
Keep migrations disabled. No schema rollback is required. Preserve the upload
volumes; use the checksum-verified archives only if restoration is necessary,
with a write freeze and a backup of the current files first. Database recovery
must preserve new transactions rather than replacing the DB with an old copy.

If suspicious activity is discovered, preserve available private logs and
container evidence before further replacement and investigate persistence.

## Files and merged security changes

### Techsense

[Security change](https://github.com/RK-Rohan/Techsense/pull/2) — merged.

- `.dockerignore`
- `.github/workflows/php-security.yml`
- `Dockerfile`
- `PHP2SHELL-RAILWAY.md`
- `RAILWAY_DEPLOY.md`
- `app/Http/Middleware/PreventRequestsDuringMaintenance.php`
- `docker/start.sh`
- `php.ini`
- `railway.toml`

### Rayhan

[Security change](https://github.com/RK-Rohan/rayhantrading/pull/1) — merged.

- `.dockerignore`
- `.github/workflows/php-security.yml`
- `Dockerfile`
- `app/Http/Middleware/PreventRequestsDuringMaintenance.php`
- `docker/start.sh`
- `php.ini`
- `railway.toml`

### Kashful

[Security change](https://github.com/RK-Rohan/kashful-super-shop/pull/1) — merged.

- `.dockerignore`
- `.github/workflows/php-security.yml`
- `Dockerfile`
- `RAILWAY_DEPLOY.md`
- `app/Http/Middleware/PreventRequestsDuringMaintenance.php`
- `docker/php-runtime.ini`
- `docker/start.sh`
- `railway.toml`

### Design

[Security change](https://github.com/RK-Rohan/design.newlifestylebd.com/pull/1) — merged.

- `.dockerignore`
- `.github/workflows/php-security.yml`
- `Dockerfile`
- `docker/php-security.ini`

### HUTBAZAR

[Security change](https://github.com/RK-Rohan/hutbazar-api/pull/4) — merged.

- `.gitattributes`
- `.github/workflows/php-security.yml`
- `composer.json`
- `composer.lock`
- `start-container.sh`

Kashful's unrelated local installer edit and untracked Accounting archive were
excluded. Production was deployed from scoped GitHub commits, not its dirty
working tree.

## Official sources checked

- [PHP security advisory and October 23 follow-up](https://www.php.net/archive/2026.php)
- [PHP changelog](https://www.php.net/ChangeLog-8.php)
- [PHP supported versions](https://www.php.net/supported-versions.php)
- [FrankenPHP 1.13.2 release](https://github.com/php/frankenphp/releases/tag/v1.13.2)
- [Docker vendor security advisory](https://www.docker.com/blog/security-update-on-php2shell-impact-for-docker-customers/)
- [Railpack PHP version and extension selection](https://railpack.com/languages/php/)
- [Railpack build documentation](https://railpack.com/getting-started/)
- [Railway volumes](https://docs.railway.com/volumes)
