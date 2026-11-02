<?php
/**
 * Yönetim paneli özet ekranı.
 */

require_once __DIR__ . '/includes/auth.php';
$admin = require_login();

$pdo = db();

$totalArticles   = (int)$pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$publishedCount  = (int)$pdo->query('SELECT COUNT(*) FROM articles WHERE is_published = 1')->fetchColumn();
$totalMessages   = (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$unreadMessages  = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$totalVisits     = (int)$pdo->query('SELECT COUNT(*) FROM visits')->fetchColumn();
$todayVisits     = (int)$pdo->query('SELECT COUNT(*) FROM visits WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$totalLikes      = (int)$pdo->query('SELECT COUNT(*) FROM article_likes')->fetchColumn();
$totalDownloads  = (int)$pdo->query('SELECT COUNT(*) FROM downloads')->fetchColumn();

$topArticles = $pdo->query(
    'SELECT id, title, slug, view_count, is_published
     FROM articles
     ORDER BY view_count DESC, created_at DESC
     LIMIT 5'
)->fetchAll();

$lastMessages = $pdo->query(
    'SELECT id, name, subject, is_read, created_at
     FROM messages
     ORDER BY created_at DESC
     LIMIT 4'
)->fetchAll();

$lastActivity = $pdo->query(
    'SELECT l.action, l.detail, l.created_at, a.username
     FROM activity_log l
     LEFT JOIN admins a ON a.id = l.admin_id
     ORDER BY l.created_at DESC, l.id DESC
     LIMIT 6'
)->fetchAll();

$adminTitle = 'Özet';
require __DIR__ . '/includes/layout-header.php';
?>

<p style="color:#a2a2ad;font-size:.9rem;margin-bottom:20px">
  Merhaba <strong style="color:#f0f0f2"><?= e($admin['username']) ?></strong>. Veritabanı bağlı çalışıyor;
  aşağıdaki sayılar doğrudan <span class="mono">kisisel_blog_db</span> tablolarından geliyor.
</p>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Toplam Yazı</div>
    <div class="stat-value"><?= $totalArticles ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Yayında</div>
    <div class="stat-value"><?= $publishedCount ?></div>
  </div>
  <div class="stat-card highlight">
    <div class="stat-label">Okunmamış Mesaj</div>
    <div class="stat-value"><?= $unreadMessages ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Toplam Mesaj</div>
    <div class="stat-value"><?= $totalMessages ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Bugünkü Ziyaret</div>
    <div class="stat-value"><?= $todayVisits ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Toplam Ziyaret</div>
    <div class="stat-value"><?= $totalVisits ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Beğeni</div>
    <div class="stat-value"><?= $totalLikes ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">CV İndirme</div>
    <div class="stat-value"><?= $totalDownloads ?></div>
  </div>
</div>

<div class="split">

  <section class="panel">
    <h2>En Çok Okunan Yazılar</h2>
    <?php if (empty($topArticles)): ?>
      <p style="color:#a2a2ad;font-size:.88rem">Henüz yazı yok.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>Başlık</th><th>Okunma</th><th>Durum</th></tr>
          </thead>
          <tbody>
            <?php foreach ($topArticles as $row): ?>
              <tr>
                <td><?= e($row['title']) ?></td>
                <td class="mono"><?= (int)$row['view_count'] ?></td>
                <td>
                  <?php if ((int)$row['is_published'] === 1): ?>
                    <span class="badge badge-success">Yayında</span>
                  <?php else: ?>
                    <span class="badge badge-warn">Taslak</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <section class="panel">
    <h2>Son Mesajlar</h2>
    <?php if (empty($lastMessages)): ?>
      <p style="color:#a2a2ad;font-size:.88rem">Henüz mesaj yok.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr><th>Gönderen</th><th>Konu</th><th>Tarih</th></tr>
          </thead>
          <tbody>
            <?php foreach ($lastMessages as $msg): ?>
              <tr>
                <td>
                  <?= e($msg['name']) ?>
                  <?php if ((int)$msg['is_read'] === 0): ?>
                    <span class="badge badge-danger">Yeni</span>
                  <?php endif; ?>
                </td>
                <td><?= e($msg['subject']) ?></td>
                <td class="mono"><?= e(date('d.m.Y H:i', strtotime((string)$msg['created_at']))) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="btn-row">
        <a class="btn btn-outline btn-sm" href="<?= e(url('admin/mesajlar.php')) ?>">Tüm Mesajlar →</a>
      </div>
    <?php endif; ?>
  </section>

</div>

<section class="panel">
  <h2>Son İşlemler (activity_log)</h2>
  <?php if (empty($lastActivity)): ?>
    <p style="color:#a2a2ad;font-size:.88rem">Henüz kayıtlı işlem yok.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>Tarih</th><th>Kullanıcı</th><th>İşlem</th><th>Detay</th></tr>
        </thead>
        <tbody>
          <?php foreach ($lastActivity as $row): ?>
            <tr>
              <td class="mono"><?= e(date('d.m.Y H:i', strtotime((string)$row['created_at']))) ?></td>
              <td class="mono"><?= e($row['username'] ?? '—') ?></td>
              <td><?= e($row['action']) ?></td>
              <td class="mono"><?= e($row['detail']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
