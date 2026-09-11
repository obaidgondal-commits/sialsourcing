<?php
$pageTitle='Meet the SialSourcing Team';
$pageDesc='Connect with the people coordinating your sourcing brief, supplier communication, samples, quality checks and shipment planning.';
$canonicalPath='/team';
require __DIR__.'/includes/header.php';
$team=db()->query('SELECT * FROM team_members WHERE is_active=1 ORDER BY sort_order')->fetchAll();
?>
<section class="guide-hero"><p class="eyebrow">The people behind your project</p><h1>Good sourcing starts<br>with clear communication.</h1><p>Discuss your requirements with the SialSourcing team. Establish the right points of contact for your product, quality plan and shipment.</p></section>
<section class="editorial-section">
<?php if($team): ?><div class="category-grid"><?php foreach($team as $member): ?><article class="category-card"><?php if(!empty($member['image'])): ?><img src="<?= e($member['image']) ?>" alt="<?= e($member['name']) ?>" width="120" height="120" loading="lazy" style="border-radius:50%;object-fit:cover;aspect-ratio:1;margin-bottom:25px"><?php endif; ?><h2 style="font-size:1.6rem;margin-bottom:12px"><?= e($member['name']) ?></h2><p class="eyebrow"><?= e($member['role'] ?? $member['position'] ?? '') ?></p><p><?= e($member['bio'] ?? '') ?></p></article><?php endforeach; ?></div>
<?php else: ?><div class="section-heading"><div><p class="eyebrow">Start a conversation</p><h2>Bring the right questions<br>to the right people.</h2></div><p>Tell us about your product and destination so we can discuss the coordination your project needs.</p></div><?php endif; ?>
<div class="category-grid"><article class="category-card"><p class="eyebrow">Product & supplier coordination</p><h3>Agree the brief.</h3><p>Clarify specifications, development questions and the information needed to assess manufacturing suitability.</p></article><article class="category-card"><p class="eyebrow">Quality coordination</p><h3>Define the checks.</h3><p>Agree the approved reference, relevant test evidence and inspection scope for your order.</p></article><article class="category-card"><p class="eyebrow">Order & shipment coordination</p><h3>Keep the details together.</h3><p>Confirm progress updates, documentation responsibilities and delivery arrangements in the agreed scope.</p></article></div></section>
<section class="closing-cta"><p class="eyebrow">Tell us about your project</p><h2>Let's start with your brief.</h2><p>Share the product, destination and timing. We will confirm the next steps with you.</p><a class="btn btn-gold" href="/contact">Contact the team</a></section>
<?php require __DIR__.'/includes/footer.php'; ?>
