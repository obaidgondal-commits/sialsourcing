# Expansion review — 11 September 2026

> **Updated owner requirement — 11 September 2026:** preserve existing data, content, downloads, URLs and CMS behavior while improving the already-live English website. The current implementation draft and its existing ZIP do not yet meet that requirement. Follow [the revised preservation review](PRESERVATION-REVIEW.md) before deployment.

## Scope and evidence

This review covers the Claude excerpt supplied by the owner, the accessible public website, and current primary guidance. The original 14-document package, `REVIEW-BRIEF.md`, `CONTEXT.md`, manufacturer agreements and source catalogues were not available for this review. It is therefore a provisional second opinion, not a claim to have reviewed those files.

The reported 120-manufacturer panel is an owner-supplied business fact pending documentary review. Do not infer that all 120 are dental manufacturers, that they all support every listed product, or that every supplier holds the same certificates. Record industry and capability separately for each supplier.

The new public regional pages are buyer guides. They explain how to prepare a sourcing brief and link to product categories; they do not publish invented supplier records, stock, prices or certification evidence. Wazirabad is treated as the intended location behind “Zirabad”; the owner should confirm that interpretation before release.

## Decisions on the four contested questions

### 1. Keep P0 useful and bounded

Improve the already-live public site independently of full catalogue ingestion. P0 starts with a private baseline and staging copy, then preserves existing data, copy, downloads, URLs and CMS behavior while improving design, technical SEO, accessibility, RFQs and deployment reliability. The current draft needs the preservation corrections recorded in PRESERVATION-REVIEW.md before release.

Advance dental taxonomy and the private capability matrix as a separate pilot. Publication requires enough evidence to support the claims on each product page. It does not require interviewing all 120 suppliers before any public-site improvement can ship. Do not make OCR the critical path: first settle the data model, rights, proof requirements and staff workflow with a small representative sample.

Retain sportswear, balls, leather and cutlery as existing lines. Add substantive Sialkot, Wazirabad and Faisalabad guides, with textile enquiries described as requiring capability confirmation. The dental pilot is a focused operational test within the wider sourcing business.

### 2. Protect commercial information with a regulatory disclosure exception

Keep private prices, staff assessments, contracts and the supplier capability matrix access-controlled. Model the commercial supplier, legal manufacturer, production site, product family and local importer/authorization holder as distinct entities. Track evidence scope, issuer, validity, source and review date.

