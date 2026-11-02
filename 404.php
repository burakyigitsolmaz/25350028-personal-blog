<?php
/**
 * 404 — Sayfa bulunamadı.
 * page_not_found() tarafından da çağrılır.
 */

if (!function_exists('e')) {
    require_once __DIR__ . '/includes/db.php';
    require_once __DIR__ . '/includes/functions.php';
    blog_config_boot();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

if (!headers_sent()) {
    http_response_code(404);
}

$pageTitle       = 'Sayfa Bulunamadı | ' . setting('site_title_short', 'Burak Yiğit Solmaz');
$pageDescription = 'Aradığınız sayfa bulunamadı.';

require __DIR__ . '/includes/header.php';
?>

    <section class="section notfound">
      <div class="container notfound-inner">
        <p class="notfound-code">404</p>
        <h1>Bu sayfayı bulamadım</h1>
        <p class="notfound-text">
          Adres yanlış yazılmış olabilir ya da yazı yayından kaldırılmış olabilir.
        </p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="<?= e(url()) ?>">Ana Sayfa</a>
          <a class="btn btn-outline" href="<?= e(url('yazilar.php')) ?>">Yazılara Git</a>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
