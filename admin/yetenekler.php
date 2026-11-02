<?php
/**
 * Yetenek yönetimi (anasayfadaki yüzde çubukları).
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

$editId  = (int)($_GET['id'] ?? 0);
$editRow = ['id' => 0, 'name' => '', 'level' => 50, 'group_name' => 'Diller', 'sort_order' => 0];

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT id, name, level, group_name, sort_order FROM skills WHERE id = ? LIMIT 1');
    $stmt->execute([$editId]);
    $found = $stmt->fetch();
    if ($found) {
        $editRow = $found;
    } else {
        flash_set('error', 'Yetenek bulunamadı.');
        redirect('admin/yetenekler.php');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Güvenlik doğrulaması başarısız.');
        redirect('admin/yetenekler.php');
    }

    $action = (string)($_POST['action'] ?? 'save');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        $chk = $pdo->prepare('SELECT name FROM skills WHERE id = ? LIMIT 1');
        $chk->execute([$id]);
        $name = $chk->fetchColumn();

        if ($name !== false) {
            $pdo->prepare('DELETE FROM skills WHERE id = ?')->execute([$id]);
            log_activity('Yetenek silindi', (string)$name);
            flash_set('success', 'Yetenek silindi.');
        } else {
            flash_set('error', 'Yetenek bulunamadı.');
        }
        redirect('admin/yetenekler.php');
    }

    $name      = post_str('name', 80);
    $groupName = post_str('group_name', 50);
    $level     = max(0, min(100, (int)($_POST['level'] ?? 0)));
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (mb_strlen($name, 'UTF-8') < 2) {
        flash_set('error', 'Yetenek adı en az 2 karakter olmalıdır.');
        redirect('admin/yetenekler.php');
    }
    if ($groupName === '') {
        $groupName = 'Diller';
    }

    if ($id > 0) {
        $pdo->prepare(
            'UPDATE skills SET name = ?, level = ?, group_name = ?, sort_order = ? WHERE id = ?'
        )->execute([$name, $level, $groupName, $sortOrder, $id]);
        log_activity('Yetenek güncellendi', $name);
        flash_set('success', 'Yetenek güncellendi.');
    } else {
        $pdo->prepare(
            'INSERT INTO skills (name, level, group_name, sort_order) VALUES (?, ?, ?, ?)'
        )->execute([$name, $level, $groupName, $sortOrder]);
        log_activity('Yetenek eklendi', $name);
        flash_set('success', 'Yetenek eklendi.');
    }

    redirect('admin/yetenekler.php');
}

$skills = $pdo->query(
    'SELECT id, name, level, group_name, sort_order
     FROM skills ORDER BY group_name ASC, sort_order ASC, id ASC'
)->fetchAll();

$adminTitle = 'Yetenekler';
require __DIR__ . '/includes/layout-header.php';
?>

<section class="panel">
  <h2><?= $editRow['id'] > 0 ? 'Yeteneği Düzenle' : 'Yeni Yetenek' ?></h2>
  <form class="admin-form" method="post" action="<?= e(url('admin/yetenekler.php')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

    <div class="form-row-2">
      <div class="form-field">
        <label for="name">Yetenek Adı *</label>
        <input type="text" id="name" name="name" maxlength="80" required value="<?= e($editRow['name']) ?>">
      </div>
      <div class="form-field">
        <label for="group_name">Grup</label>
        <input type="text" id="group_name" name="group_name" maxlength="50"
               list="group-list" value="<?= e($editRow['group_name']) ?>">
        <datalist id="group-list">
          <?php foreach (array_unique(array_column($skills, 'group_name')) as $g): ?>
            <option value="<?= e($g) ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
    </div>

    <div class="form-row-2">
      <div class="form-field">
        <label for="level">Seviye (0-100)</label>
        <input type="number" id="level" name="level" min="0" max="100"
               value="<?= (int)$editRow['level'] ?>">
      </div>
      <div class="form-field">
        <label for="sort_order">Sıra</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= (int)$editRow['sort_order'] ?>">
      </div>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editRow['id'] > 0 ? 'Güncelle' : 'Ekle' ?></button>
      <?php if ($editRow['id'] > 0): ?>
        <a class="btn btn-outline" href="<?= e(url('admin/yetenekler.php')) ?>">Vazgeç</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h2>Yetenekler (<?= count($skills) ?>)</h2>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>#</th><th>Ad</th><th>Grup</th><th>Seviye</th><th>Sıra</th><th class="col-actions">İşlem</th></tr>
      </thead>
      <tbody>
        <?php foreach ($skills as $row): ?>
          <tr>
            <td class="mono"><?= (int)$row['id'] ?></td>
            <td><?= e($row['name']) ?></td>
            <td><?= e($row['group_name']) ?></td>
            <td class="mono">%<?= (int)$row['level'] ?></td>
            <td class="mono"><?= (int)$row['sort_order'] ?></td>
            <td class="col-actions">
              <a class="btn btn-outline btn-sm" href="<?= e(url('admin/yetenekler.php?id=' . (int)$row['id'])) ?>">Düzenle</a>
              <form method="post" class="inline-form" action="<?= e(url('admin/yetenekler.php')) ?>"
                    onsubmit="return confirm('Yetenek silinsin mi?');">
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
