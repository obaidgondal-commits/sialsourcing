<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/icons.php';
$siteName = setting('site_name', 'SialSourcing');
$pageTitle = $pageTitle ?? 'Pakistan Sourcing & Supply Chain Partner';
if (stripos($pageTitle, $siteName) === false) $pageTitle .= ' | ' . $siteName;
$pageDesc = $pageDesc ?? 'Source products from Pakistan with SialSourcing. Supplier matching, sample coordination, quality checks and shipment planning.';
$canonicalPath = $canonicalPath ?? strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$canonicalOrigin = defined('CANONICAL_URL') ? CANONICAL_URL : 'https://sialsourcing.com';
$canonicalUrl = $canonicalOrigin . '/' . ltrim($canonicalPath, '/');
$ogImage = $ogImage ?? $canonicalOrigin . '/og-image.jpg';
if (str_starts_with($ogImage, '/')) $ogImage = $canonicalOrigin . $ogImage;
$ogType = $ogType ?? 'website';
$isLocal = defined('APP_ENV') && APP_ENV !== 'production';
$noindex = !empty($noindex) || $isLocal;
if ($noindex && !headers_sent()) header('X-Robots-Tag: noindex, nofollow');
$organization = [
 '@context'=>'https://schema.org', '@type'=>'Organization', '@id'=>$canonicalOrigin.'/#organization',
 'name'=>$siteName, 'url'=>$canonicalOrigin.'/', 'logo'=>$canonicalOrigin.'/sialsourcing-logo.png',
 'description'=>'Sourcing and supply chain coordination for international buyers of products from Pakistan.',
 'contactPoint'=>['@type'=>'ContactPoint','contactType'=>'sales','email'=>setting('contact_email','info@sialsourcing.com'),'availableLanguage'=>['English','Urdu']],
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>">
<meta name="robots" content="<?= $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' ?>">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDesc) ?>">
<meta property="og:type" content="<?= e($ogType) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($pageTitle) ?>">
<meta name="twitter:description" content="<?= e($pageDesc) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<meta name="theme-color" content="#08122a">
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="apple-touch-icon" href="/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap">
<link rel="stylesheet" href="/assets/css/style.css?v=<?= (int) filemtime(__DIR__.'/../assets/css/style.css') ?>">
<link rel="stylesheet" href="/assets/css/refinement.css?v=<?= (int) filemtime(__DIR__.'/../assets/css/refinement.css') ?>">
<script type="application/ld+json"><?= json_encode($organization, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<noscript><style>@media(max-width:1100px){.nav{position:static}.nav-inner{flex-wrap:wrap}.nav-links{display:flex;position:static;max-height:none;padding:16px 0}.hamburger{display:none}}</style></noscript>
<?php
// Preserve the existing production analytics property; local previews never load it.
$analyticsId = getenv('SIAL_GA_MEASUREMENT_ID');
if ($analyticsId === false) $analyticsId = 'G-P9R9P3LNSC';
if (!$isLocal && preg_match('/^G-[A-Z0-9]+$/D', $analyticsId)):
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($analyticsId) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',<?= json_encode($analyticsId) ?>);</script>
<?php endif; ?>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<nav class="nav" id="mainNav" aria-label="Main navigation"><div class="nav-inner">
<a href="/" class="nav-logo" aria-label="SialSourcing home"><span class="brand-icon"><?= icon('globe',30) ?></span><span>Sial<em>Sourcing</em></span></a>
<button class="hamburger" id="hamburger" aria-label="Open navigation" aria-expanded="false" aria-controls="navLinks"><span></span><span></span><span></span></button>
<div class="nav-links" id="navLinks"><a href="/products">Products</a><a href="/sourcing-regions">Sourcing regions</a><a href="/solutions">Our process</a><a href="/lab-qc">Quality control</a><a href="/resources">Resources</a><a href="/about">About</a><a href="/contact" class="nav-cta">Start a sourcing brief <?= icon('arrow-right',16) ?></a></div>
</div></nav>
<main id="main-content" tabindex="-1">
