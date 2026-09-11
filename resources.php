<?php
$pageTitle='Buyer Resources: Sourcing Briefs, Tech Packs & QC Templates';
$pageDesc='Download SialSourcing buyer templates: sourcing brief, apparel tech pack, inspection request, specimen QC report and US import planning checklist.';
$canonicalPath='/resources';
require __DIR__.'/includes/header.php';
$docs=[
 ['Sourcing brief / RFQ','Prepare your product, quantity, destination, materials, branding and timeline before discussing a quotation.','RFQ-Sourcing-Brief-Template.pdf','2 pages · Printable template'],
 ['Apparel & uniform tech pack','Record fabric, measurements, tolerances, trims, decoration and packaging in a consistent specification.','Apparel-Uniform-Tech-Pack-Template.pdf','2 pages · Printable template'],
 ['QC inspection request','Describe your order, inspection stage, approved reference and the product-specific checks you need.','QC-Inspection-Request-Form.pdf','2 pages · Printable template'],
 ['Specimen inspection report','A blank illustrative format for recording findings, evidence and decisions. It does not represent an actual inspection.','Sample-QC-Inspection-Report.pdf','2 pages · Specimen only'],
 ['US import planning checklist','Prepare questions and documents with your customs broker. Requirements depend on the goods, shipment and applicable rules.','US-Import-Documents-Checklist.pdf','2 pages · Planning reference'],
 ['SialSourcing company profile','An introduction to our sourcing approach, product categories and the steps to scope a project.','SialSourcing-Company-Profile.pdf','1 page · Company overview'],
];
?>
<section class="guide-hero"><p class="eyebrow">The details make the difference</p><h1>Tools for a better<br>sourcing brief.</h1><p>Practical templates to help you define requirements, compare samples and prepare the next conversation. Free to download, with no email required.</p></section>
<section class="editorial-section"><div class="category-grid"><?php foreach($docs as [$title,$description,$file,$meta]): ?><article class="category-card"><p class="eyebrow"><?= e($meta) ?></p><h2 style="font-size:1.5rem;margin-bottom:18px"><?= e($title) ?></h2><p><?= e($description) ?></p><a class="text-link" style="margin-top:auto" href="/assets/docs/<?= e($file) ?>" download aria-label="Download <?= e($title) ?> PDF">Download PDF <?= icon('arrow-right',18) ?></a></article><?php endforeach; ?></div><p class="field-note">Updated September 2026. Templates help structure a brief; the agreed contract, product specification and applicable requirements govern each order.</p></section>
<section class="resource-band"><div><p class="eyebrow">Put your brief to work</p><h2>Ready to discuss the details?</h2><p>Send your requirements or request a separately scoped inspection for an order with a Pakistani manufacturer.</p></div><div class="button-row"><a class="btn btn-dark" href="/contact">Send a sourcing brief</a><a class="text-link" href="/qc-inspection-request">Request an inspection</a></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
