# Hostinger deployment and rollback

This is the procedure for deploying the reviewed SialSourcing PHP upgrade. No deployment has been performed by the packaging script or GitHub validation workflow. This first release needs a staging rehearsal and a deliberate production cutover because the repository does not contain the live database, uploaded media or server-only configuration.

Hostinger Web and Cloud hosting use LiteSpeed and support `.htaccess`; `mod_rewrite` is enabled according to [Hostinger's server-access documentation](https://www.hostinger.com/support/which-file-transfer-and-server-access-options-are-supported-at-hostinger/). The project uses PHP, MySQL and Apache-compatible rewrites. It needs no Node server. Use PHP 8.2 or newer, matching the tested staging version; select PHP options/extensions through hPanel and verify the website's PHP version, which may differ from SSH's default. [Hostinger's PHP version guidance](https://www.hostinger.com/support/4047803-how-to-change-the-php-version-for-subfolders-or-subdomains-in-hostinger/)

## 1. Capture a restorable baseline

1. Identify the actual document root, current PHP version, MySQL database and existing DNS/SSL settings. Record the current release and important URLs, including product slugs and article URLs.
2. Download both website files and the matching database backup. Separately preserve the current `config.php`, all uploaded media, `.htaccess` and any private storage outside the document root. Keep them in restricted storage outside Git and web-accessible directories. Hostinger provides separate file and database downloads in hPanel's Backups area. [Hostinger backup instructions](https://www.hostinger.com/support/5981435-how-to-download-backups-at-hostinger/)
3. Verify that archives open, uploads are present and the database can be restored into a disposable database. A backup is not verified until a restore has succeeded.
4. Record current table row counts and slugs. Protect personal enquiry data and supplier records during any export; use synthetic or redacted records for routine staging tests.

Do not delete the existing site or connect a new deployment over it as a shortcut. Current Hostinger Git instructions warn that switching repositories can overwrite files in the target directory. [Hostinger Git deployment](https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/)

## 2. Reconcile runtime and schema in staging

Create a separate custom-PHP staging website/document root and a separate MySQL database with separate credentials. Use access protection plus `X-Robots-Tag: noindex, nofollow` at the staging server level. Do not rely on a production canonical tag to keep staging private. Keep notifications disabled. The local-only SQLite preview is not a Hostinger staging system.

Use the actual production schema as the starting point. Compare `SHOW CREATE TABLE` output for every existing table against `database/schema.sql`, including column names/types, nullability/defaults, indexes, character sets, foreign keys and timestamp behaviour. The file describes a **fresh empty install**. Never import it blindly over the live database. Prepare only the additive or otherwise reviewed migration steps demonstrated to be necessary, take another backup before running them, and verify existing IDs/slugs/row counts afterwards. If no schema change is needed, perform none.

The updated application expects `db()`, `setting()`, escaping/CSRF helpers, `rate_limit()` and `send_notification()` from `config.example.php`. Preserve existing production credentials and database identity while constructing a server-only compatible `config.php`. Do not overwrite the live configuration with the generated local preview file. The public release archive intentionally contains neither configuration file. The simplest manual setup is to copy the reviewed runtime into a new server-only `config.php` in staging and supply the documented `SIAL_*` environment values via a private loader outside the document root. Reconcile any existing project-specific helpers before cutover. See [runtime configuration](LOCAL-DEVELOPMENT.md).

Create `SIAL_PRIVATE_PATH` outside `public_html`, writable only by the hosting account; it stores sessions and throttling counters. Confirm PDO MySQL, sessions, fileinfo, iconv and GD. Use the existing production administrator account/password hash after schema reconciliation. The runtime creates no default administrator. New accounts, if necessary, must be provisioned privately with a PHP password hash.

Staging uses production runtime mode with a staging HTTPS `SIAL_SITE_URL`, the intended `SIAL_CANONICAL_URL`, separate private storage and `SIAL_MAIL_ENABLED=0`. The production `.htaccess` contains production host redirects; make a documented staging-only server configuration override so requests stay on staging, and reapply the reviewed production redirects at cutover. Do not commit a staging hostname or weaken production routing as part of that override.

## 3. Build and inspect the release

From the reviewed checkout:

```sh
python3 scripts/package-release.py
```

The result is `.preview/releases/sialsourcing-release-<content-id>-<commit>.zip` plus a sibling `.manifest.json`; uncommitted builds also have a `-draft` suffix. The script validates all allowed PHP and runtime/setup PHP with `php -l`, rejects symlinks and newly introduced unreviewed PHP files, copies only an explicit public allowlist, and checks ZIP entries/hashes. The manifest records per-file SHA256 hashes, the ZIP hash, source commit, working-tree status and the PHP lint version. Build the final production candidate from the exact reviewed commit with no uncommitted changes; a local dirty build is a review artifact only.

The ZIP contains application files and reviewed public assets, including public resource PDFs. It excludes configuration, uploads, database dumps/schema, internal docs, scripts, `.git`, `.github`, `.preview`, backups and private data. The manifest remains outside the ZIP. The script does not connect to Hostinger, change database records, remove files or deploy.

Inspect public PDFs and image rights/claims as content, not just as file types. Hash validation proves file integrity; it does not prove the accuracy of business claims.

## 4. Verify the complete staging flow

Check desktop/mobile navigation and content, every product/region guide, existing blog posts, real uploaded product images and resource downloads. Verify custom 404 responses, HTTPS/canonical-host redirects, `.php` and old product-path redirects, canonical tags, robots directives, sitemap URLs and structured data against visible facts. Test real LiteSpeed `.htaccess` behaviour; the PHP preview router cannot validate it.

Test RFQ, manufacturer and inspection forms for valid submission, missing fields, invalid/oversized input, rejected CSRF, honeypot and throttling. Confirm each successful request is saved once with the intended category/context. Test MySQL admin login, wrong-password throttling, session expiry, logout, content edits, settings, article publication and image uploads. Check error logs without exposing customer data.

Verify that `config.php`, `.env`, `.git`, SQL/backups, logs and private storage cannot be downloaded. Public release packaging must leave existing `uploads/` intact. Confirm script execution and directory listing are denied in uploads on the actual server. Never use a destructive synchronisation command with `--delete` against `public_html`.

The current notification helper uses PHP's configured `mail()` transport; it does **not** configure an authenticated SMTP client. Keep it disabled until an authorised sender/transport is configured. Send an explicitly authorised test to the business inbox and verify inbox receipt, reply-to behaviour, SPF/DKIM/DMARC and spam placement. A `true` return from PHP `mail()` only means transport acceptance, not successful inbox delivery. Database storage remains the source of truth. If Hostinger requires authenticated SMTP, add and test that transport before enabling notifications.

## 5. Apply the approved production candidate

1. Record the approved commit and manifest ZIP hash. Reconfirm a fresh restorable backup, the reviewed runtime configuration and any separately approved database migration. Pause admin content editing for the cutover; preserve incoming enquiries if database work needs a write pause.
2. Upload/extract the release into a private temporary directory and verify the manifest. Compare destination files and apply **only** the reviewed archive paths to the live root. Preserve `config.php`, uploaded files, database, private session/rate-limit storage and hosting-generated configuration. Apply separately reviewed runtime changes deliberately; the ZIP cannot do this for you.
3. Apply only the rehearsed necessary database changes, if any. Do not replace the production database with SQLite or seeded demo content. Do not replace product IDs, slugs, buyer records or supplier data.
4. Verify production host/HTTPS redirects, database connection, error logs, live contact saving, uploaded images, sitemap and critical old URLs immediately. Remove staging-only noindex/access settings only from the intended public production site. Keep admin/private pages unindexed.
5. Enable notifications only after the authorised delivery check succeeds. Reopen admin editing and retain the old release/backups and deployment record in private storage.

## Rollback

If a critical check fails, restore only the previously backed-up code files that the release changed. Preserve current configuration and uploads unless the failure is specifically in a reviewed configuration change. Restore the prior runtime configuration only from its verified private backup. A code rollback should not replace the database or discard enquiries received since the backup. If a schema rollback is required, follow the rehearsed migration-specific procedure and reconcile post-backup writes first; do not blindly restore an older database.

## GitHub-to-Hostinger deployment later

GitHub is the source/review system; it does not have to be the first deployment transport. Hostinger currently offers GitHub OAuth integration for custom PHP/HTML sites on eligible Web/Cloud plans through hPanel → Advanced → Git, with branch selection and manual/automatic deployment. Confirm availability for the account. [Current Hostinger instructions](https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/)

Do not point that integration at this complete repository's root inside `public_html`: it includes internal docs and setup tools intentionally absent from the release. A future automation should deploy only the validated allowlist artifact through a reviewed preserve-existing-data procedure, or use a deliberately generated deployment branch containing those files. Verify configuration/upload preservation against Hostinger's actual deployment behaviour before enabling auto-deploy. The provided GitHub workflow only validates and retains a package; it contains no deployment credentials and does not publish the site.

Hostinger documentation checked 11 September 2026. Hosting account limits and UI can change; confirm settings in the actual account during the staging rehearsal.
