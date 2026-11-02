<?php
/**
 * Site geneli üst parça: DOCTYPE, navbar, flash banner.
 * Her public sayfada çıktı üretmeden önce çağrılır.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/functions.php';

blog_config_boot();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sayfa ziyaretini kaydet (page + ip_hash)
log_visit(basename($_SERVER['SCRIPT_NAME'] ?? 'index.php'));

$flash = flash_get();

// Sayfalar bu değişkenleri header'dan ÖNCE tanımlayarak başlık/açıklama verebilir.
$pageTitle       = $pageTitle       ?? setting('site_title', 'Burak Yiğit Solmaz | Kişisel Blog');
$pageDescription = $pageDescription ?? setting('site_description', 'Burak Yiğit Solmaz — Kişisel blog ve tanıtım sitesi.');
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($pageDescription) ?>">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body>

  <a class="skip-link" href="#main-content">İçeriğe geç</a>
  <div class="scroll-progress" aria-hidden="true"><span id="scrollProgress"></span></div>

  <header class="site-header">
    <nav class="navbar" aria-label="Ana menü">
      <a href="<?= e(url()) ?>" class="logo">
        <span class="logo-text"><?= e(setting('site_title_short', 'Burak Yiğit Solmaz')) ?></span>
      </a>
      <button class="menu-toggle" id="menuToggle" aria-label="Menüyü aç/kapat" aria-expanded="false" aria-controls="navLinks">
        <span></span><span></span><span></span>
      </button>
      <ul class="nav-links" id="navLinks">
        <li><a href="<?= e(url()) ?>" class="nav-link">Ana Sayfa</a></li>
        <li><a href="<?= e(url('yazilar.php')) ?>" class="nav-link">Yazılar</a></li>
        <li><a href="<?= e(url('projeler.php')) ?>" class="nav-link">Projeler</a></li>
        <li><a href="<?= e(url('iletisim.php')) ?>" class="nav-link">İletişim</a></li>
      </ul>
      <button class="theme-toggle" id="themeToggle" aria-label="Açık/koyu tema arasında geçiş yap">
        <svg class="icon-sun" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
        <svg class="icon-moon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>
    </nav>
  </header>

  <?php if ($flash): ?>
    <div class="flash-banner flash-<?= e($flash['type']) ?>" role="status">
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <main id="main-content">
