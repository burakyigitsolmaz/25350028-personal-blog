<?php
/**
 * Yazı listesi: yayın durumu değiştir, sil.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Güvenlik doğrulaması başarısız.');
        redirect('admin/yazilar.php');
    }

    $id     = (int)($_POST['id'] ?? 0);
    $action = (string)($_POST['action'] ?? '');

    $check = $pdo->prepare('SELECT id, title, is_published FROM articles WHERE id = ? LIMIT 1');
    $check->execute([$id]);
    $article = $check->fetch();

    if (!$article) {
        flash_set('error', 'Yazı bulunamadı.');
        redirect('admin/yazilar.php');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        log_activity('Yazı silindi', $article['title']);
        flash_set('success', 'Yazı silindi: ' . $article['title']);
    } elseif ($action === 'toggle') {
        $newState = (int)$article['is_published'] === 1 ? 0 : 1;
        $stmt = $pdo->prepare('UPDATE articles SET is_published = ? WHERE id = ?');
        $stmt->execute([$newState, $id]);
        log_activity(
            $newState === 1 ? 'Yazı yayınlandı' : 'Yazı yayından kaldırıldı',
            $article['title']
        );
        flash_set('success', $newState === 1 ? 'Yazı yayınlandı.' : 'Yazı yayından kaldırıldı.');
    }
    redirect('admin/yazilar.php');
}

$articles = $pdo->query(
    'SELECT a.id, a.title, a.slug, a.view_count, a.is_published, a.created_at,
            c.name AS category_name,
            (SELECT COUNT(*) FROM article_tags at WHERE at.article_id = a.id) AS tag_count
     FROM articles a
     LEFT JOIN categories c ON c.id = a.category_id
     ORDER BY a.created_at DESC, a.id DESC'
)->fetchAll();

$adminTitle = 'Yazılar';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="btn-row" style="margin-bottom:18px">
  <a class="btn btn-primary" href="<?= e(url('admin/yazi-form.php')) ?>">+ Yeni Yazı</a>
</div>

<section class="panel">
  <h2>Toplam <?= count($articles) ?> yazı</h2>

  <?php if (empty($articles)): ?>
    <p style="color:#a2a2ad;font-size:.9rem">Henüz yazı yok. “Yeni Yazı” ile başlayın.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Başlık</th>
            <th>Kategori</th>
            <th>Etiket</th>
            <th>Okunma</th>
            <th>Durum</th>
            <th>Tarih</th>
            <th class="col-actions">İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($articles as $row): ?>
            <tr>
              <td class="mono"><?= (int)$row['id'] ?></td>
              <td>
                <?= e($row['title']) ?><br>
                <span class="mono">/<?= e($row['slug']) ?></span>
              </td>
              <td><?= e($row['category_name'] ?? '—') ?></td>
              <td class="mono"><?= (int)$row['tag_count'] ?></td>
              <td class="mono"><?= (int)$row['view_count'] ?></td>
              <td>
                <?php if ((int)$row['is_published'] === 1): ?>
                  <span class="badge badge-success">Yayında</span>
                <?php else: ?>
                  <span class="badge badge-warn">Taslak</span>
                <?php endif; ?>
              </td>
              <td class="mono"><?= e(date('d.m.Y', strtotime((string)$row['created_at']))) ?></td>
              <td class="col-actions">
                <a class="btn btn-outline btn-sm" href="<?= e(url('admin/yazi-form.php?id=' . (int)$row['id'])) ?>">Düzenle</a>

                <form method="post" class="inline-form" action="<?= e(url('admin/yazilar.php')) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <input type="hidden" name="action" value="toggle">
                  <button type="submit" class="btn btn-outline btn-sm">
                    <?= (int)$row['is_published'] === 1 ? 'Kaldır' : 'Yayınla' ?>
                  </button>
                </form>

                <form method="post" class="inline-form" action="<?= e(url('admin/yazilar.php')) ?>"
                      onsubmit="return confirm('Bu yazı kalıcı olarak silinsin mi?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
