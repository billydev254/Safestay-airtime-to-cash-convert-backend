#!/bin/bash
# Runs on the cPanel server (via SSH from GitHub Actions) after every push to
# main. Pulls the latest code into the already-cloned repo and rebuilds
# whatever needs rebuilding. The repo lives outside public_html; public_html
# is a symlink to this repo's public/ folder (the host doesn't allow changing
# the main domain's document root) — see DEPLOY.md.
#
# This server's default `php`/`composer` resolve to PHP 8.2, but the app
# requires 8.3+ (matches the PHP version set for the domain in MultiPHP
# Manager) — so both are invoked explicitly via the 8.3 binary below.
set -e

PHP_BIN=/opt/cpanel/ea-php83/root/usr/bin/php

cd "$(dirname "$0")"

git pull origin main

"$PHP_BIN" /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan filament:upgrade

echo "Deploy complete: $(git rev-parse --short HEAD)"
