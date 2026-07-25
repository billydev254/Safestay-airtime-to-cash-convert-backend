# Deploying Safestay Deals backend to safestay.co.ke (cPanel)

Server: `rs3.rcnoc.com`, SSH port `1980`, cPanel user `safestay`, home dir
`/home3/safestay`. Two things about this specific host that aren't obvious:

- **The main domain's document root can't be changed** through cPanel's
  Domains page (the field is read-only). Workaround: `public_html` is
  replaced with a symlink pointing at the repo's `public/` folder, instead of
  pointing the docroot at the repo directly.
- **This server's default `php`/`composer` CLI resolve to PHP 8.2**, but the
  app requires 8.3+. Always use the 8.3 binary explicitly:
  `/opt/cpanel/ea-php83/root/usr/bin/php`. The *website itself* also needs to
  be set to PHP 8.3 separately, via cPanel → **MultiPHP Manager** (or
  `uapi LangPHP php_set_vhost_versions version=ea-php83 vhost-0=safestay.co.ke`)
  — the CLI and the live site have independent PHP version settings on this
  host, forgetting the second one gives a "Composer dependencies require PHP
  >= 8.3.0" fatal error on every page even though CLI commands work fine.

## One-time server setup (via SSH)

1. Clone the repo **outside** `public_html` (never put a Laravel repo's root
   directly in a web-exposed folder — `.env` and `app/` would become
   publicly downloadable):

   ```
   cd ~
   git clone https://github.com/billydev254/Safestay-airtime-to-cash-convert-backend.git safestay-backend
   cd safestay-backend
   /opt/cpanel/ea-php83/root/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
   ```

   If the repo is private and this fails with an auth prompt/rejection, see
   "Private repo access" below.

2. Swap `public_html` for a symlink into the repo (back up what's there
   first — don't delete):

   ```
   cd ~
   mv public_html public_html_old_backup
   ln -s /home3/safestay/safestay-backend/public public_html
   ```

3. Set the domain to PHP 8.3: cPanel → **MultiPHP Manager** → select
   `safestay.co.ke` → `8.3` → Apply.

4. Drop in the real `.env`: copy the contents of `backend/.env.production`
   (has the real DB credentials and a fresh APP_KEY — kept out of git) into
   `~/safestay-backend/.env` on the server.

5. Database: already imported (`safestay_airtime_to_cash`, schema + seed
   data). If starting fresh instead, either import `safestay_import.sql` via
   phpMyAdmin's SQL tab, or run:

   ```
   /opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --seed --force
   ```

6. Make `deploy.sh` executable:

   ```
   chmod +x ~/safestay-backend/deploy.sh
   ```

7. Log in at `https://safestay.co.ke/admin` and **change the default admin
   password immediately**, set up 2FA when prompted.

## Private repo access

The repo is private. Cloning/pulling from the server over plain HTTPS asks
for a username/password, and GitHub no longer accepts password auth for git
— it fails. Until a proper read-only Deploy Key is set up (GitHub repo →
Settings → Deploy keys, paired with an SSH key that lives only on the
server, then `git remote set-url origin git@github.com:...`), the fallback
is: temporarily flip the repo to Public on GitHub, `git pull`, flip it back
to Private. Not ideal for a recurring workflow — worth fixing properly
before this needs to happen often.

## Setting up auto-deploy on every push

GitHub Actions (`.github/workflows/deploy.yml`, already in the repo) SSHes
into the server after every push to `main` and runs `deploy.sh`, which pulls
the new code, reinstalls composer deps (via the PHP 8.3 binary), runs any
new migrations, and rebuilds Laravel's caches.

Add these secrets in **GitHub → this repo → Settings → Secrets and variables
→ Actions → New repository secret**:

| Secret | Value |
|---|---|
| `SSH_HOST` | `rs3.rcnoc.com` |
| `SSH_PORT` | `1980` |
| `SSH_USERNAME` | `safestay` |
| `SSH_PRIVATE_KEY` | The deploy private key (generated separately, see chat history / ask for it again) |
| `DEPLOY_SCRIPT_PATH` | `bash ~/safestay-backend/deploy.sh` |

The matching **public** key must be authorized in cPanel first:
**Security → SSH Access → Manage SSH Keys → Import Key** → paste the public
key → Authorize. This SSH key is separate from the private-repo Deploy Key
above — one logs into the server, the other lets the server read from
GitHub.

## Day-to-day workflow, once this is all set up

```
git add -A
git commit -m "whatever changed"
git push origin main
```

That's it — GitHub Actions handles the rest, and the live admin app picks up
the change within the same push (typically well under a minute).

## Manual deploy (until auto-deploy is wired up)

```
ssh safestay@rs3.rcnoc.com -p 1980
cd ~/safestay-backend
git pull origin main
/opt/cpanel/ea-php83/root/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
/opt/cpanel/ea-php83/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan filament:upgrade
```
