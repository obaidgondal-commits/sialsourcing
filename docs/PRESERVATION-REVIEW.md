# Revised review: improve the live English site and preserve its data

11 September 2026. This review supersedes conflicting rollout recommendations in the earlier implementation and expansion reviews. The owner clarified: “i want to keep the data as it is” and the English website is already live.

## Decision

Upgrade the existing English website in place. Preserve its records, original content and downloads, media, identifiers, published URLs and CMS editing behavior. Improve presentation, accessibility, performance and technical SEO around those assets. Catalogue expansion is a separate, additive workstream.

The current draft PR is **not ready to deploy under this requirement**. No production deployment or production database change has occurred, but the draft changes visible content and bypasses parts of the existing CMS. Its previously generated release ZIP must not be used as the preservation release. This review changes documentation and the release recommendation only; it does not claim those application changes have already been corrected.

## What the live site establishes

- English is already published. There is no initial English-language launch to wait for or rebuild from zero.
- Direct HTTP checks on 11 September 2026 showed `https://www.sialsourcing.com/` returning a 301 redirect to `https://sialsourcing.com/`. The destination returned 200 and declared `https://sialsourcing.com/` as its canonical URL. Preserve this existing host preference.
- Preserve existing English paths. Do not introduce `/en/`, rename established categories or change product references merely to prepare for possible future languages. Google recommends consistent canonical signals and permanent redirects when URLs actually change. [Canonical guidance](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls).
- Search Console and analytics were not available to this review. That does not establish that demand data is absent: an existing website may already have useful query, country and enquiry history.

## What “preserve the data” includes

| Preserve | Practical requirement |
| --- | --- |
| Database records | No resets, demo imports, replacement database, renumbering or unsolicited taxonomy migration. Retain products, specifications, FAQs, articles, team, services, lab tests, settings and enquiries. |
| Public content | Keep existing copy, category membership, order and published article visibility as the starting point. Record suggested factual or editorial corrections separately. |
| Assets and downloads | Keep uploaded images and original PDFs at their existing URLs. A PDF replacement is a content change even when its filename stays the same. |
| Identity and URLs | Keep primary keys, product references, slugs, internal relationships and working published routes. Technical SEO must not silently remove existing content. |
| CMS and operations | Admin edits must continue to appear on the intended public pages. Preserve enquiry storage, recipient settings and working notification delivery. |
| Hosting configuration | Retain production database identity, credentials, uploads and hosting settings. Reconcile any necessary code helpers with the current runtime in staging. |

Metadata, layout, navigation styling, image delivery and structured data can be improved without rewriting database content. Changes that affect factual claims, product meaning, visible membership or publishing behavior need to be listed as content changes, not bundled invisibly into technical SEO.

## Audit of the current draft

This comparison uses repository baseline `c192687` and implementation commit `6eeb630`, not a complete production export. Repository source is useful evidence but cannot establish the exact current contents of the live database.

| Area | What the draft does | Required revision |
| --- | --- | --- |
| Homepage | Replaces database-driven products, solutions, recent posts and FAQs with static content. | Apply the new layout to the existing queries and records; preserve their intended visibility. |
| Services and lab/QC | Replaces `solutions` and `lab_tests` output with static narrative while the admin still edits these tables. | Restore the connection between CMS edits and public output. |
| Business copy and downloads | Rewrites About, homepage, services, QC, team/footer text and all six PDFs. | Retain original content and PDFs for the preservation release; separate proposed corrections for review. |
| Category listing | Changes the `sort_order < 100` selection, filters by available PHP wrappers, inserts a static Home Textiles item and removes listing images/badges. | Preserve current membership, ordering and media; add new categories separately when supported. |
| Product details | Continues to query product, gallery and FAQ records, but changes defaults, labels and metadata precedence. | Keep the data connection and verify actual rendered content against the live baseline. |
| Blog | Adds stricter lowercase slug checks and a new publication-date cutoff. | Inventory real published slugs/dates first; preserve all currently published articles and their URLs. |
| RFQ and email | Uses the existing enquiry table, but makes destination required, changes future message formatting and introduces a mail helper disabled by default. | Preserve existing enquiries and delivery; test any new fields or helpers against the established workflow. |
| Packaging | Excludes database, configuration and uploads, but includes rewritten public PDFs and pages. | Keep the exclusion controls; rebuild from the corrected preservation implementation. |

Useful changes to retain where compatible include accessible navigation, responsive layouts, escaped JSON-LD, accurate page types, form validation, CSRF protection, admin redirect fixes, restricted upload execution and explicit release packaging. The local synthetic-data tests show that the draft runs; they do not prove preservation of live content or CMS behavior.

