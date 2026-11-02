<?php
/**
 * Kategori yönetimi: ekle, düzenle, sil.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

$editId = (int)($_GET['id'] ?? 0);
$editRow = ['id' => 0, 'name' => '', 'slug' => ''];

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT id, name, slug FROM categories WHERE id = ? LIMIT 1');
    $stmt->execute([$editId]);
    $found = $stmt->fetch();
    if ($found) {
        $editRow = $found;
    } else {
        flash_set('error', 'Kategori bulunamadı.');
        redirect('admin/kategoriler.php');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Güvenlik doğrulaması başarısız.');
        redirect('admin/kategoriler.php');
    }

    $action = (string)($_POST['action'] ?? 'save');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        $chk = $pdo->prepare('SELECT name FROM categories WHERE id = ? LIMIT 1');
        $chk->execute([$id]);
        $name = $chk->fetchColumn();

        if ($name !== false) {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            log_activity('Kategori silindi', (string)$name);
            flash_set('success', 'Kategori silindi: ' . $name);
        } else {
            flash_set('error', 'Kategori bulunamadı.');
        }
        redirect('admin/kategoriler.php');
    }

    $name = post_str('name', 80);
    $slug = post_str('slug', 100);
    $slug = $slug !== '' ? slugify($slug) : slugify($name);

    if (mb_strlen($name, 'UTF-8') < 2) {
        flash_set('error', 'Kategori adı en az 2 karakter olmalıdır.');
        redirect('admin/kategoriler.php');
    }

    $dup = $pdo->prepare('SELECT id FROM categories WHERE (name = ? OR slug = ?) AND id <> ? LIMIT 1');
    $dup->execute([$name, $slug, $id]);
    if ($dup->fetchColumn()) {
        flash_set('error', 'Bu isim veya slug zaten kullanılıyor.');
        redirect('admin/kategoriler.php');
    }

    if ($id > 0) {
        $pdo->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?')->execute([$name, $slug, $id]);
        log_activity('Kategori güncellendi', $name);
        flash_set('success', 'Kategori güncellendi.');
    } else {
        $pdo->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)')->execute([$name, $slug]);
        log_activity('Kategori eklendi', $name);
        flash_set('success', 'Kategori eklendi.');
    }
    redirect('admin/kategoriler.php');
}

$categories = $pdo->query(
    'SELECT c.id, c.name, c.slug, COUNT(a.id) AS article_count
     FROM categories c
     LEFT JOIN articles a ON a.category_id = c.id
     GROUP BY c.id, c.name, c.slug
     ORDER BY c.name ASC'
)->fetchAll();

$adminTitle = 'Kategoriler';
require __DIR__ . '/includes/layout-header.php';
?>

<section class="panel">
  <h2><?= $editRow['id'] > 0 ? 'Kategoriyi Düzenle' : 'Yeni Kategori' ?></h2>
  <form class="admin-form" method="post" action="<?= e(url('admin/kategoriler.php')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

    <div class="form-row-2">
      <div class="form-field">
        <label for="name">Kategori Adı *</label>
        <input type="text" id="name" name="name" maxlength="80" required value="<?= e($editRow['name']) ?>">
      </div>
      <div class="form-field">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" maxlength="100"
               placeholder="Boş bırakılırsa addan üretilir" value="<?= e($editRow['slug']) ?>">
      </div>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editRow['id'] > 0 ? 'Güncelle' : 'Ekle' ?></button>
      <?php if ($editRow['id'] > 0): ?>
        <a class="btn btn-outline" href="<?= e(url('admin/kategoriler.php')) ?>">Vazgeç</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h2>Kategoriler (<?= count($categories) ?>)</h2>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>#</th><th>Ad</th><th>Slug</th><th>Yazı</th><th class="col-actions">İşlem</th></tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $row): ?>
          <tr>
            <td class="mono"><?= (int)$row['id'] ?></td>
            <td><?= e($row['name']) ?></td>
            <td class="mono"><?= e($row['slug']) ?></td>
            <td class="mono"><?= (int)$row['article_count'] ?></td>
            <td class="col-actions">
              <a class="btn btn-outline btn-sm" href="<?= e(url('admin/kategoriler.php?id=' . (int)$row['id'])) ?>">Düzenle</a>
              <form method="post" class="inline-form" action="<?= e(url('admin/kategoriler.php')) ?>"
                    onsubmit="return confirm('Kategori silinsin mi? Yazılar kategorisiz kalacak.');">
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
