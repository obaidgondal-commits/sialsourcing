# Local development and Hostinger runtime

The website remains a PHP application compatible with Apache/LiteSpeed and MySQL on Hostinger. It does not need a Node service. Use PHP 8.2 or newer with PDO MySQL, sessions, fileinfo, iconv and GD. SQLite is supported only for the local public-site preview.

## Run a safe local preview

From the repository directory:

```sh
php scripts/setup-preview.php --local
php -d disable_functions=mail -S 127.0.0.1:8765 scripts/preview-router.php
```

Open `http://127.0.0.1:8765`. The router requires the loopback host and disabled native mail function. It adds a `noindex` response header and serves a disallow-all robots file. Forms save only to the ignored `.preview/preview.sqlite`; notification sending is disabled. The port is intentionally fixed at 8765 in both the generated configuration and router. Never bind this preview to `0.0.0.0`, tunnel it, or use it as a production service.

Setup creates only ignored `config.php` and `.preview/` files. Re-running preserves existing preview data. It refuses to overwrite a configuration not marked as its own. To start a clean preview, use a new checkout rather than importing over a production configuration.

Seed content demonstrates the eleven existing product wrapper URLs, four service cards, FAQs and quality checks. There are no buyer records, supplier identities, prices, certificates, published articles, or administrator accounts. One team entry is explicitly a placeholder. All preview text must receive business approval before being used as production content.

Admin URLs are blocked by the preview router. SQLite checks public rendering and form storage; it does **not** verify MySQL-specific admin operations such as `ON DUPLICATE KEY UPDATE` and `IF()`. Verify those against a separate MySQL staging database before launch. The preview router approximates clean-URL redirects; validate the real `.htaccess` on Hostinger as a separate deployment check.

## Production configuration

`config.example.php` is a reusable runtime containing no credentials. Create a server-only `config.php` by copying that file and setting environment variables in the hosting environment. Alternatively, add a private environment loader immediately after its `declare(strict_types=1);` line, before the rest of the runtime:

```php
<?php
declare(strict_types=1);
// Use the actual private path on the hosting account; never commit this file.
require '/absolute/private/path/sialsourcing-environment.php';
// The remainder of config.example.php follows here in this server-only config.php.
```

The private environment loader can call `putenv()` with values supplied through Hostinger. Keep the loader outside `public_html`, with restrictive permissions. `SIAL_PRIVATE_PATH` must point to an existing writable directory outside the application document root. Sessions and throttling state are created there; never put it inside `public_html`.

`setting()` caches repeated reads for one request. After editing a setting, a subsequent request always sees the new database value. The existing settings form performs updates before its first setting read.

| Variable | Production value |
| --- | --- |
| `SIAL_ENV` | `production` (default) |
| `SIAL_SITE_URL` | `https://sialsourcing.com` |
| `SIAL_CANONICAL_URL` | `https://sialsourcing.com` |
| `SIAL_DB_DRIVER` | `mysql` (default) |
| `SIAL_DB_HOST` / `SIAL_DB_PORT` | Hostinger database host and port; default `localhost` / `3306` |
| `SIAL_DB_NAME` | Actual database name |
| `SIAL_DB_USER` | Account scoped to that database |
| `SIAL_DB_PASSWORD` | Server-only database password; no default |
| `SIAL_PRIVATE_PATH` | Absolute writable private directory outside the document root |
| `SIAL_MAIL_ENABLED` | `1` only after notification transport is configured and tested; default `0` |
| `SIAL_MAIL_FROM` | Valid sender address authorised for the site domain |

`send_notification($to, $subject, $body, $replyTo)` returns whether mail was accepted by PHP's configured transport. It validates header values and is disabled in local mode. An accepted PHP `mail()` call does not guarantee delivery. Test inbox delivery, SPF/DKIM/DMARC and spam placement before relying on notifications. RFQs remain in the database if email fails.

`rate_limit($key, $limit, $seconds)` returns `true` if the operation is permitted. Callers must compose a bounded key from a fixed purpose and the actual `REMOTE_ADDR`; do not trust forwarded-IP headers without a configured proxy boundary. Counters use hashed filenames and an exclusive file lock. These files contain counts and expiry timestamps, not raw request details. Schedule removal of expired counter files during server maintenance; retain active windows.

## Database setup and existing data

`database/schema.sql` describes a **fresh** MySQL/MariaDB database for the existing CMS. It has no destructive statements, seed data, or default administrator. It is intentionally not an automatic migration and should not be blindly imported over the current database. First take a restorable backup, compare the existing schema and character encoding, preserve IDs/slugs/content/uploads, and rehearse any required migration in staging.

Use Hostinger's MySQL database tools to import the schema into a new empty staging database. Provision an administrator server-side using PHP `password_hash($password, PASSWORD_DEFAULT)` and a parameterized insert into `admin_users(username,password)`. Choose the password interactively in a private environment; do not include a plaintext password in a shell command, Git, SQL export, or committed setup script. No account is automatically provisioned by this runtime.

The schema matches existing page/admin queries and includes indexes for product lists, articles and enquiries. It is the current CMS foundation, not the future instrument taxonomy or confidential supplier capability matrix. Those require their own reviewed schema and migration plan.

## Publish boundary

Upload only reviewed public PHP pages, `includes/`, browser assets, the production `.htaccess`, approved public uploads, and your server-only production configuration/runtime. **Exclude** `.git/`, `.env*`, `.preview/`, `scripts/`, `database/`, `docs/`, working files, SQL exports, local logs and the generated preview `config.php`. The repository's ignore rules prevent preview files, credentials and uploaded customer assets being accidentally committed. Ignore rules do not prevent accidental web uploads; use an explicit deployment allowlist.

Public image upload handling accepts only decoded JPEG, PNG and WebP up to 5 MiB and 16 megapixels, re-encodes them with GD, and uses random filenames. On Hostinger, separately deny script execution and directory listing in `uploads/`; ensure `.htaccess` enforcement is verified on the deployed server. Preserve existing approved images during migration. The runtime does not delete uploaded files.

Before launch, verify MySQL admin editing, successful and rejected RFQs, session expiration, throttling, actual email receipt, HTTPS redirects, canonical URLs, sitemap entries and access denials for private files. Also replace demonstration content and confirm all claims about capability, people and compliance.