Absolute supplier secrecy is an unsuitable promise for regulated devices. ANVISA says foreign businesses need a legally constituted Brazilian partner for marketing authorization. Its August 2026 notice explains identification codes for foreign legal manufacturers and manufacturing units used in imported-device regularization; class I/II manufacturer registration now uses Solicita. [ANVISA medical-device overview](https://www.gov.br/anvisa/en/regulation-of-products/medical-devices), [14 August 2026 manufacturer notice](https://www.gov.br/anvisa/pt-br/assuntos/noticias-anvisa/2026/cadastro-de-fabricantes-internacionais-de-dispositivos-medicos-risco-i-e-ii-agora-e-eletronico).

These sources support a required-disclosure exception, not unrestricted access to every internal commercial record. A Brazilian regulatory specialist must confirm the route for the specific device, the holder arrangement, label/dossier requirements and public visibility of manufacturer data before Brazil-ready offers are published. An NDA cannot override legally required disclosure. This review is not legal clearance.

### 3. Improve the existing English site; choose later languages deliberately

English is already live. Preserve its current URLs and content; do not move it to an `/en/` path just to prepare for translation. Check existing Search Console, analytics and enquiry history before concluding that demand data is absent. Choose any additional language using that evidence, the sales team's ability to support enquiries, relationships and target sectors. A language expansion is not part of the preservation release.

Use separate locale URLs, full translations, a visible language switcher and reciprocal `hreflang` for equivalent pages. Avoid forced IP-based redirects and translated navigation around untranslated main content. Portuguese is not automatically the first language simply because ANVISA is discussed. [Google multilingual guidance](https://developers.google.com/search/docs/specialty/international/managing-multi-regional-sites).

### 4. Do not declare `100/xx` references universally generic

Manufacturer catalogues demonstrate usage, not freedom to reuse all identifiers, images or text. Novacore uses the exact `100/17`-style references; HuFriedyGroup distinguishes pattern numbers from its own `F150` ordering reference. That is evidence to keep standard-pattern identity separate from supplier catalogue references. [Novacore extracting forceps](https://novacoreindustry.com/products.php?id=21), [HuFriedyGroup surgical catalogue](https://catalog.hu-friedy.com/surgical/files/basic-html/page67.html).

Preserve all existing identifiers and public URLs. A future taxonomy may add a stable internal SialSourcing identifier alongside existing records, without renumbering them. Keep original references in a source-aware crosswalk with supplier, document/version, page, review status and rights information. Verify pattern, dimensions and working geometry before declaring equivalence. Common-looking numbering is not proof of a universal standard or an IP clearance conclusion. The actual catalogues, agreements and applicable rights need review.

## SEO and content corrections

- **Original buyer value:** publish product pages when they contain meaningful verified specifications, images and procurement information. Do not create a page for every supplier/size/market combination simply to capture keywords. Google's scaled-content policy applies to low-value ranking manipulation regardless of how content is generated. [Google spam policies](https://developers.google.com/search/docs/essentials/spam-policies).
- **Migration:** retain existing paths where possible, inventory linked/indexed URLs and use relevant permanent redirects for replacements. Update canonicals, internal links and sitemaps; do not redirect unrelated missing pages to the homepage. [Google site-move guidance](https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes).
- **Structured data:** use truthful Organization, BreadcrumbList and appropriate page types. Product snippets require a name and a qualifying offer, review or aggregate rating. Do not invent prices, zero-price offers or reviews to satisfy a validator. Quote-only product pages can be useful without rich-product eligibility. [Product snippet requirements](https://developers.google.com/search/docs/appearance/structured-data/product-snippet), [structured-data policies](https://developers.google.com/search/docs/appearance/structured-data/sd-policies).
- **Current FAQ correction:** Google stopped showing FAQ rich results on 7 May 2026 and removed the documentation in June. Visible FAQs can still help buyers; do not promise a FAQ rich-result advantage. Google also says `llms.txt` is not needed for Search visibility. [Google 2026 changelog](https://developers.google.com/search/updates).
- **Controlled catalogue filters:** keep useful canonical family pages discoverable through HTML links. Plan faceted URLs explicitly instead of exposing unlimited parameter combinations. [Google faceted-navigation guidance](https://developers.google.com/crawling/docs/faceted-navigation).
- **Performance:** target good field Core Web Vitals, accessible mobile forms, correctly sized images and stable layouts. Do not equate a Lighthouse score with guaranteed rankings. This review did not measure field performance. [Google Core Web Vitals](https://developers.google.com/search/docs/appearance/core-web-vitals).

## Public-site observations requiring verification

The homepage and category pages contained broad assurances about payments, legal protection and compliance. Verify the supporting corporate entities, service terms and evidence before retaining such promises. The homepage surgical MOQ differed from the surgical category; use one maintained source for commercial guidance. Surgical and cutlery pages contained photography placeholders, while About counters appeared as zero in retrieved text. These observations came from public-page retrieval and may reflect cached copies.

The surgical page used “FDA 510(k) registered manufacturers.” Correct the distinction among establishment registration, device listing and clearance where applicable. FDA says registration/listing does not denote approval, clearance or authorization and does not issue device registration certificates. [FDA registration guidance](https://www.fda.gov/medical-devices/device-registration-and-listing/important-reminders-about-registration-and-listing).

The public sitemap returned a bot-verification page to the research browser; some endpoints failed retrieval. These are checks to perform with direct HTTP requests, Hostinger/CDN configuration and Search Console, not confirmed proof that Google cannot index the site. No Search Console, analytics or production hosting access was available for this research.

## Regional source basis

- **Sialkot:** the Chamber's profile identifies sports goods, instruments, sportswear and leather among the city's export industries. The public guide avoids uncited market-share claims. [SCCI profile](https://scci.com.pk/profile/).
- **Wazirabad:** TDAP identifies the cutlery cluster in its exhibitor directory. The public guide focuses on product specification and inspection rather than claiming SialSourcing has a particular factory relationship. [TDAP directory](https://hems.tdap.gov.pk/wp-content/uploads/2023/03/EHCS-EHIBITORS-DIRECTORY-11-2-23.pdf).
- **Faisalabad:** TDAP's textile exhibition directory includes city businesses across home textiles, apparel and textile inputs. This supports regional context; it does not prove a SialSourcing supplier partnership. [TDAP TEXPO directory](https://texpo.tdap.gov.pk/exhibitors-directory/).

## Success measures

Measure qualified enquiries by category and destination, completed RFQs, response time, accepted quotations and repeat customers alongside organic impressions/clicks. Review which queries attract relevant buyers and which content answers their procurement questions. No technical configuration can guarantee rankings or capture every international buyer.

## Draft editorial changes requiring revision

The draft About, Solutions and Lab/QC pages use static content. Legacy `solutions` and `lab_tests` records remain unchanged, but admin edits no longer appear on the corresponding public pages. The homepage also stops rendering several CMS collections, and all six PDFs were replaced. These changes conflict with the clarified preservation requirement. Restore the original content and CMS connections for the preservation release; keep suggested wording corrections separate. See PRESERVATION-REVIEW.md for the full audit.
