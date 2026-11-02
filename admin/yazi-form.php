<?php
/**
 * Yazı ekleme / düzenleme formu.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isEdit = $id > 0;

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
$allTags    = $pdo->query('SELECT id, name FROM tags ORDER BY name ASC')->fetchAll();

$form = [
    'title'        => '',
    'slug'         => '',
    'category_id'  => '',
    'summary'      => '',
    'content'      => '',
    'cover_image'  => '',
    'is_published' => 1,
    'tags'         => [],
];

if ($isEdit) {
    $stmt = $pdo->prepare(
        'SELECT id, title, slug, category_id, summary, content, cover_image, is_published
         FROM articles WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        flash_set('error', 'Yazı bulunamadı.');
        redirect('admin/yazilar.php');
    }

    $form = $row;
    $form['tags'] = array_map('intval', $pdo->query(
        'SELECT tag_id FROM article_tags WHERE article_id = ' . (int)$id
    )->fetchAll(PDO::FETCH_COLUMN));
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {

    if (!csrf_check()) {
        $errors['genel'] = 'Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.';
    }

    $form['title']        = post_str('title', 190);
    $form['slug']         = post_str('slug', 190);
    $form['category_id']  = (string)(int)($_POST['category_id'] ?? 0);
    $form['summary']      = post_str('summary', 400);
    $form['content']      = post_str('content', 65000);
    $form['cover_image']  = post_str('cover_image', 190);
    $form['is_published'] = isset($_POST['is_published']) ? 1 : 0;
    $form['tags']         = array_map('intval', (array)($_POST['tags'] ?? []));

    if (mb_strlen($form['title'], 'UTF-8') < 3) {
        $errors['title'] = 'Başlık en az 3 karakter olmalıdır.';
    }
    if (mb_strlen($form['content'], 'UTF-8') < 20) {
        $errors['content'] = 'İçerik en az 20 karakter olmalıdır.';
    }
    if ($form['slug'] === '') {
        $form['slug'] = slugify($form['title']);
    } else {
        $form['slug'] = slugify($form['slug']);
    }

    // Slug benzersizliği
    if (!$errors) {
        $slugCheck = $pdo->prepare('SELECT id FROM articles WHERE slug = ? AND id <> ? LIMIT 1');
        $slugCheck->execute([$form['slug'], $id]);
        if ($slugCheck->fetchColumn()) {
            $base  = $form['slug'];
            $suffix = 2;
            do {
                $candidate = $base . '-' . $suffix;
                $slugCheck->execute([$candidate, $id]);
                $exists = (bool)$slugCheck->fetchColumn();
                $suffix++;
            } while ($exists && $suffix < 100);
            $form['slug'] = $candidate;
        }
    }

    if (!$errors) {
        $categoryId = (int)$form['category_id'] > 0 ? (int)$form['category_id'] : null;

        if ($isEdit) {
            $upd = $pdo->prepare(
                'UPDATE articles
                 SET title = ?, slug = ?, category_id = ?, summary = ?, content = ?,
                     cover_image = ?, is_published = ?
                 WHERE id = ?'
            );
            $upd->execute([
                $form['title'], $form['slug'], $categoryId, $form['summary'],
                $form['content'],
                $form['cover_image'] !== '' ? $form['cover_image'] : null,
                (int)$form['is_published'], $id,
            ]);
            log_activity('Yazı güncellendi', $form['title']);
            flash_set('success', 'Yazı güncellendi.');
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO articles
                   (title, slug, category_id, summary, content, cover_image, is_published, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $ins->execute([
                $form['title'], $form['slug'], $categoryId, $form['summary'],
                $form['content'],
                $form['cover_image'] !== '' ? $form['cover_image'] : null,
                (int)$form['is_published'],
            ]);
            $id = (int)$pdo->lastInsertId();
            log_activity('Yazı eklendi', $form['title']);
            flash_set('success', 'Yeni yazı eklendi.');
        }

        // Etiket bağlantılarını yenile
        $pdo->prepare('DELETE FROM article_tags WHERE article_id = ?')->execute([$id]);
        if ($form['tags']) {
            $tagIns = $pdo->prepare('INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)');
            foreach ($form['tags'] as $tagId) {
                if ($tagId > 0) {
                    $tagIns->execute([$id, $tagId]);
                }
            }
        }

        redirect('admin/yazilar.php');
    }
}

$adminTitle = $isEdit ? 'Yazıyı Düzenle' : 'Yeni Yazı';
require __DIR__ . '/includes/layout-header.php';
?>

<?php if (isset($errors['genel'])): ?>
  <div class="admin-flash admin-flash-error"><?= e($errors['genel']) ?></div>
<?php endif; ?>

<section class="panel">
  <form class="admin-form" method="post"
        action="<?= e(url('admin/yazi-form.php' . ($isEdit ? '?id=' . $id : ''))) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <div class="form-row-2">
      <div class="form-field">
        <label for="title">Başlık *</label>
        <input type="text" id="title" name="title" maxlength="190" required
               value="<?= e($form['title']) ?>">
        <?php if (isset($errors['title'])): ?>
          <span class="mono" style="color:#e63946"><?= e($errors['title']) ?></span>
        <?php endif; ?>
      </div>

      <div class="form-field">
        <label for="slug">URL Adresi (slug)</label>
        <input type="text" id="slug" name="slug" maxlength="190"
               placeholder="Boş bırakılırsa başlıktan üretilir"
               value="<?= e($form['slug']) ?>">
      </div>
    </div>

    <div class="form-row-2">
      <div class="form-field">
        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id">
          <option value="0">— Kategorisiz —</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>"
              <?= (int)$form['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-field">
        <label for="cover_image">Kapak Görseli (opsiyonel yol)</label>
        <input type="text" id="cover_image" name="cover_image" maxlength="190"
               placeholder="uploads/ornek.jpg" value="<?= e($form['cover_image'] ?? '') ?>">
      </div>
    </div>

    <div class="form-field">
      <label for="summary">Özet</label>
      <input type="text" id="summary" name="summary" maxlength="400"
             placeholder="Kartlarda görünecek kısa açıklama"
             value="<?= e($form['summary']) ?>">
    </div>

    <div class="form-field">
      <label for="content">İçerik * <span style="text-transform:none;letter-spacing:0">(paragrafları boş satırla ayırın)</span></label>
      <textarea id="content" name="content" required><?= e($form['content']) ?></textarea>
      <?php if (isset($errors['content'])): ?>
        <span class="mono" style="color:#e63946"><?= e($errors['content']) ?></span>
      <?php endif; ?>
    </div>

    <div class="form-field">
      <label>Etiketler</label>
      <div class="checkbox-grid">
        <?php foreach ($allTags as $tag): ?>
          <label>
            <input type="checkbox" name="tags[]" value="<?= (int)$tag['id'] ?>"
              <?= in_array((int)$tag['id'], array_map('intval', (array)$form['tags']), true) ? 'checked' : '' ?>>
            <?= e($tag['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-field">
      <label style="display:inline-flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0">
        <input type="checkbox" name="is_published" value="1"
          <?= (int)$form['is_published'] === 1 ? 'checked' : '' ?>>
        Yayında
      </label>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Değişiklikleri Kaydet' : 'Yazıyı Ekle' ?></button>
      <a class="btn btn-outline" href="<?= e(url('admin/yazilar.php')) ?>">Vazgeç</a>
    </div>
  </form>
</section>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
