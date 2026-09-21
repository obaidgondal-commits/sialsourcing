<?php
/** Original editorial drafts. Publication requires a real named reviewer. */
return [
    'surgical-instrument-sourcing-evidence' => [
        'title' => 'Sourcing Surgical Instruments: Specifications and Evidence',
        'summary' => 'Build a clear purchasing brief for surgical and dental instruments, then distinguish supplier statements, material-test reports, quality-system certificates and regulatory records before deciding what still needs verification.',
        'industry' => 'Surgical & Dental',
        'icon' => 'scissors',
        'keywords' => ['surgical', 'dental', 'instruments', 'specifications', 'materials', 'quality', 'documents', 'test reports'],
        'status' => 'draft',
        'reviewer' => '',
        'reviewed_on' => '',
        'prepared_on' => '2026-09-21',
        'sections' => [
            [
                'title' => 'Start with one written specification',
                'paragraphs' => [
                    'A familiar instrument name can leave important purchasing details unresolved. Before comparing prices, prepare a specification that lets each supplier quote the same item. This is a proposed procurement checklist; the intended clinical use and any clinical suitability assessment belong with appropriately qualified people.',
                    'Give the brief a revision number. Attach reference photographs or drawings where available, and identify which information is confirmed and which still needs agreement. A photograph helps identify an item but does not establish its material or performance.',
                ],
                'bullets' => [
                    'Record the instrument reference, pattern, dimensions, units and agreed tolerances.',
                    'Specify the requested material, finish, markings, packaging and delivery condition.',
                    'List quantities by reference and identify the destination market.',
                ],
            ],
            [
                'title' => 'Separate the different kinds of evidence',
                'paragraphs' => [
                    'ISO 13485 addresses quality management systems for medical devices. A statement about a quality system should therefore be considered separately from evidence about an individual instrument or shipment.',
                    'FDA also distinguishes establishment registration and device listing from approval, clearance or authorization. It states that it does not issue device-facility registration certificates. Avoid treating a registration number or certificate image as a complete answer to a product-status question.',
                    'For your proposed document review, record the issuing organization, named legal entity, site, scope and relevant dates. Ask the appropriate regulatory specialist which destination-specific evidence applies to the exact device before relying on a general assurance.',
                ],
                'sources' => ['iso13485', 'fda-registration'],
            ],
            [
                'title' => 'Connect test reports to the supplied item',
                'paragraphs' => [
                    'SIMAP lists material-analysis, hardness, oxidation, passivation and coating-thickness services at SIMTEL. This provides useful examples of testing to discuss; it does not demonstrate that any catalogue item has undergone those tests.',
                    'Agree the relevant test plan with qualified technical personnel. When reviewing a report, ask how the tested sample relates to the quoted product and, later, the delivered batch. Keep the original report with the purchase record.',
                ],
                'bullets' => [
                    'Check the sample identifier, report date and named laboratory.',
                    'Record the method, reported results and agreed acceptance criteria.',
                    'Resolve missing traceability or unexplained differences before acceptance.',
                ],
                'sources' => ['simtel'],
            ],
            [
                'title' => 'Record decisions before placing the order',
                'paragraphs' => [
                    'Keep a short decision log: what was requested, what evidence arrived, who reviewed it and what remains open. Record the approved specification and sample reference in the order, together with the agreed inspection and discrepancy process.',
                    'An unanswered question should stay visible. Do not replace it with an assumed material grade, test result, certification or delivery promise simply to complete the comparison sheet.',
                ],
            ],
        ],
        'sources' => [
            'iso13485' => [
                'title' => 'ISO 13485:2016 — Medical devices quality management systems',
                'url' => 'https://www.iso.org/standard/59752.html',
                'publisher' => 'International Organization for Standardization',
            ],
            'fda-registration' => [
                'title' => 'Important Reminders about Registration and Listing',
                'url' => 'https://www.fda.gov/medical-devices/device-registration-and-listing/important-reminders-about-registration-and-listing',
                'publisher' => 'U.S. Food and Drug Administration',
            ],
            'simtel' => [
                'title' => 'SIMTEL Services',
                'url' => 'https://simap.org.pk/simtel-services/',
                'publisher' => 'Surgical Instruments Manufacturers Association of Pakistan',
            ],
        ],
        'next_step' => 'Choose the instrument references you want to discuss and send a specification brief with quantities, destination and the evidence you need reviewed.',
        'related' => [
            ['path' => '/dental-instruments/extraction', 'label' => 'Explore extraction instrument families'],
            ['path' => '/contact', 'label' => 'Discuss your sourcing brief'],
        ],
    ],
    'football-sourcing-test-evidence' => [
        'title' => 'Buying Footballs: Specifications, Samples and Test Evidence',
        'summary' => 'Turn a football enquiry into a comparable specification, understand the scope of FIFA quality testing, and connect sample approval and production checks to the exact model you plan to buy.',
        'industry' => 'Sports Goods',
        'icon' => 'soccer-ball',
        'keywords' => ['football', 'soccer balls', 'FIFA', 'samples', 'quality marks', 'testing', 'branding', 'inspection'],
        'status' => 'draft',
        'reviewer' => '',
        'reviewed_on' => '',
        'prepared_on' => '2026-09-21',
        'sections' => [
            [
                'title' => 'Define the job before requesting a price',
                'paragraphs' => [
                    'Start with the intended use: a promotional giveaway, regular training or a particular competition. These enquiries need different discussions. If an organizer specifies a ball standard or mark, include the actual requirement in the brief rather than assuming that a general description such as match ball is sufficient.',
                    'Use the following proposed checklist to make quotations comparable. Ask suppliers to identify assumptions and alternatives explicitly, including any change that affects the agreed sample.',
                ],
                'bullets' => [
                    'State the requested size, construction, cover and bladder specification.',
                    'Supply artwork, colour references, marking positions and packaging requirements.',
                    'Record quantities, destination, required delivery date and any required quality mark.',
                ],
            ],
            [
                'title' => 'Understand what FIFA testing covers',
                'paragraphs' => [
                    'FIFA describes seven testing areas for its football quality programme: weight, circumference, roundness, bounce, water absorption, pressure loss, and retention of shape and size. It describes more demanding testing conditions for the FIFA Quality Pro mark.',
                    'FIFA explains that quality marks are awarded to balls that pass the applicable testing process. Being made in a particular city is therefore not evidence that a model qualifies for a mark.',
                    'For a proposed evidence check, identify the exact model offered and compare its details with the relevant current FIFA information. Ask the supplier to explain any difference between the quotation, sample markings and supporting record. Keep that comparison with the order.',
                ],
                'sources' => ['fifa-footballs'],
            ],
            [
                'title' => 'Make sample approval specific',
                'paragraphs' => [
                    'Give each submitted sample an identifier and record the specification revision it represents. Separate appearance approval from performance evidence: agreeing a colour or logo position does not record a test result.',
                    'A useful sample-review sheet should leave room for photographs, measured observations, reviewer comments and unresolved questions. Agree who will perform any required testing and how reports will identify the sample. Avoid selecting acceptance limits from an unrelated model or an undated marketing sheet.',
                ],
                'bullets' => [
                    'Confirm artwork, colours, construction and packaging against the brief.',
                    'Record which checks were performed and which remain outstanding.',
                    'Keep an agreed reference sample and document later changes.',
                ],
            ],
            [
                'title' => 'Carry the agreement into production and delivery',
                'paragraphs' => [
                    'Before ordering, agree how production will be checked against the approved specification and sample. Record the inspection scope, report format, treatment of discrepancies and responsibility for approving changes. Any sampling plan should be agreed for the order rather than assumed.',
                    'Keep the quotation, artwork revision, sample record, test evidence and shipment inspection together. At receipt, record quantities and visible discrepancies against those documents. This creates a usable discussion record if the delivered goods differ from what both parties agreed.',
                ],
            ],
        ],
        'sources' => [
            'fifa-footballs' => [
                'title' => 'FIFA Quality Programme for Footballs',
                'url' => 'https://football-technology.fifa.com/innovation/standards/footballs/fifa-quality-programme-for-footballs',
                'publisher' => 'FIFA',
            ],
        ],
        'next_step' => 'Prepare a football brief with intended use, quantity, artwork, destination and any required test or quality-mark evidence before requesting comparable quotations.',
        'related' => [
            ['path' => '/custom-soccer-balls', 'label' => 'Explore custom soccer ball sourcing'],
            ['path' => '/resources', 'label' => 'Open buyer resources'],
        ],
    ],
    'sialkot-industry-buyer-map' => [
        'title' => 'A Buyer’s Map of Sialkot Industries',
        'summary' => 'Understand the range of industries associated with Sialkot, then build a category-specific enquiry and compare supplier capabilities using evidence tied to your product, order and destination.',
        'industry' => 'Sialkot Industries',
        'icon' => 'briefcase',
        'keywords' => ['Sialkot', 'industries', 'leather', 'textiles', 'sportswear', 'gloves', 'sports goods', 'surgical', 'RFQ'],
        'status' => 'draft',
        'reviewer' => '',
        'reviewed_on' => '',
        'prepared_on' => '2026-09-21',
        'sections' => [
            [
                'title' => 'Use the industry map as a starting point',
                'paragraphs' => [
                    'The Sialkot Chamber of Commerce and Industry lists sports goods, surgical instruments, leather products, gloves, textile items, sportswear and uniforms among the city’s export sectors. This establishes a useful map of industries to investigate, without establishing the capabilities of any individual supplier.',
                    'Begin with the product you need and the information required to define it. The practical questions below are a proposed buyer checklist. They are intended to help structure an enquiry, not to rank factories or imply that every business in the city offers the same materials, processes or documentation.',
                ],
                'sources' => ['scci-profile'],
            ],
            [
                'title' => 'Write a different brief for each category',
                'paragraphs' => [
                    'A single request for good quality leaves too much open to interpretation. Choose the details that define your product and ask each shortlisted supplier to quote against the same revision. Where a requirement is undecided, ask for clearly identified options.',
                ],
                'bullets' => [
                    'Surgical and dental instruments: references, dimensions, requested material, finish, packaging and evidence requirements.',
                    'Balls and other sports goods: intended use, construction, size, artwork and applicable performance requirements.',
                    'Textiles, sportswear and uniforms: fabric composition, fabric weight, measurements, size breakdown, stitching, decoration and care labelling.',
                    'Leather goods and gloves: material description, thickness, finish, lining, hardware, sizing and destination-specific testing questions.',
                ],
            ],
            [
                'title' => 'Compare the proposed supply arrangement',
                'paragraphs' => [
                    'Ask which organization will contract with you, which site will make the goods and which activities are subcontracted. Request evidence relevant to that arrangement rather than treating a website gallery or industry membership as proof of manufacturing scope.',
                    'Build a comparison sheet around the same product specification, quantities and delivery basis. Keep sampling costs, tooling, packaging and inspection charges visible. If a quotation excludes something, record the exclusion before comparing its total with another offer.',
                ],
                'bullets' => [
                    'Identify the proposed manufacturing site and contact responsible for the order.',
                    'Record sample timing, quoted production timing and conditions attached to each.',
                    'Agree how changes, inspections and discrepancies will be documented.',
                ],
            ],
            [
                'title' => 'Keep evidence attached to the decision',
                'paragraphs' => [
                    'Maintain a dated record of the brief, supplier responses, sample observations and outstanding questions. When a certificate or report is supplied, note the named organization, scope, dates and product or sample identification. Seek appropriate technical advice when the evidence requires specialist interpretation.',
                    'Treat minimum quantities, lead times and available processes as matters to confirm for the proposed order. An industry overview is useful context; your purchasing decision still needs an agreed specification, a documented supplier assessment and a clear acceptance process.',
                ],
            ],
        ],
        'sources' => [
            'scci-profile' => [
                'title' => 'Sialkot Chamber of Commerce and Industry — Profile',
                'url' => 'https://scci.com.pk/profile/',
                'publisher' => 'Sialkot Chamber of Commerce and Industry',
            ],
        ],
        'next_step' => 'Choose a product category and prepare a brief that separates confirmed requirements, proposed alternatives and the questions a supplier must answer.',
        'related' => [
            ['path' => '/products', 'label' => 'Explore existing product categories'],
            ['path' => '/resources', 'label' => 'Open sourcing templates and resources'],
        ],
    ],
];
