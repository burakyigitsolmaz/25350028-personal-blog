<?php
/**
 * Proje (GitHub deposu) yönetimi.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

$editId  = (int)($_GET['id'] ?? 0);
$editRow = [
    'id' => 0, 'title' => '', 'description' => '', 'tech_stack' => '',
    'repo_url' => '', 'demo_url' => '', 'sort_order' => 0, 'is_visible' => 1,
];

if ($editId > 0) {
    $stmt = $pdo->prepare(
        'SELECT id, title, description, tech_stack, repo_url, demo_url, sort_order, is_visible
         FROM projects WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$editId]);
    $found = $stmt->fetch();
    if ($found) {
        $editRow = $found;
    } else {
        flash_set('error', 'Proje bulunamadı.');
        redirect('admin/projeler.php');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Güvenlik doğrulaması başarısız.');
        redirect('admin/projeler.php');
    }

    $action = (string)($_POST['action'] ?? 'save');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete') {
        $chk = $pdo->prepare('SELECT title FROM projects WHERE id = ? LIMIT 1');
        $chk->execute([$id]);
        $title = $chk->fetchColumn();

        if ($title !== false) {
            $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
            log_activity('Proje silindi', (string)$title);
            flash_set('success', 'Proje silindi.');
        } else {
            flash_set('error', 'Proje bulunamadı.');
        }
        redirect('admin/projeler.php');
    }

    if ($action === 'toggle') {
        $pdo->prepare('UPDATE projects SET is_visible = 1 - is_visible WHERE id = ?')->execute([$id]);
        log_activity('Proje görünürlüğü değişti', 'ID: ' . $id);
        flash_set('success', 'Proje görünürlüğü güncellendi.');
        redirect('admin/projeler.php');
    }

    $title      = post_str('title', 120);
    $description = post_str('description', 500);
    $techStack  = post_str('tech_stack', 190);
    $repoUrl    = post_str('repo_url', 300);
    $demoUrl    = post_str('demo_url', 300);
    $sortOrder  = (int)($_POST['sort_order'] ?? 0);
    $isVisible  = isset($_POST['is_visible']) ? 1 : 0;

    if (mb_strlen($title, 'UTF-8') < 2) {
        flash_set('error', 'Proje adı en az 2 karakter olmalıdır.');
        redirect('admin/projeler.php');
    }

    // Yalnızca http(s) bağlantılarına izin ver (javascript: gibi şemalar engellenir)
    foreach ([$repoUrl, $demoUrl] as $link) {
        if ($link !== '' && !preg_match('#^https?://#i', $link)) {
            flash_set('error', 'Bağlantılar http:// veya https:// ile başlamalıdır.');
            redirect('admin/projeler.php');
        }
    }

    if ($id > 0) {
        $upd = $pdo->prepare(
            'UPDATE projects
             SET title = ?, description = ?, tech_stack = ?, repo_url = ?, demo_url = ?,
                 sort_order = ?, is_visible = ?
             WHERE id = ?'
        );
        $upd->execute([
            $title, $description, $techStack, $repoUrl,
            $demoUrl !== '' ? $demoUrl : null, $sortOrder, $isVisible, $id,
        ]);
        log_activity('Proje güncellendi', $title);
        flash_set('success', 'Proje güncellendi.');
    } else {
        $ins = $pdo->prepare(
            'INSERT INTO projects (title, description, tech_stack, repo_url, demo_url, sort_order, is_visible)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $ins->execute([
            $title, $description, $techStack, $repoUrl,
            $demoUrl !== '' ? $demoUrl : null, $sortOrder, $isVisible,
        ]);
        log_activity('Proje eklendi', $title);
        flash_set('success', 'Proje eklendi.');
    }

    redirect('admin/projeler.php');
}

$projects = $pdo->query(
    'SELECT id, title, tech_stack, repo_url, sort_order, is_visible
     FROM projects ORDER BY sort_order ASC, id ASC'
)->fetchAll();

$adminTitle = 'Projeler';
require __DIR__ . '/includes/layout-header.php';
?>

<section class="panel">
  <h2><?= $editRow['id'] > 0 ? 'Projeyi Düzenle' : 'Yeni Proje (GitHub Deposu)' ?></h2>
  <form class="admin-form" method="post" action="<?= e(url('admin/projeler.php')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">

    <div class="form-row-2">
      <div class="form-field">
        <label for="title">Proje / Depo Adı *</label>
        <input type="text" id="title" name="title" maxlength="120" required value="<?= e($editRow['title']) ?>">
      </div>
      <div class="form-field">
        <label for="tech_stack">Kullanılan Teknolojiler</label>
        <input type="text" id="tech_stack" name="tech_stack" maxlength="190"
               placeholder="HTML5 · CSS3 · JavaScript" value="<?= e($editRow['tech_stack']) ?>">
      </div>
    </div>

    <div class="form-field">
      <label for="description">Açıklama</label>
      <input type="text" id="description" name="description" maxlength="500" value="<?= e($editRow['description']) ?>">
    </div>

    <div class="form-row-2">
      <div class="form-field">
        <label for="repo_url">GitHub Deposu (URL)</label>
        <input type="text" id="repo_url" name="repo_url" maxlength="300"
               placeholder="https://github.com/kullanici/repo" value="<?= e($editRow['repo_url']) ?>">
      </div>
      <div class="form-field">
        <label for="demo_url">Canlı Demo (opsiyonel)</label>
        <input type="text" id="demo_url" name="demo_url" maxlength="300"
               value="<?= e($editRow['demo_url'] ?? '') ?>">
      </div>
    </div>

    <div class="form-row-2">
      <div class="form-field">
        <label for="sort_order">Sıra</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= (int)$editRow['sort_order'] ?>">
      </div>
      <div class="form-field">
        <label style="display:inline-flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0;margin-top:32px">
          <input type="checkbox" name="is_visible" value="1"
            <?= (int)$editRow['is_visible'] === 1 ? 'checked' : '' ?>>
          Sitede görünsün
        </label>
      </div>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editRow['id'] > 0 ? 'Güncelle' : 'Ekle' ?></button>
      <?php if ($editRow['id'] > 0): ?>
        <a class="btn btn-outline" href="<?= e(url('admin/projeler.php')) ?>">Vazgeç</a>
      <?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h2>Projeler (<?= count($projects) ?>)</h2>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>#</th><th>Ad</th><th>Teknoloji</th><th>Sıra</th><th>Durum</th><th class="col-actions">İşlem</th></tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $row): ?>
          <tr>
            <td class="mono"><?= (int)$row['id'] ?></td>
            <td>
              <?= e($row['title']) ?><br>
              <span class="mono"><?= e($row['repo_url']) ?></span>
            </td>
            <td><?= e($row['tech_stack']) ?></td>
            <td class="mono"><?= (int)$row['sort_order'] ?></td>
            <td>
              <?php if ((int)$row['is_visible'] === 1): ?>
                <span class="badge badge-success">Görünür</span>
              <?php else: ?>
                <span class="badge badge-warn">Gizli</span>
              <?php endif; ?>
            </td>
            <td class="col-actions">
              <a class="btn btn-outline btn-sm" href="<?= e(url('admin/projeler.php?id=' . (int)$row['id'])) ?>">Düzenle</a>

              <form method="post" class="inline-form" action="<?= e(url('admin/projeler.php')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm">
                  <?= (int)$row['is_visible'] === 1 ? 'Gizle' : 'Göster' ?>
                </button>
              </form>

              <form method="post" class="inline-form" action="<?= e(url('admin/projeler.php')) ?>"
                    onsubmit="return confirm('Proje silinsin mi?');">
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
