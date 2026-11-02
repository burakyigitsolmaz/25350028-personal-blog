<?php
/**
 * Beğeni uç noktası (yalnızca POST + CSRF).
 * Aynı IP aynı yazıyı yalnızca bir kez beğenebilir (UNIQUE kısıt).
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

blog_config_boot();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Bu adres yalnızca POST isteklerini kabul eder.');
}

if (!csrf_check()) {
    flash_set('error', 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.');
    redirect('yazilar.php');
}

$articleId = (int)($_POST['article_id'] ?? 0);
$slug      = trim((string)($_POST['slug'] ?? ''));

if ($articleId <= 0) {
    flash_set('error', 'Geçersiz yazı.');
    redirect('yazilar.php');
}

$pdo = db();

// Yazı gerçekten var mı ve yayında mı?
$check = $pdo->prepare('SELECT slug FROM articles WHERE id = ? AND is_published = 1 LIMIT 1');
$check->execute([$articleId]);
$row = $check->fetch();

if (!$row) {
    flash_set('error', 'Yazı bulunamadı.');
    redirect('yazilar.php');
}

// UNIQUE(article_id, ip_hash) sayesinde tekrar beğeni engellenir.
$ins = $pdo->prepare(
    'INSERT IGNORE INTO article_likes (article_id, ip_hash, created_at) VALUES (?, ?, NOW())'
);
$ins->execute([$articleId, ip_hash()]);

if ($ins->rowCount() > 0) {
    flash_set('success', 'Beğeniniz kaydedildi. Teşekkürler!');
} else {
    flash_set('info', 'Bu yazıyı zaten beğenmişsiniz.');
}

redirect('yazi.php?slug=' . urlencode((string)$row['slug']));
