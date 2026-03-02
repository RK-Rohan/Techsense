#!/usr/bin/env bash
set -euo pipefail

DEPLOYPATH="${DEPLOYPATH:-/home/dailyvoi/techsense.dailyvoice.live/}"

/bin/cp -R ./* "$DEPLOYPATH"
date > "$DEPLOYPATH/DEPLOYED_AT.txt"

/usr/bin/php "$DEPLOYPATH/artisan" config:clear || true
/usr/bin/php "$DEPLOYPATH/artisan" cache:clear || true
/usr/bin/php "$DEPLOYPATH/artisan" config:cache || true
/usr/bin/php "$DEPLOYPATH/artisan" route:cache || true
/usr/bin/php "$DEPLOYPATH/artisan" view:cache || true
