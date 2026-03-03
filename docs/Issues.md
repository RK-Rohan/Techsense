# Issues

Track reported issues and their resolution status.

## Template
- Title:
- Reported By:
- Status: triage | in_progress | blocked | resolved | wontfix
- Repro Steps:
- Expected / Actual:
- Fix Notes:

## Issue Log

### ISS-2026-03-03-01: Quotation/Sales Edit + Print PDF error
- Title: `Unknown column 'sort_order' in 'ORDER BY'` during quotation/sales edit and PDF print
- Reported By: Client (Techsence Bangladesh Ltd.) via production error log on March 3, 2026
- Status: resolved
- Repro Steps:
  - Open Quotation List or Sales List.
  - Click `Edit` or `Print Invoice` from row actions.
  - Server throws SQL error.
- Expected / Actual:
  - Expected: Edit screen and quotation/invoice PDF should load.
  - Actual: Request fails with `SQLSTATE[42S22] Unknown column 'sort_order' in 'ORDER BY'`.
- Fix Notes:
  - Root cause: application code expected `transaction_sell_lines.sort_order`, but production DB schema was not migrated.
  - Added deploy-time migration step in `.cpanel.yml` to run:
    - `/usr/bin/php "$DEPLOYPATH/artisan" migrate --force`
  - Fix commit: `10a26b0` (`Run migrations during cPanel deploy`).
