<?php
// includes/header.php — shared across all frontend pages
//
// Page contract (all optional — set BEFORE requiring this file):
//   $pageTitle     — page title; "| SialSourcing" is appended only if the brand isn't already in it
//   $pageDesc      — meta description
//   $canonicalPath — canonical path, leading slash, no query string (defaults to the request path)
//   $ogImage       — absolute URL for social-share image (defaults to /og-image.jpg)
//   $ogType        — Open Graph type (defaults to "website"; blog posts use "article")
//   $noindex       — set true on pages that must not be indexed (e.g. 404)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/icons.php';

$siteName  = setting('site_name', 'SialSourcing');
$pageTitle = $pageTitle ?? $siteName . ' — Pakistan Sourcing & Supply Chain Partner';
if (stripos($pageTitle, $siteName) === false) {
    $pageTitle .= ' | ' . $siteName;
}
$pageDesc      = $pageDesc ?? setting('site_tagline', 'Pakistan\'s Premier Sourcing Partner');
$canonicalPath = $canonicalPath ?? strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$canonicalUrl  = SITE_URL . $canonicalPath;
$ogImage       = $ogImage ?? SITE_URL . '/og-image.jpg';
$ogType        = $ogType ?? 'website';
$cssVersion    = @filemtime(__DIR__ . '/../assets/css/style.css') ?: 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <?php if ($ogImage === SITE_URL . '/og-image.jpg'): ?>
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="SialSourcing — Pakistan's Premier Sourcing Partner">
  <?php endif; ?>
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <meta property="og:type" content="<?= e($ogType) ?>">
  <meta property="og:site_name" content="SialSourcing">
  <!-- WhatsApp uses Twitter card as fallback -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="<?= e($ogImage) ?>">
  <?php if (!empty($noindex)): ?>
  <meta name="robots" content="noindex, nofollow">
  <?php else: ?>
  <meta name="robots" content="index, follow">
  <?php endif; ?>
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  <meta name="theme-color" content="#08122a">
  <!-- Favicon — all browsers and devices -->
  <link rel="icon" type="image/webp" sizes="512x512" href="<?= SITE_URL ?>/favicon.webp">
  <link rel="icon" type="image/png" sizes="512x512" href="<?= SITE_URL ?>/favicon.png">
  <link rel="apple-touch-icon" href="<?= SITE_URL ?>/favicon.png">
  <!-- Preconnect to font origins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <!-- Load Google Fonts async -->
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap"></noscript>
  <!-- Site styles — cached for a year, version param busts on deploy -->
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= $cssVersion ?>">
  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "SialSourcing",
  "url": "https://sialsourcing.com",
  "logo": "https://sialsourcing.com/sialsourcing-logo.png",
  "description": "End-to-end supply chain solutions from Sialkot, Pakistan. Manufacturer vetting, quality inspection, lab testing, logistics, and doorstep delivery.",
  "foundingLocation": "Sialkot, Pakistan",
  "areaServed": ["US", "FR", "GB", "DE", "AU", "AE", "CA"],
  "knowsAbout": ["Supply Chain Management", "Quality Control", "Surgical Instruments", "Sportswear Manufacturing", "Soccer Ball Manufacturing", "Uniforms & Tactical Wear", "Leather Goods", "Export from Pakistan"],
  "address": [
    {
      "@type": "PostalAddress",
      "addressLocality": "Sialkot",
      "addressRegion": "Punjab",
      "addressCountry": "PK",
      "description": "Operations & QC Hub"
    },
    {
      "@type": "PostalAddress",
      "addressLocality": "Dallas",
      "addressRegion": "TX",
      "addressCountry": "US",
      "description": "Americas Headquarters"
    },
    {
      "@type": "PostalAddress",
      "addressLocality": "Paris",
      "addressCountry": "FR",
      "description": "European Headquarters"
    }
  ],
  "contactPoint": [
    {
      "@type": "ContactPoint",
      "telephone": "+92-300-1100110",
      "contactType": "sales",
      "email": "info@sialsourcing.com",
      "availableLanguage": ["English", "Urdu"]
    }
  ],
  "sameAs": []
}
</script>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-P9R9P3LNSC"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-P9R9P3LNSC');
</script>
</head>
<body>
<nav class="nav" id="mainNav" aria-label="Main navigation">
  <div class="nav-inner">
    <a href="<?= SITE_URL ?>" class="nav-logo" aria-label="SialSourcing — Home">
      <!-- SVG globe icon — instant render, no external file -->
      <svg width="44" height="44" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="flex-shrink:0;">
        <circle cx="18" cy="18" r="16" stroke="#c9a84c" stroke-width="1.5"/>
        <ellipse cx="18" cy="18" rx="7" ry="16" stroke="#c9a84c" stroke-width="1.5"/>
        <line x1="2" y1="18" x2="34" y2="18" stroke="#c9a84c" stroke-width="1.5"/>
        <line x1="5" y1="10" x2="31" y2="10" stroke="#c9a84c" stroke-width="1"/>
        <line x1="5" y1="26" x2="31" y2="26" stroke="#c9a84c" stroke-width="1"/>
        <circle cx="8" cy="18" r="2" fill="#c9a84c"/>
        <circle cx="18" cy="18" r="2" fill="#c9a84c"/>
        <circle cx="28" cy="18" r="2" fill="#c9a84c"/>
        <line x1="8" y1="18" x2="28" y2="18" stroke="#c9a84c" stroke-width="1"/>
        <polyline points="22,15 26,18 22,21" fill="none" stroke="#c9a84c" stroke-width="1.2"/>
      </svg>
      <span>Sial<em>Sourcing</em></span>
    </a>
    <div class="nav-links" id="navLinks">
      <div class="nav-dropdown">
        <a href="<?= SITE_URL ?>/products" class="nav-dropdown-toggle">Products ▾</a>
        <div class="nav-dropdown-menu">
          <a href="<?= SITE_URL ?>/products" class="nav-dropdown-header">All Products</a>
          <a href="<?= SITE_URL ?>/custom-soccer-balls"><span class="nav-ico"><?= icon('soccer-ball', 17) ?></span> Custom Soccer Balls</a>
          <a href="<?= SITE_URL ?>/activewear-sports-uniforms"><span class="nav-ico"><?= icon('shirt', 17) ?></span> Activewear &amp; Sports Uniforms</a>
          <a href="<?= SITE_URL ?>/uniforms-tactical-wear"><span class="nav-ico"><?= icon('shield', 17) ?></span> Uniforms &amp; Tactical Wear</a>
          <a href="<?= SITE_URL ?>/surgical-instruments"><span class="nav-ico"><?= icon('scissors', 17) ?></span> Surgical Instruments</a>
          <a href="<?= SITE_URL ?>/sports-goods"><span class="nav-ico"><?= icon('target', 17) ?></span> Sports Goods</a>
          <a href="<?= SITE_URL ?>/leather-goods"><span class="nav-ico"><?= icon('briefcase', 17) ?></span> Leather Products</a>
          <a href="<?= SITE_URL ?>/cutlery"><span class="nav-ico"><?= icon('cutlery', 17) ?></span> Cutlery &amp; Kitchenware</a>
        </div>
      </div>
      <a href="<?= SITE_URL ?>/solutions">Solutions</a>
      <a href="<?= SITE_URL ?>/lab-qc">Lab &amp; QC</a>
      <a href="<?= SITE_URL ?>/blog">Blog</a>
      <a href="<?= SITE_URL ?>/resources">Resources</a>
      <a href="<?= SITE_URL ?>/about">About</a>
      <a href="<?= SITE_URL ?>/team">Team</a>
      <a href="<?= SITE_URL ?>/contact" class="nav-cta">Get a Quote</a>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
<main>
