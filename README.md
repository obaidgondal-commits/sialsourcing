# SialSourcing

PHP website and CMS for product sourcing across Sialkot, Wazirabad and Faisalabad, Pakistan. The upgrade preserves existing product URLs while adding regional sourcing guides, clearer RFQs and a more consistent buyer experience.

## Preview

Use PHP 8.2+ with PDO SQLite for local preview:

```sh
php scripts/setup-preview.php --local
php -d disable_functions=mail -S 127.0.0.1:8765 scripts/preview-router.php
```

Open `http://127.0.0.1:8765`. Preview data is synthetic; email and admin access are disabled. Existing production credentials, uploads and databases are not read. See [local development](docs/LOCAL-DEVELOPMENT.md) for runtime requirements and helper contracts.

## Validate and package

```sh
python3 scripts/package-release.py --check-only
python3 scripts/verify-site.py
python3 scripts/package-release.py
```

The second command writes a content-addressed ZIP and SHA256 manifest under ignored `.preview/releases/`. It includes only the explicit public-site allowlist, requires PHP syntax checks, rejects symlinks/unreviewed PHP files and verifies ZIP hashes. It never packages `config.php`, `config.example.php`, uploads, SQL, Git data, internal docs, scripts or local preview data.

GitHub Actions validates source, the local schema and isolated website/form flows, then retains this reviewed package. The verification script uses a fresh temporary fixture and localhost requests with mail disabled; it does not use production data. The workflow has no deployment credentials and does not publish anything. Review changes in a pull request before merging.

## Hostinger

Use the [Hostinger deployment procedure](docs/HOSTINGER-DEPLOYMENT.md). Back up the existing site, configuration, uploads and database; rehearse against a separate MySQL staging database; preserve server-only configuration and existing data when applying the reviewed file package.

The new pages require the helper contract in `config.example.php`. An older live `config.php` must be reconciled manually before the upgrade. That runtime and `database/schema.sql` are manual setup references, deliberately outside the public release archive. The SQL file is a fresh-install schema, not an automatic migration.

## Working documents

`docs/` contains the current provisional expansion review and implementation/deployment documentation. The original 14-document Claude specification was not available and has not been reviewed. Public page code, proposed future catalogue architecture and confidential supplier-capability records have different release requirements. Do not add buyer records, factory identities, private catalogues or credentials to this repository or the public package.

Business claims, product imagery, certification scope and published content require approval before launch. Search visibility also depends on original content, verified capabilities and ongoing measurement; technical SEO is the foundation, not a ranking guarantee.
