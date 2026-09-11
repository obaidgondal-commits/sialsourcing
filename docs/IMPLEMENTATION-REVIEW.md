# SialSourcing review and implemented foundation

> **Updated owner requirement — 11 September 2026:** preserve existing data, content, downloads, URLs and CMS behavior while improving the already-live English website. The current implementation draft and its existing ZIP do not yet meet that requirement. Follow [the revised preservation review](PRESERVATION-REVIEW.md) before deployment.

Reviewed on 11 September 2026. Starting repository: `obaidgondal-commits/sialsourcing`, commit `c192687`.

## Material actually available

- The owner's pasted Claude plan and four contested questions.
- The public GitHub repository, its PHP pages/admin panel, routing, styles, JavaScript, deployment and SEO text files.
- The public website, representative categories and official current SEO/regulatory/hosting guidance.
- All six PDFs in `assets/docs/`: company profile, RFQ brief, apparel tech pack, QC request, sample QC report and US import checklist. These contained dated commercial/compliance assurances and have been revised in this release.
- An older locally available SialSourcing SEO strategy, considered historical context rather than current evidence.

**Not available:** Claude's 14 Markdown documents, `REVIEW-BRIEF.md`, `CONTEXT.md`, the catalogue archive, supplier agreements, current production database/configuration/uploads, Search Console data and Hostinger staging access. No claim is made that these missing materials were reviewed. Their absence limits final catalogue planning and live content verification.

## Main findings

1. The source was already on GitHub and the repository was public. Public authentication source alone is not an authentication vulnerability. The visible login uses password verification; actual issues included output before redirect handlers and incomplete deploy reproducibility. No current live database credentials were obtained or committed.
2. Missing configuration and schema meant a fresh clone could not run. This release adds a safe configuration example, fresh-install schema and isolated preview setup. The new schema is not a migration to apply blindly to the live database.
3. Category structured data incorrectly presented SialSourcing as the manufacturer of entire categories. It now models collection pages and sourcing services without invented offers, prices or ratings.
4. Legacy commercial and certification guarantees were too broad. Homepage/About/Services/QC and wrapper defaults are revised. Existing production product, FAQ and blog records must still be checked against evidence before launch.
5. Dental-first catalogue work should not delay the wider website. Existing industries remain, with distinct Wazirabad and Faisalabad routes and a textiles guide.
6. The earlier SEO plans treated rankings and timelines too confidently. No page count, tool, keyword density or markup guarantees international customer acquisition. The plan now prioritizes useful buyer information and measured qualified enquiries.

## Implemented

- Editorial navy/gold responsive homepage with a labelled illustrative still life; clearer product navigation and accessible mobile menu.
- Sourcing-region index; distinct Sialkot, Wazirabad and Faisalabad buyer guides; home-textiles guide.
- Server-rendered category directory with optional client-side search. Existing product paths are retained.
- Better RFQ fields for destination, quantity, region and target date; product/region preselection; validation, CSRF, throttling and save-before-notify behavior.
- Admin handler/template separation, CSRF logout, login throttling, input validation and appropriate private-page headers.
- Safer JSON-LD encoding, corrected category page types, future/draft blog exclusion, crawlable blog pagination, canonical handling, sitemap filtering and legacy redirects.
- Content visible without reveal JavaScript, native FAQ disclosure, keyboard focus styles and reduced-motion support.
- Environment-based MySQL configuration and localhost-only synthetic SQLite preview with native email disabled.
- A reviewed public-file release package with SHA256 manifest, validation-only GitHub Actions and Hostinger staging/rollback instructions.

## Important boundaries

- This is a first implementation of the public-site foundation, not the completed private supplier-capability/catalogue platform. See `CATALOGUE-ARCHITECTURE.md` for the proposed next stage.
- Preview data is synthetic and deliberately contains no real suppliers, buyers, admin credentials or product photos. The generated hero is decorative and visibly labelled; it is not factory or stock evidence.
- The existing production analytics property is preserved only for production requests. Set `SIAL_GA_MEASUREMENT_ID` to an empty string to disable it; confirm the applicable consent/privacy setup before launch. Local tests do not load analytics.
- About, Solutions and Lab/QC now use reviewed static narrative. Their legacy CMS tables are retained but no longer render into these pages. Product/FAQ/blog/team editing remains database-driven.
- Production SMTP delivery, MySQL compatibility against the actual database, real stored HTML/content, server routing and Google indexing remain staging/live checks. No production deployment or database alteration has occurred.

## Release gate

Obtain the missing planning documents and reconcile business requirements; confirm Wazirabad; review production content and supplier evidence; back up live files/uploads/database; stage the new runtime against a separate MySQL copy; verify forms and actual email delivery; verify redirects, noindex removal, canonical host and sitemap through Hostinger; then release the reviewed package and monitor Search Console and enquiry handling.

Full reasoning and primary-source references: `EXPANSION-REVIEW.md`. Installation and rollback: `HOSTINGER-DEPLOYMENT.md`.

## Verification record

The final local verifier passed for 28 public routes, 56 local references, metadata/JSON-LD and sitemap checks; RFQ context, invalid-input protection, persistence and refresh behavior; and blog visibility/pagination/security. Actual Apache testing passed 34 route/security checks, including upload PATH_INFO and multiple-extension denials. The checks use synthetic data and cannot certify the live MySQL database, real email delivery or field Core Web Vitals. Desktop and 390px mobile layouts were inspected; mobile category search, product-to-RFQ preselection and a saved local enquiry were exercised. All six revised PDFs (11 pages total) were rendered and visually checked. The public release allowlist and PHP syntax checks passed.
