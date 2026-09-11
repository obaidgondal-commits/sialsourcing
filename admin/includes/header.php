<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Admin') ?> — SialSourcing CMS</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap');
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'DM Sans',sans-serif;background:#f3f4f6;min-height:100vh;display:flex;}
    /* Sidebar */
    .sidebar{width:260px;background:#08122a;min-height:100vh;position:fixed;left:0;top:0;bottom:0;display:flex;flex-direction:column;z-index:100;}
    .sidebar-logo{padding:1.75rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.08);}
    .sidebar-logo a{font-family:'Playfair Display',serif;font-size:1.5rem;color:#fff;text-decoration:none;}
    .sidebar-logo span{color:#c9a84c}
    .sidebar-logo small{display:block;font-size:0.7rem;color:rgba(255,255,255,0.4);letter-spacing:2px;text-transform:uppercase;margin-top:0.2rem;}
    nav.sidebar-nav{flex:1;padding:1.5rem 0;}
    .nav-group{margin-bottom:0.25rem;}
    .nav-label{font-size:0.65rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.3);padding:0.75rem 1.5rem 0.25rem;}
    .nav-group a{display:flex;align-items:center;gap:0.75rem;padding:0.65rem 1.5rem;color:rgba(255,255,255,0.6);text-decoration:none;font-size:0.88rem;font-weight:500;transition:all 0.2s;border-left:3px solid transparent;}
    .nav-group a:hover,.nav-group a.active{color:#fff;background:rgba(201,168,76,0.1);border-left-color:#c9a84c;}
    .sidebar-bottom{padding:1.5rem;border-top:1px solid rgba(255,255,255,0.08);}
    .sidebar-user{font-size:0.8rem;color:rgba(255,255,255,0.5);margin-bottom:0.75rem;}
    .sidebar-user strong{color:#fff;display:block;}
    .btn-logout{display:block;text-align:center;padding:0.5rem;background:rgba(255,255,255,0.05);color:rgba(255,255,255,0.5);border-radius:6px;text-decoration:none;font-size:0.8rem;transition:all 0.2s;}
    .btn-logout:hover{background:rgba(220,38,38,0.2);color:#f87171;}
    /* Main */
    .main{margin-left:260px;flex:1;min-height:100vh;display:flex;flex-direction:column;}
    .topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;}
    .topbar h1{font-size:1.3rem;font-weight:700;color:#1f2937;}
    .topbar-meta{font-size:0.8rem;color:#9ca3af;}
    .content{padding:2rem;flex:1;}
    /* Cards */
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem;}
    .stat-card{background:#fff;border-radius:12px;padding:1.5rem;border:1px solid #e5e7eb;}
    .stat-card .num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:#08122a;}
    .stat-card .label{font-size:0.78rem;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-top:0.25rem;}
    /* Table */
    .card{background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;margin-bottom:1.5rem;}
    .card-header{padding:1.25rem 1.5rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;}
    .card-header h2{font-size:1rem;font-weight:700;color:#1f2937;}
    table{width:100%;border-collapse:collapse;}
    th{text-align:left;padding:0.75rem 1rem;font-size:0.75rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#9ca3af;background:#f9fafb;border-bottom:1px solid #f3f4f6;}
    td{padding:0.85rem 1rem;font-size:0.88rem;color:#374151;border-bottom:1px solid #f9fafb;vertical-align:middle;}
    tr:last-child td{border:none;}
    tr:hover td{background:#fafafa;}
    /* Badges */
    .badge{display:inline-block;padding:0.2rem 0.65rem;border-radius:50px;font-size:0.72rem;font-weight:600;}
    .badge-green{background:#dcfce7;color:#16a34a;}
    .badge-gray{background:#f3f4f6;color:#6b7280;}
    .badge-gold{background:rgba(201,168,76,0.1);color:#c9a84c;}
    /* Forms */
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}
    .form-full{grid-column:1/-1;}
    .form-group{display:flex;flex-direction:column;gap:0.4rem;}
    .form-group label{font-size:0.82rem;font-weight:600;color:#374151;}
    .form-group input,.form-group select,.form-group textarea{padding:0.7rem 0.9rem;border:1.5px solid #e5e7eb;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.9rem;outline:none;transition:border-color 0.2s;width:100%;}
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#c9a84c;}
    .form-group textarea{resize:vertical;min-height:120px;}
    .btn-save{background:#c9a84c;color:#08122a;border:none;padding:0.7rem 1.75rem;border-radius:8px;font-weight:700;cursor:pointer;font-size:0.9rem;font-family:'DM Sans',sans-serif;transition:background 0.2s;}
    .btn-save:hover{background:#e8c96a;}
    .btn-danger{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:0.4rem 0.9rem;border-radius:6px;font-size:0.78rem;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all 0.2s;}
    .btn-danger:hover{background:#dc2626;color:#fff;}
    .btn-edit{background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;padding:0.4rem 0.9rem;border-radius:6px;font-size:0.78rem;cursor:pointer;text-decoration:none;font-family:'DM Sans',sans-serif;transition:all 0.2s;}
    .btn-edit:hover{background:#0369a1;color:#fff;}
    .btn-sm{padding:0.35rem 0.8rem;border-radius:6px;font-size:0.78rem;font-weight:600;cursor:pointer;text-decoration:none;font-family:'DM Sans',sans-serif;transition:all 0.2s;}
    .alert{padding:0.85rem 1.25rem;border-radius:8px;margin-bottom:1.25rem;font-size:0.88rem;}
    .alert-success{background:#dcfce7;color:#166534;border:1px solid #86efac;}
    .alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;}
  </style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <a href="<?= SITE_URL ?>">SialSourcing<span>.</span></a>
    <small>Content Manager</small>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-group">
      <div class="nav-label">Overview</div>
      <a href="dashboard.php" <?= (basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="active"':'') ?>>📊 Dashboard</a>
    </div>
    <div class="nav-group">
      <div class="nav-label">Content</div>
      <a href="products.php" <?= (basename($_SERVER['PHP_SELF'])=='products.php'?'class="active"':'') ?>>📦 Products</a>
      <a href="product-pages.php" <?= (basename($_SERVER['PHP_SELF'])=='product-pages.php'?'class="active"':'') ?>>🗂 Product Pages</a>
      <a href="solutions.php" <?= (basename($_SERVER['PHP_SELF'])=='solutions.php'?'class="active"':'') ?>>⚙️ Solutions</a>
      <a href="lab.php" <?= (basename($_SERVER['PHP_SELF'])=='lab.php'?'class="active"':'') ?>>🔬 Lab & QC</a>
      <a href="blog.php" <?= (basename($_SERVER['PHP_SELF'])=='blog.php'?'class="active"':'') ?>>✍️ Blog</a>
      <a href="faq.php" <?= (basename($_SERVER['PHP_SELF'])=='faq.php'?'class="active"':'') ?>>❓ FAQs</a>
      <a href="team.php" <?= (basename($_SERVER['PHP_SELF'])=='team.php'?'class="active"':'') ?>>👥 Team</a>
    </div>
    <div class="nav-group">
      <div class="nav-label">System</div>
      <a href="settings.php" <?= (basename($_SERVER['PHP_SELF'])=='settings.php'?'class="active"':'') ?>>⚙️ Settings</a>
      <a href="inbox.php" <?= (basename($_SERVER['PHP_SELF'])=='inbox.php'?'class="active"':'') ?>>📬 Enquiries</a>
      <a href="manufacturers.php" <?= (basename($_SERVER['PHP_SELF'])=='manufacturers.php'?'class="active"':'') ?>>🏭 Manufacturers</a>
    </div>
  </nav>
  <div class="sidebar-bottom">
    <div class="sidebar-user">Logged in as<strong><?= e($adminUser) ?></strong></div>
    <form method="post" action="logout.php">
      <?= csrf_field() ?>
      <button type="submit" class="btn-logout" style="width:100%;border:0;cursor:pointer;">Sign Out</button>
    </form>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
    <div class="topbar-meta">SialSourcing CMS &nbsp;|&nbsp; <?= date('D, d M Y') ?></div>
  </div>
  <div class="content">