## Reassessment of Claude's four contested questions

### P0: preserve and improve the existing public site

P0 should cover a live content/URL baseline, a verified private backup, a staging copy, CMS-preserving design improvements, technical SEO and end-to-end enquiry checks. It does not require a new product taxonomy, a new database or completion of 120 factory interviews.

Keep the existing business categories. Later regional guides can connect buyers to relevant existing products across Sialkot, Wazirabad and Faisalabad. Use distinct, useful sourcing information on each guide; avoid duplicating every product across every city or language just to increase page count. “Wazirabad” remains an interpretation of the owner's earlier “Zirabad” and should be confirmed before publishing new location claims.

### Supplier confidentiality and ANVISA: separate from the website upgrade

Private supplier capability records can be added later with access controls. Regulatory disclosure questions matter when offering affected products into a specific market; they are not a prerequisite for improving the existing English site. Retain the earlier recommendation to distinguish commercial suppliers, legal manufacturers and production sites, and obtain product-specific advice before making market-readiness promises. The review does not grant regulatory clearance.

### Language: optimize the English site that already exists

Use available Search Console countries/queries, past enquiries, sales relationships and the team's support capability to decide whether a second language is worthwhile. No extra language is needed for this preservation release. If translations are added, use separate localized URLs, equivalent translated content and reciprocal `hreflang`; retain the existing English URLs. [Google multilingual guidance](https://developers.google.com/search/docs/specialty/international/managing-multi-regional-sites).

### `100/xx` references: preserve current identifiers

There is insufficient evidence to declare these references universally generic. That is not a reason to renumber existing records. Keep current identifiers and source references. A future taxonomy can add a separate stable internal key and reviewed mappings alongside the existing records. Review rights and technical equivalence before reusing new catalogue material or declaring two patterns identical.

## SEO priorities without replacing content

1. **Protect existing discovery.** Inventory published URLs and preserve working routes. Keep the existing non-www HTTPS canonical preference; align internal links and sitemap entries with it. Check robots/noindex behavior on actual hosting.
2. **Improve templates.** Make titles and descriptions useful and specific to existing page content, maintain a clear heading hierarchy, improve breadcrumbs and internal links, and use accurate structured data without fabricated prices, ratings or manufacturer claims.
3. **Improve the experience.** Preserve original media while generating optimized derivatives, reserve image dimensions, reduce unnecessary scripts, keep important copy rendered in HTML, improve mobile navigation and form accessibility, and measure performance before and after.
4. **Protect conversion.** Verify that enquiries save once and reach the working inbox. Changes to required fields should be justified by buyer needs and checked for abandonment.
5. **Add proven content later.** Publish useful regional/category guides, sourcing explanations and eventually verified catalogue pages as additions. Measure qualified enquiries as well as search clicks.

The earlier review raised questions about broad guarantees, inconsistent MOQs and certification language. Keep the source records intact and create a proposed correction list with the current text, evidence, proposed wording and affected pages. Missing evidence in this review is not by itself proof that an owner's business claim is false. Structured data must still accurately describe the visible page; preserving records does not require retaining misleading markup.

## Acceptance criteria for a future preservation release

- A private, restorable baseline includes current files, original downloads, uploads, schema/configuration and data. No confidential exports enter GitHub.
- Staging uses the existing schema and content as its baseline, with appropriate protection for buyer data. No fresh-install SQL or preview seed is imported over it.
- Existing product IDs, references, slugs, record values, asset hashes and published URL availability are compared before and after; expected presentation/metadata differences are documented. Database counts alone are insufficient.
- A reversible staging edit to products, FAQs, services, lab tests and blog content renders in the correct public location. Tests use the actual publishing rules.
- Forms, admin access, uploads and notification delivery work with the existing configuration. New incoming enquiries are preserved through cutover and rollback.
- The final file package contains only the intended reviewed changes. No destructive synchronization or replacement production database is part of deployment.

The implementation can be corrected locally from the repository baseline, but certifying parity with the live website requires its current private files/data and staging configuration. This does not block completing the review or correcting known draft regressions.

## Review limits

The original 14 Claude Markdown documents, `REVIEW-BRIEF.md`, `CONTEXT.md`, source catalogues and agreements were not available. This is a reassessment of the supplied excerpt, accessible repository, live-site checks and the actual draft changes. It is not a claim to have reviewed those missing documents, and it does not promise search rankings or every international customer.
