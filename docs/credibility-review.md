# Existing website credibility review

Reviewed 21 September 2026. Scope: original public copy in `index.php`, `lab-qc.php`, and the sanitized `data/site-content.json` snapshot. **No existing public copy was changed.** This is an editorial evidence checklist for the owner before the knowledge base is published, not a determination that unsupported business claims are false or a regulatory approval of any product.

CMS references below identify table, row ID and field. Suggested wording is a draft; retain only service statements the owner can substantiate. Keep supplier records and sensitive evidence private.

## 1. Category badges conflate registration, conformity, testing and certification

**Correction plus owner evidence.** `index.php:292–299` describes “ISO, CE, FDA, REACH through accredited labs.” CMS `products` IDs 1–11, field `certifications`, mixes badges such as FDA Registered, WHO GMP, FIFA Quality Pro, SGS Tested and EU Food Contact Approved. `faqs` ID 5 describes manufacturers as certified under FIFA and REACH/RoHS. These are not interchangeable credentials, and a category badge does not prove a particular variant qualifies.

FDA establishment registration/listing does not establish device approval, clearance or authorization; FDA does not issue device-facility registration certificates. CE marking is the manufacturer's conformity declaration under applicable product rules, not general EU approval; not all products require it. FIFA marks relate to tested football models. Sources: [FDA registration](https://www.fda.gov/medical-devices/device-registration-and-listing/important-reminders-about-registration-and-listing), [European Commission CE marking](https://single-market-economy.ec.europa.eu/single-market/goods/ce-marking_en), [FIFA football programme](https://football-technology.fifa.com/innovation/standards/footballs/fifa-quality-programme-for-footballs).

**Evidence:** exact supplier/site, product/model scope, issuer, document identifier, expiry and applicable market. **Draft:** “Documentation is checked for the selected manufacturer, product and destination market. Available certificates and test reports are confirmed with your quotation.” Do not propagate legacy category badges into new SKU facts.

## 2. ISO 13485 is presented as a universal export certificate

**Factual overstatement.** `lab-qc.php:45` says ISO 13485 is “mandatory for export to US, EU, and AU markets”; CMS `blog_posts` ID 1 also makes broad legal-requirement claims. Quality-system obligations, certification and permission to market a device are different questions. FDA's QMSR incorporates ISO 13485:2016, effective 2 February 2026, but FDA explicitly does not require an ISO 13485 certificate. Sources: [FDA QMSR](https://www.fda.gov/medical-devices/postmarket-requirements-devices/quality-management-system-regulation-qmsr), [FDA FAQ, question 13](https://www.fda.gov/medical-devices/quality-management-system-regulation-qmsr/quality-management-system-regulation-frequently-asked-questions).

**Draft:** “ISO 13485 addresses medical-device quality management. Applicable quality-system and market-access requirements depend on the device and destination; an ISO certificate alone does not establish market eligibility.” Have the market-specific article reviewed against current device classifications before publishing it as guidance.

## 3. Test labels overstate their scope

**Technical correction needed.** CMS `lab_tests` ID 2 associates general leather/sports durability with ASTM D412; that method covers tensile properties of rubber and thermoplastic elastomers. ID 6 presents FIFA Quality Pro as the standard for general stitch/seam testing, although the FIFA mark requires the programme's football tests. ID 5 groups chemical testing with broad FDA compliance. Sources: [ASTM D412 scope](https://store.astm.org/standards/d412), [FIFA programme](https://football-technology.fifa.com/innovation/standards/footballs/fifa-quality-programme-for-footballs).

**Evidence:** material, specimen, method/revision, laboratory scope and report for each offered test. **Draft:** “Testing is selected for the product material, intended use and buyer specification. The quotation identifies the test method, laboratory and acceptance criteria.” Avoid a generic method badge as proof of full product compliance. The fallback test list in `lab-qc.php:80–90` also needs review, but it is not currently displayed because six CMS test records exist.

## 4. “100% Quality Inspected” is ambiguous beside sampling

