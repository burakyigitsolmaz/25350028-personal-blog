<?php
/**
 * Etiket yönetimi: ekle, düzenle, sil.
 * article_tags köprü tablosu sayesinde yazı-etiket ilişkisi çoktan-çoğadır.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

$editId  = (int)($_GET['id'] ?? 0);
$editRow = ['id' => 0, 'name' => '', 'slug' => ''];

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT id, name, slug FROM tags WHERE id = ? LIMIT 1');
    $stmt->execute([$editId]);
    $found = $stmt->fetch();
    if ($found) {
        $editRow = $found;
    } else {
        flash_set('error', 'Etiket bulunamadı.');
        redirect('admin/etiketler.php');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Güvenlik doğrulaması başarısız.');
        redirect('admin/etiketler.php');
    }

    $action = (string)($_POST['action'] ?? 'save');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        $chk = $pdo->prepare('SELECT name FROM tags WHERE id = ? LIMIT 1');
        $chk->execute([$id]);
        $name = $chk->fetchColumn();

        if ($name !== false) {
            $pdo->prepare('DELETE FROM tags WHERE id = ?')->execute([$id]);
            log_activity('Etiket silindi', (string)$name);
            flash_set('success', 'Etiket silindi: ' . $name);
        } else {
            flash_set('error', 'Etiket bulunamadı.');
        }
        redirect('admin/etiketler.php');
    }

    $name = post_str('name', 50);
    $slug = post_str('slug', 60);
    $slug = $slug !== '' ? slugify($slug) : slugify($name);

    if (mb_strlen($name, 'UTF-8') < 2) {
        flash_set('error', 'Etiket adı en az 2 karakter olmalıdır.');
        redirect('admin/etiketler.php');
    }

    $dup = $pdo->prepare('SELECT id FROM tags WHERE (name = ? OR slug = ?) AND id <> ? LIMIT 1');
    $dup->execute([$name, $slug, $id]);
    if ($dup->fetchColumn()) {
        flash_set('error', 'Bu etiket zaten var.');
        redirect('admin/etiketler.php');
    }

    if ($id > 0) {
        $pdo->prepare('UPDATE tags SET name = ?, slug = ? WHERE id = ?')->execute([$name, $slug, $id]);
        log_activity('Etiket güncellendi', $name);
        flash_set('success', 'Etiket güncellendi.');
    } else {
        $pdo->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)')->execute([$name, $slug]);
        log_activity('Etiket eklendi', $name);
        flash_set('success', 'Etiket eklendi.');
    }
    redirect('admin/etiketler.php');
}

$tags = $pdo->query(
    'SELECT t.id, t.name, t.slug, COUNT(at.article_id) AS article_count
     FROM tags t
     LEFT JOIN article_tags at ON at.tag_id = t.id
     GROUP BY t.id, t.name, t.slug
     ORDER BY t.name ASC'
)->fetchAll();

$adminTitle = 'Etiketler';
require __DIR__ . '/includes/layout-header.php';
?>

<section class="panel">
  <h2><?= $editRow['id'] > 0 ? 'Etiketi Düzenle' : 'Yeni Etiket' ?></h2>
  <form class="admin-form" method="post" action="<?= e(url('admin/etiketler.php')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

    <div class="form-row-2">
      <div class="form-field">
        <label for="name">Etiket Adı *</label>
        <input type="text" id="name" name="name" maxlength="50" required value="<?= e($editRow['name']) ?>">
      </div>
      <div class="form-field">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" maxlength="60"
               placeholder="Boş bırakılırsa addan üretilir" value="<?= e($editRow['slug']) ?>">
      </div>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editRow['id'] > 0 ? 'Güncelle' : 'Ekle' ?></button>
      <?php if ($editRow['id'] > 0): ?>
        <a class="btn btn-outline" href="<?= e(url('admin/etiketler.php')) ?>">Vazgeç</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h2>Etiketler (<?= count($tags) ?>)</h2>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>#</th><th>Ad</th><th>Slug</th><th>Yazı</th><th class="col-actions">İşlem</th></tr>
      </thead>
      <tbody>
        <?php foreach ($tags as $row): ?>
          <tr>
            <td class="mono"><?= (int)$row['id'] ?></td>
            <td>#<?= e($row['name']) ?></td>
            <td class="mono"><?= e($row['slug']) ?></td>
            <td class="mono"><?= (int)$row['article_count'] ?></td>
            <td class="col-actions">
              <a class="btn btn-outline btn-sm" href="<?= e(url('admin/etiketler.php?id=' . (int)$row['id'])) ?>">Düzenle</a>
              <form method="post" class="inline-form" action="<?= e(url('admin/etiketler.php')) ?>"
                    onsubmit="return confirm('Etiket silinsin mi? Yazılardan da kaldırılacak.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Sil</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
