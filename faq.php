<?php
$pageTitle='Pakistan Sourcing Questions: Orders, Samples & Quality';
$pageDesc='Answers to common questions about sourcing from Pakistan, product samples, order quantities, quality checks and shipping.';
$canonicalPath='/faq';
require __DIR__.'/includes/header.php';
$faqs=db()->query('SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order,id')->fetchAll();
$cats=array_values(array_unique(array_column($faqs,'category')));
?>
<section class="guide-hero"><p class="eyebrow">Before you source</p><h1>Your sourcing questions,<br>answered.</h1><p>Explore the practical details of working with a sourcing partner. Your quotation and agreed specification set the terms for a particular order.</p></section>
<section class="editorial-section guide-grid"><div class="guide-content faq-content"><?php foreach($cats as $i=>$category): ?><section id="category-<?= $i ?>"><h2><?= e($category) ?></h2><?php foreach($faqs as $f): if($f['category']!==$category) continue; ?><details><summary><?= e($f['question']) ?></summary><p><?= nl2br(e($f['answer'])) ?></p></details><?php endforeach; ?></section><?php endforeach; ?><?php if(!$faqs): ?><p>Share your product, destination and quantity with our team to discuss the sourcing process.</p><?php endif; ?></div><aside class="guide-sidebar"><h2>Find an answer</h2><ul><?php foreach($cats as $i=>$category): ?><li><a href="#category-<?= $i ?>"><?= e($category) ?></a></li><?php endforeach; ?></ul><p>Have a question about your specific product or market?</p><a class="btn btn-gold" href="/contact">Discuss your brief</a></aside></section>
<?php require __DIR__.'/includes/footer.php'; ?>
