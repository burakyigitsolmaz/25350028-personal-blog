<?php
/**
 * İletişim formundan gelen mesajlar.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Güvenlik doğrulaması başarısız.');
        redirect('admin/mesajlar.php');
    }

    $action = (string)($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'read') {
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
        flash_set('success', 'Mesaj okundu olarak işaretlendi.');
    } elseif ($action === 'unread') {
        $pdo->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([$id]);
        flash_set('success', 'Mesaj okunmadı olarak işaretlendi.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        log_activity('Mesaj silindi', 'ID: ' . $id);
        flash_set('success', 'Mesaj silindi.');
    } elseif ($action === 'read_all') {
        $pdo->exec('UPDATE messages SET is_read = 1 WHERE is_read = 0');
        flash_set('success', 'Tüm mesajlar okundu olarak işaretlendi.');
    }

    redirect('admin/mesajlar.php');
}

$messages = $pdo->query(
    'SELECT id, name, email, subject, message, is_read, created_at
     FROM messages ORDER BY created_at DESC, id DESC'
)->fetchAll();

$unreadTotal = 0;
foreach ($messages as $msg) {
    if ((int)$msg['is_read'] === 0) {
        $unreadTotal++;
    }
}

$adminTitle = 'Mesajlar';
require __DIR__ . '/includes/layout-header.php';
?>

<div class="btn-row" style="margin-bottom:18px">
  <span class="badge badge-danger">Okunmamış: <?= $unreadTotal ?></span>
  <?php if ($unreadTotal > 0): ?>
    <form method="post" class="inline-form" action="<?= e(url('admin/mesajlar.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="read_all">
      <button type="submit" class="btn btn-outline btn-sm">Tümünü okundu işaretle</button>
    </form>
  <?php endif; ?>
</div>

<?php if (empty($messages)): ?>
  <section class="panel">
    <p style="color:#a2a2ad;font-size:.9rem">
      Henüz mesaj yok. Sitedeki iletişim formundan gönderilen mesajlar burada listelenir.
    </p>
  </section>
<?php else: ?>
  <?php foreach ($messages as $msg): ?>
    <article class="message-item <?= (int)$msg['is_read'] === 0 ? 'unread' : '' ?>">
      <div class="message-head">
        <div>
          <strong><?= e($msg['name']) ?></strong>
          <?php if ((int)$msg['is_read'] === 0): ?>
            <span class="badge badge-danger">Yeni</span>
          <?php endif; ?>
          <div class="message-meta">
            <?= e($msg['email']) ?> · <?= e(date('d.m.Y H:i', strtotime((string)$msg['created_at']))) ?>
            · ID: <?= (int)$msg['id'] ?>
          </div>
        </div>
        <div>
          <span class="badge"><?= e($msg['subject']) ?></span>
        </div>
      </div>

      <p class="message-body"><?= e($msg['message']) ?></p>

      <div class="btn-row" style="margin-top:0">
        <form method="post" class="inline-form" action="<?= e(url('admin/mesajlar.php')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
          <input type="hidden" name="action" value="<?= (int)$msg['is_read'] === 1 ? 'unread' : 'read' ?>">
          <button type="submit" class="btn btn-outline btn-sm">
            <?= (int)$msg['is_read'] === 1 ? 'Okunmadı yap' : 'Okundu yap' ?>
          </button>
        </form>

        <form method="post" class="inline-form" action="<?= e(url('admin/mesajlar.php')) ?>"
              onsubmit="return confirm('Mesaj kalıcı olarak silinsin mi?');">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>">
          <input type="hidden" name="action" value="delete">
          <button type="submit" class="btn btn-danger btn-sm">Sil</button>
        </form>

        <a class="btn btn-outline btn-sm" href="mailto:<?= e($msg['email']) ?>?subject=<?= e(rawurlencode('Yanıt: ' . $msg['subject'])) ?>">
          E-posta ile yanıtla
        </a>
      </div>
    </article>
  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
