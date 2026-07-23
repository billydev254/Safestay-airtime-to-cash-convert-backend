#!/bin/bash
# Runs on the cPanel server (via SSH from GitHub Actions) after every push to
# main. Pulls the latest code into the already-cloned repo and rebuilds
# whatever needs rebuilding. The repo lives outside public_html; the domain's
# document root points at its public/ subfolder — see DEPLOY.md.
set -e

cd "$(dirname "$0")"

git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deploy complete: $(git rev-parse --short HEAD)"
