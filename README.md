# SialSourcing — the existing website, extended

This repository preserves the complete original SialSourcing website and adds detailed instrument browsing inside its existing surgical and dental pages. The homepage, shared navigation, typography, colours, existing category content, blog, team, FAQs, resources and PDFs retain their original design and content.

The original application files were checked against the live Hostinger download on 19 September 2026 and matched commit `c192687`. The repository now also contains the three uploaded public images, a sanitized snapshot of the public CMS content, and a credential-free configuration example so the site can be restored completely.

## Instrument detail integration

- Existing `/surgical-instruments` and `/dental-instruments` pages gain an instrument browsing section when a catalogue export is configured.
- `/dental-instruments/extraction` lists the available families; `/dental-instruments/extraction/{family-slug}` shows variants, filters and recorded specifications in the existing site style.
- Selected references open the original `/contact` form with a validated family and SKU selection. The server validates the references again when saving the enquiry.
- The current private preview contains 42 dental extraction families and 344 variants. These are imported pilot specifications awaiting human review, not the entire future surgical range. No product photographs or approvals have been invented.
- Published mode displays only verified specifications. Without a catalogue file, the original category pages continue to work without the added section.

Set `SIAL_CATALOGUE_FILE` to an absolute JSON export path **outside the document root**. Export schema version 1 contains only public family/product fields. The hardened importer, provenance review and export tools are maintained in the private [catalogue operations repository](https://github.com/obaidgondal-commits/sialsourcing-catalogue).

The existing CMS and the instrument catalogue are separate stores. Both have a table called `products` with different meanings. **Never run the instrument migrations in the website CMS database.**

## Knowledge base and photographs

The existing Resources page links to `/knowledge-base`. The first three source-linked guides cover surgical-instrument specifications/evidence, football procurement/testing, and a Sialkot industry buyer map including leather and textiles. Search and industry filters work without JavaScript. Guides use the existing navigation, fonts and colour palette. Each article includes linked sources, preparation date, review status, related products and a corrections route.

All three are **drafts**, prepared with AI assistance and awaiting a real specialist review. Drafts appear only in trusted staging and are excluded from the sitemap. Publication requires `status: published`, a real `reviewer`, and `reviewed_on` in `includes/knowledge-content.php`; technical changes must reset those fields and return the guide to draft. No author credentials, review or product certification are implied. The [credibility review](docs/credibility-review.md) identifies existing marketing claims that need evidence or correction before a public knowledge-base launch; it does not change the preserved original copy.

Instrument hero images, family cards and variant galleries are ready for **owned or supplier-authorized photographs**, each mapped to its exact SKU. No source photographs are currently present. Missing variants keep the honest placeholder. Configure `SIAL_INSTRUMENT_MEDIA_FILE` to a private JSON manifest outside every web root; only approved, hash-matched, valid JPG/PNG/WebP images are returned. Private permission/source records do not reach HTML. A family illustration identifies the specific variant shown. PHP GD is required for image validation. Follow the [photography brief](docs/instrument-photography.md); only approved delivery files belong in `uploads/instruments/` because static image URLs are public. Keep originals and permission evidence private.

The staging builder accepts optional `--media-manifest /private/instrument-media.json`. This copies the manifest outside `public_html`; approved delivery images must already be in `uploads/instruments/`. Always include the current manifest when building a release containing photographs.

## Run the complete website locally

Requirements: PHP 8.1+ with PDO SQLite, Python 3.9+. Run from the repository root:

```sh
python3 scripts/setup-preview.py --output /tmp/sialsourcing-preview/cms.sqlite --site-url http://127.0.0.1:8878
cp config.example.php config.php
SIAL_STAGING=1 SIAL_SITE_URL=http://127.0.0.1:8878 SIAL_CMS_FILE=/tmp/sialsourcing-preview/cms.sqlite php -S 127.0.0.1:8878 scripts/preview-router.php
```

Open `http://127.0.0.1:8878/`. For the instrument preview, also set `SIAL_CATALOGUE_FILE=/absolute/private/catalogue.json` before starting PHP. A `staging-preview` export is accepted only with trusted `SIAL_STAGING=1` configuration.

The preview restores 121 public content records across nine tables: 11 category/subcategory pages, 16 blog posts, 66 product FAQs, six general FAQs, six solutions, six lab tests, five team profiles and five public settings. It creates empty isolated enquiry/application inboxes. The setup command refuses to overwrite an existing database. Staging suppresses form emails, blocks administration, and sends noindex/no-store headers. Local PHP binds only to localhost.

## Restore or deploy the existing CMS

For a new MySQL/MariaDB installation, import `database/schema.sql` followed by `database/public-content.sql` into a **new empty website CMS database**. These restore files contain no administrator credentials, contact submissions or manufacturer applications. Configure a separate administrator through the operator's private deployment process. Existing production installations should retain their current `config.php`, database and uploaded assets; they do not need the restore SQL.

Keep `config.php` ignored. `config.example.php` supports `SIAL_DB_HOST`, `SIAL_DB_PORT`, `SIAL_DB_NAME`, `SIAL_DB_USER`, `SIAL_DB_PASS`, `SIAL_DB_CHARSET` and `SIAL_SITE_URL`. Production defaults to MySQL and `https://sialsourcing.com`. Do not place database passwords or private catalogue/source documents in GitHub.

`data/site-content.json` is the public snapshot used for local/staging restores. Only active/published content is included; non-displayed team contact fields are cleared. Runtime deployment need not include `data`, `database`, `scripts` or `tests`; their `.htaccess` files deny direct web access.

## Dedicated Hostinger staging

`scripts/build-staging.py` packages the complete site, three public uploads, an isolated SQLite CMS and a public-field catalogue export. Its output is a **dedicated staging site root** containing `public_html`, `full-site-private` and `full-site-config.php`. The last two remain outside `public_html`. It reuses the existing review login hash, blocks plaintext access, disables production analytics, blocks admin access, suppresses email and keeps pending specifications private. Existing public CSS, JS, images and PDFs are ordinary public assets; dynamic pages require the staging login.

```sh
python3 scripts/build-staging.py --catalogue /private/catalogue.json --auth-config /private/catalogue-config.php --output /private/new-release --origin https://staging.example.com
```

Do not upload this staging configuration to the production site. Back up the previous staging `public_html` outside the web root before switching releases. Hostinger requires PHP PDO SQLite for this isolated staging package; production continues using its existing MySQL CMS.

## Validation

```sh
php tests/instrument-data-test.php
python3 tests/contact-instruments-test.py
python3 tests/setup-preview-test.py
php tests/knowledge-base-test.php
php tests/instrument-media-test.php
python3 scripts/test_site_integration.py --baseline-url http://127.0.0.1:8879 --integrated-url http://127.0.0.1:8878
```

The last command compares an untouched original-site preview with the integrated preview and expects the 42-family pilot snapshot. It performs GET requests only. Initial verification passed 203 checks: 37 unaffected pages match the original rendered HTML after URL/CSRF/cache-version normalization; both medical pages preserve the original hero, content/gallery, specifications, FAQs, CTA and footer; all 344 variants and 42 routes work. Mobile and desktop browsing, search, specifications and selection-to-contact were also checked in the browser. The complete CMS restore passed on MySQL 8.4 and MariaDB 10.11.

The existing live production website has not been changed by this integration branch.
