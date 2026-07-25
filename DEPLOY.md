# Deploying Safestay Deals backend to safestay.co.ke (cPanel)

## One-time server setup (via SSH)

1. SSH into the server, then clone the repo **outside** `public_html` (never
   put a Laravel repo's root directly in a web-exposed folder — `.env` and
   `app/` would become publicly downloadable):

   ```
   cd ~
   git clone https://github.com/billydev254/Safestay-airtime-to-cash-convert-backend.git safestay-backend
   cd safestay-backend
   composer install --no-dev --optimize-autoloader
   ```

2. Point the domain's document root at `~/safestay-backend/public`:
   cPanel → **Domains** → find `safestay.co.ke` → edit → set Document Root
   to `safestay-backend/public`.

3. Upload the real `.env`: copy the contents of `backend/.env.production`
   (already prepared, has the real DB credentials and a fresh APP_KEY) into
   `~/safestay-backend/.env` on the server.

4. Import the database: use phpMyAdmin on `safestay_airtime_to_cash` → SQL tab → paste
   `safestay_import.sql` (already covers schema + seed data), **or**, since
   the repo is already cloned with `.env` in place, just run migrations
   directly instead:

   ```
   php artisan migrate --seed --force
   ```

5. Make `deploy.sh` executable (should already be, but just in case):

   ```
   chmod +x ~/safestay-backend/deploy.sh
   ```

6. Log in at `https://safestay.co.ke/admin` and **change the default admin
   password immediately**.

## Setting up auto-deploy on every push

GitHub Actions (`.github/workflows/deploy.yml`, already in the repo) SSHes
into the server after every push to `main` and runs `deploy.sh`, which pulls
the new code, reinstalls composer deps, runs any new migrations, and rebuilds
Laravel's caches.

Add these secrets in **GitHub → this repo → Settings → Secrets and variables
→ Actions → New repository secret**:

| Secret | Value |
|---|---|
| `SSH_HOST` | Your server's SSH hostname/IP (from cPanel → SSH Access) |
| `SSH_PORT` | Your server's SSH port (often 22, sometimes custom — cPanel's SSH Access page states it) |
| `SSH_USERNAME` | Your cPanel username |
| `SSH_PRIVATE_KEY` | The deploy private key (see below) |
| `DEPLOY_SCRIPT_PATH` | `bash ~/safestay-backend/deploy.sh` |

The matching **public** key must be authorized in cPanel first:
**Security → SSH Access → Manage SSH Keys → Import Key** → paste the public
key → Authorize.

## Day-to-day workflow, once this is all set up

```
git add -A
git commit -m "whatever changed"
git push origin main
```

That's it — GitHub Actions handles the rest, and the live admin app picks up
the change within the same push (typically well under a minute).
