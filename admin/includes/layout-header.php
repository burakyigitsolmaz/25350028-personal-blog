<?php
/**
 * Yönetim paneli üst parçası.
 * Kullanmadan önce $adminTitle tanımlanabilir.
 */

$currentAdmin = current_admin();
$adminTitle   = $adminTitle ?? 'Yönetim Paneli';
$currentFile  = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');

$menu = [
    'index.php'       => 'Özet',
    'yazilar.php'     => 'Yazılar',
    'kategoriler.php' => 'Kategoriler',
    'etiketler.php'   => 'Etiketler',
    'projeler.php'    => 'Projeler',
    'yetenekler.php'  => 'Yetenekler',
    'mesajlar.php'    => 'Mesajlar',
    'ayarlar.php'     => 'Site Ayarları',
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($adminTitle) ?> | Yönetim</title>
  <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body>

  <header class="admin-topbar">
    <a class="admin-brand" href="<?= e(url('admin/index.php')) ?>">
      <span class="admin-brand-dot"></span> Burak Blog · Yönetim
    </a>
    <div class="admin-topbar-right">
      <a class="admin-site-link" href="<?= e(url()) ?>" target="_blank" rel="noopener">Siteyi Gör ↗</a>
      <?php if ($currentAdmin): ?>
        <span class="admin-user">@<?= e($currentAdmin['username']) ?></span>
        <a class="admin-logout" href="<?= e(url('admin/logout.php')) ?>">Çıkış</a>
      <?php endif; ?>
    </div>
  </header>

  <div class="admin-shell">

    <aside class="admin-sidebar">
      <nav aria-label="Yönetim menüsü">
        <ul>
          <?php foreach ($menu as $file => $label): ?>
            <li>
              <a class="<?= $currentFile === $file ? 'active' : '' ?>"
                 href="<?= e(url('admin/' . $file)) ?>"><?= e($label) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
      <p class="admin-version">Veritabanı bağlantısı aktif<br><small>kisisel_blog_db</small></p>
    </aside>

    <main class="admin-main">
      <?php $flash = flash_get(); ?>
      <?php if ($flash): ?>
        <div class="admin-flash admin-flash-<?= e($flash['type']) ?>">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

      <h1 class="admin-title"><?= e($adminTitle) ?></h1>
