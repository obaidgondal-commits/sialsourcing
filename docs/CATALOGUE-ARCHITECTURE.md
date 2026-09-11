# Catalogue architecture and expansion sequence

> **Updated owner requirement — 11 September 2026:** preserve existing data, content, downloads, URLs and CMS behavior while improving the already-live English website. The current implementation draft and its existing ZIP do not yet meet that requirement. Follow [the revised preservation review](PRESERVATION-REVIEW.md) before deployment.

This is a proposed next stage, not a description of functionality already implemented. The original Claude specification and source catalogues are still needed for reconciliation.

## Two connected workstreams

1. **Public acquisition website:** fast server-rendered category and regional guides, accurate product information, clear requests for quotation, preserved URLs and measured qualified enquiries. The draft explores that foundation on PHP/MySQL-compatible hosting but requires preservation corrections before deployment.
2. **Private sourcing operations:** verified supplier capabilities, document evidence, commercial information, buyer requests and quotation workflow. Start with a small dental pilot while retaining the wider product business. Do not expose this dataset through the public site or commit its records to GitHub.

## Identity and taxonomy

Preserve existing identifiers, references and URLs. If needed, add a stable internal identifier alongside them that survives display-name, URL and supplier changes; do not replace existing IDs or migrate records as part of the website upgrade. Model industry → product family → pattern/design → variant. A variant is a meaningful difference in dimensions, material, finish, construction or packaging, not an excuse to create a near-identical search page.

Product families share identity and editorial fields but have different specification groups:

| Industry | Examples of specification fields |
| --- | --- |
| Dental/surgical instruments | Pattern, intended use, working-end geometry, size, steel, finish, reusable/single-use status, traceability |
| Cutlery | Steel grade, dimensions, mass, finish, edge/hardness where relevant, handle construction, food-contact evidence |
| Sports goods | Size, construction, materials, performance test method, artwork and packing |
| Apparel | Fibre content, fabric weight, measurement chart, grading, seams, trims, decoration and care |
| Home textiles | Weave, dimensions, finished mass, colour, shrinkage, washing protocol, labels and assortment |

Do not apply an instrument-only model to every supplier or product. Do not assume the reported supplier-panel count identifies the number of dental factories.

## Proposed records

- **ProductFamily:** stable ID, controlled name, category, buying intent, editorial status and canonical public URL.
- **ProductVariant:** family ID, structured dimensions/material/process fields, units, tolerances and approved drawing/photo references.
- **Supplier:** private entity record, industry, facility relationships, internal owner and access controls.
- **ManufacturingSite:** address, legal entity, processes and observed capabilities. Distinct from commercial intermediary and legal manufacturer.
- **CapabilityEvidence:** supplier/site, product family or variant, supported process/material, evidence document, reviewer, observation date, expiry/recheck date and confidence status.
- **ComplianceEvidence:** issuer, certificate/report number, subject legal entity, site/product scope, market, validity, document hash and reviewer. A document is not universal product authorization.
- **SourceDocument:** owner, permitted uses, version, page, language, original file hash, extraction method and rights status.
- **SupplierReference:** source document + exact catalogue identifier → candidate canonical pattern/variant, with mapping reason and review status. Preserve uncertain mappings; never silently equate similar codes.
- **RFQ / RFQItem:** buyer brief, destination, intended use, quantities, references, due date, required evidence and internal status.
- **Quote / QuoteItem:** specification version, supplier selection, cost/terms snapshot, validity and approval history. Do not present inferred prices as accepted quotes.

Record quality as observations tied to orders, samples, tests and dates. Avoid a single unsupported permanent “high quality” supplier label.

## Publication gate

A public product page needs reviewed original copy, rights-cleared imagery, a defensible identity/specification and useful procurement information. Keep unreviewed OCR, internal factory names, prices, agreements and confidential evidence private. Publish commercial numbers only with an explicit scope and maintenance owner. A missing price is valid for a quote-led product; do not invent zero-price offers, ratings or availability.

Use one canonical page where variants do not merit distinct buyer information. Design pagination and filter URL rules before exposing them. Keep search/filter combinations out of the index unless they serve a distinct, maintained buying purpose. When languages are added, use fully translated equivalent pages with reciprocal hreflang and local editorial review.

## Practical pilot

1. Obtain the original 14 documents and representative source catalogues, including rights and manufacturer agreements.
2. Choose a small representative dental family set with the sales/technical team. Validate the taxonomy using real catalogue examples and disagreements.
3. Interview representative factories and record evidence-backed capabilities. Resolve regulatory disclosure arrangements for target markets.
4. Trial a real internal RFQ from brief → required capabilities → shortlist → evidence review → quotation. Measure the work, including unanswered fields.
5. Only then expand extraction and publication. OCR is an input aid; human review decides identity, equivalence, claims and release.

## Operating controls

Keep the capability system separate from public publishing, use role-based access and retain an audit trail. Back up records and original evidence privately. Establish retention and export rules for buyer data. Credentials belong in server configuration outside the repository. Private supplier data belongs in protected storage, even if the application repository is made private later.

Measure qualified enquiries, time to a usable shortlist, sampling conversion, accepted quotes and evidence completeness. Website rankings and instant RFQ response are outcomes to measure, not promises this software alone can guarantee.