**Clarification plus owner evidence.** `index.php:50–51,199,298` combines “100% Quality Inspected,” “Lab Test Everything” and statistical sampling. `lab-qc.php:24,51` calls AQL 2.5 an international standard for all products. Inspecting every order is different from inspecting every unit; an AQL value alone is not a complete sampling plan. [NIST explains acceptance sampling](https://www.itl.nist.gov/div898/handbook/pmc/section2/pmc21.htm) and [sampling-plan parameters](https://www.itl.nist.gov/div898/handbook/pmc/section2/pmc22.htm).

**Evidence:** order inspection coverage, lot size, sampling standard/revision, inspection level, defect classes, acceptance limits and release records. **Draft:** “Inspection scope and sampling criteria are agreed for each order. Reports state the quantity examined, findings and release decision.” Use “every order” only if records support it; do not imply zero defects.

## 5. Owned laboratory and inspection capability need business evidence

**Unverified, not disproved.** `index.php:234,290–299` claims an owned Sialkot facility, testing lab, inspectors, tensile/durability testing and internationally accredited laboratory partners. CMS `team_members` ID 3 repeats in-house laboratory and technical-verification claims.

**Evidence:** facility address and operating arrangement, staff roles, equipment/calibration records, actual methods, example reports and partner accreditation scopes. A nearby trade-association lab is not evidence of SialSourcing ownership or of tests performed for a given order. **Draft pending confirmation:** “QC and laboratory testing arrangements are confirmed in the order scope, including who performs each inspection or test.” Once verified, describe owned and contracted capabilities precisely.

## 6. Offices, experience and network counts need a dated evidence register

**Unverified, not disproved.** `index.php:38–47,232–234,256–277,380` claims 100+ vetted/audited manufacturers, 30+ countries served and three global offices, including Dallas headquarters and a Paris office. CMS `team_members` IDs 1–5 contain location, qualification, military-service and previous-company experience claims.

**Evidence:** current office/entity arrangements, team confirmation and supporting credentials, consent for named career affiliations, a defined factory-vetting standard and dated deduplicated supplier/customer-country counts. A representative's location should not automatically be described as a corporate office. **Draft:** “Meet the people responsible for your enquiry, sourcing and quality coordination.” Add confirmed locations and dated metrics individually; no replacement count has been established by this review.

## 7. Payment and import promises imply protections that need exact terms

**Unsubstantiated guarantees and scope ambiguity.** `index.php:168,200,227,262–267` promises protected money/full legal protection and suggests direct buyers have no legal standing. A bank account's country does not itself document escrow, reimbursement or a buyer's legal remedies. CMS `blog_posts` ID 4 promises sourcing without customs-detention risk and says the entire surgical network has current FDA Green List status, registration and ISO certification.

**Evidence:** named contracting entity, signed payment/release/refund terms, governing law and dispute venue; dated supplier-and-product checks against [FDA Import Alert 76-01](https://www.accessdata.fda.gov/cms_ia/importalert_224.html). No supplier's current status was verified in this review. **Draft:** “Your quotation identifies the contracting entity, payment account, release milestones and dispute terms. For US medical-device orders, current manufacturer and product requirements are checked before shipment; customs release cannot be guaranteed.” Remove universal claims about direct buyers' legal standing.

## 8. Sector statistics and brand associations need dated primary support

**Unverified statistics, not established factual corrections.** CMS `products` IDs 1, 2 and 6 cite 150 million instruments, 50 million footballs, a 70% hand-stitched-football share and named global brands. `blog_posts` ID 1 claims US$1.8 billion Pakistani surgical exports in 2024 and a single company's 90% professional-hockey-stick share. The text does not supply enough dated source detail to validate the geography, product definition or denominator.

**Evidence:** exact primary publication/table, reference year, country versus city, product/HS-code coverage and whether a brand relationship is historical or current. Do not treat projections as realized exports. Even the [Sialkot Chamber profile](https://scci.com.pk/profile/) contains differing headline/body export totals without a clear common reference year, so it supports sector identification better than a precise current statistic. **Draft:** “Sialkot is an established manufacturing centre for surgical instruments, sports goods, leather products and other export industries.” Add numerical claims only with a dated source beside them; name client brands only where the relevant relationship is documented and appropriate to disclose.

## Publication handling

Preserve the original website until the owner approves concrete copy changes. The new knowledge base should use independently sourced explanations and must not cite these legacy marketing statements as evidence. Record each retained claim's scope, source, owner, check date and next review date. Private catalogue review status and image approval do not validate any of the business or regulatory claims above.
