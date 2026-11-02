<?php
/**
 * Tüm yayınlanmış yazılar:
 *  - kategori filtresi  ?kategori=slug
 *  - arama              ?q= (sp_article_search prosedürü üzerinden)
 *  - sayfalama          ?sayfa=
 *  - aylık arşiv menüsü
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

blog_config_boot();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

const PER_PAGE = 6;

// --- Girdiler ---
$q            = trim((string)($_GET['q'] ?? ''));
$categorySlug = trim((string)($_GET['kategori'] ?? ''));
$tagSlug      = trim((string)($_GET['etiket'] ?? ''));
$monthParam   = trim((string)($_GET['ay'] ?? ''));   // YYYY-MM
$currentPage  = max(1, (int)($_GET['sayfa'] ?? 1));
$offset       = ($currentPage - 1) * PER_PAGE;

$monthYear = null;
$monthNum  = null;
if ($monthParam !== '' && preg_match('#^(\d{4})-(\d{2})$#', $monthParam, $mMatch)) {
    $monthYear = (int)$mMatch[1];
    $monthNum  = (int)$mMatch[2];
}

// --- Arama: stored procedure ile eşleşen yazı id'lerini topla ---
$idList = null;
if ($q !== '') {
    $stmt = $pdo->prepare('CALL sp_article_search(?)');
    $stmt->execute([mb_substr($q, 0, 100, 'UTF-8')]);
    $idList = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    while ($stmt->nextRowset()) {}
    $stmt->closeCursor();

    if (!$idList) {
        $idList = [0];   // eşleşme yok: boş sonuç kümesi
    }
}

// --- Dinamik WHERE ---
$whereParts = ['a.is_published = 1'];
$params     = [];

if ($idList !== null) {
    $whereParts[] = 'a.id IN (' . implode(',', $idList) . ')';
}
if ($categorySlug !== '') {
    $whereParts[]      = 'c.slug = :cat';
    $params[':cat']    = $categorySlug;
}
if ($tagSlug !== '') {
    $whereParts[] = 'a.id IN (SELECT at.article_id
                              FROM article_tags at
                              INNER JOIN tags t ON t.id = at.tag_id
                              WHERE t.slug = :tag)';
    $params[':tag'] = $tagSlug;
}
if ($monthYear !== null) {
    $whereParts[]        = 'YEAR(a.created_at) = :ay_y AND MONTH(a.created_at) = :ay_m';
    $params[':ay_y']     = $monthYear;
    $params[':ay_m']     = $monthNum;
}
$where = implode(' AND ', $whereParts);

// --- Toplam ---
$countStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM articles a
     LEFT JOIN categories c ON c.id = a.category_id
     WHERE $where"
);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / PER_PAGE));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * PER_PAGE;
}

// --- Sayfalık yazılar ---
$listStmt = $pdo->prepare(
    "SELECT a.id, a.slug, a.title, a.summary, a.created_at,
            c.name AS category_name, c.slug AS category_slug
     FROM articles a
     LEFT JOIN categories c ON c.id = a.category_id
     WHERE $where
     ORDER BY a.created_at DESC, a.id DESC
     LIMIT :lim OFFSET :off"
);
foreach ($params as $key => $value) {
    $listStmt->bindValue($key, $value, PDO::PARAM_STR);
}
$listStmt->bindValue(':lim', PER_PAGE, PDO::PARAM_INT);
$listStmt->bindValue(':off', $offset, PDO::PARAM_INT);
$listStmt->execute();
$articles = $listStmt->fetchAll();

// --- Kategori filtre menüsü ---
$categories = $pdo->query(
    'SELECT c.id, c.name, c.slug, COUNT(a.id) AS total
     FROM categories c
     LEFT JOIN articles a ON a.category_id = c.id AND a.is_published = 1
     GROUP BY c.id, c.name, c.slug
     ORDER BY c.name ASC'
)->fetchAll();

// --- Aylık arşiv ---
$archive = $pdo->query(
    'SELECT YEAR(created_at) AS y, MONTH(created_at) AS m, COUNT(*) AS total
     FROM articles
     WHERE is_published = 1
     GROUP BY YEAR(created_at), MONTH(created_at)
     ORDER BY y DESC, m DESC'
)->fetchAll();

// --- Sayfalama bağlantı üreticisi ---
function page_link(array $get, int $page): string
{
    $get['sayfa'] = $page;
    return '?' . http_build_query($get);
}
$linkBase = array_filter([
    'q'        => $q !== '' ? $q : null,
    'kategori' => $categorySlug !== '' ? $categorySlug : null,
    'etiket'   => $tagSlug !== '' ? $tagSlug : null,
]);

// --- Etiket filtresi menüsü ---
$allTags = $pdo->query(
    'SELECT t.name, t.slug, COUNT(a.id) AS total
     FROM tags t
     LEFT JOIN article_tags at ON at.tag_id = t.id
     LEFT JOIN articles a ON a.id = at.article_id AND a.is_published = 1
     GROUP BY t.id, t.name, t.slug
     HAVING total > 0
     ORDER BY total DESC, t.name ASC'
)->fetchAll();

// --- Aktif etiketin adı ---
$activeTagName = '';
if ($tagSlug !== '') {
    $tagNameStmt = $pdo->prepare('SELECT name FROM tags WHERE slug = ? LIMIT 1');
    $tagNameStmt->execute([$tagSlug]);
    $activeTagName = (string)($tagNameStmt->fetchColumn() ?: '');
}

require __DIR__ . '/includes/header.php';
?>

    <!-- SAYFA BAŞLIĞI -->
    <section class="hero page-hero">
      <div class="container hero-inner">
        <span class="hero-line" aria-hidden="true"></span>
        <p class="hero-kicker">Blog</p>
        <h1>Yazılar</h1>
        <?php if ($q !== ''): ?>
          <p class="hero-text">“<?= e($q) ?>” aramasına uyan <?= $totalRows ?> yazı bulundu.</p>
        <?php elseif ($activeTagName !== ''): ?>
          <p class="hero-text">“#<?= e($activeTagName) ?>” etiketli <?= $totalRows ?> yazı listeleniyor.</p>
        <?php elseif ($categorySlug !== ''): ?>
          <p class="hero-text">Seçili kategoriye ait yazılar listelenmektedir.</p>
        <?php else: ?>
          <p class="hero-text">Yazılım, oyun geliştirme ve veritabanı alanındaki tüm yazılarım.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- LİSTE + FİLTRE -->
    <section id="yazilar-liste" class="section">
      <div class="container">
        <div class="filter-grid">
          <form class="filter-search" method="get" action="<?= e(url('yazilar.php')) ?>">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Yazılarda ara..." aria-label="Yazılarda ara">
            <?php if ($categorySlug !== ''): ?>
              <input type="hidden" name="kategori" value="<?= e($categorySlug) ?>">
            <?php endif; ?>
            <?php if ($tagSlug !== ''): ?>
              <input type="hidden" name="etiket" value="<?= e($tagSlug) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Ara</button>
            <?php if ($q !== '' || $categorySlug !== '' || $tagSlug !== ''): ?>
              <a class="filter-clear" href="<?= e(url('yazilar.php')) ?>">Filtreleri temizle</a>
            <?php endif; ?>
          </form>

          <?php $catBase = array_filter(['q' => $q !== '' ? $q : null, 'etiket' => $tagSlug !== '' ? $tagSlug : null]); ?>

          <div class="filter-group">
            <h3>Kategoriler</h3>
            <ul class="filter-list">
              <li class="<?= $categorySlug === '' ? 'active' : '' ?>">
                <a href="<?= e(url('yazilar.php' . ($catBase ? '?' . http_build_query($catBase) : ''))) ?>">Tümü</a>
              </li>
              <?php foreach ($categories as $cat): ?>
                <li class="<?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                  <a href="<?= e(url('yazilar.php?' . http_build_query(array_filter([
                        'q'        => $q !== '' ? $q : null,
                        'kategori' => $cat['slug'],
                        'etiket'   => $tagSlug !== '' ? $tagSlug : null,
                    ])))) ?>">
                    <?= e($cat['name']) ?> (<?= (int)$cat['total'] ?>)
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="filter-group">
            <h3>Aylık Arşiv</h3>
            <ul class="filter-list">
              <?php foreach ($archive as $row): ?>
                <li>
                  <a href="<?= e(url('yazilar.php?' . http_build_query(array_merge($linkBase, [
                      'ay' => sprintf('%04d-%02d', $row['y'], $row['m']),
                  ])))) ?>">
                    <?= e(tr_month_year((int)$row['y'], (int)$row['m'])) ?> (<?= (int)$row['total'] ?>)
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <?php if ($allTags): ?>
            <div class="filter-group">
              <h3>Etiketler</h3>
              <ul class="tag-list">
                <?php foreach ($allTags as $tag): ?>
                  <li>
                    <a class="tag-chip <?= $tagSlug === $tag['slug'] ? 'active' : '' ?>"
                       href="<?= e(url('yazilar.php?' . http_build_query(array_filter([
                           'q'        => $q !== '' ? $q : null,
                           'kategori' => $categorySlug !== '' ? $categorySlug : null,
                           'etiket'   => $tag['slug'],
                       ])))) ?>">
                      #<?= e($tag['name']) ?> (<?= (int)$tag['total'] ?>)
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>

        <div class="posts-grid posts-main">
          <?php if (empty($articles)) : ?>
            <p class="empty-note">Aramanıza uyan yazı bulunamadı.</p>
          <?php endif; ?>
          <?php foreach ($articles as $post): ?>
            <article class="post-card">
              <div class="post-meta">
                <a class="post-tag" href="<?= e(url('yazilar.php?kategori=' . urlencode($post['category_slug'] ?? ''))) ?>">
                  <?= e($post['category_name'] ?? 'Genel') ?>
                </a>
                <time datetime="<?= e(date('Y-m-d', strtotime($post['created_at']))) ?>"><?= e(tr_date($post['created_at'])) ?></time>
              </div>
              <h3><?= e($post['title']) ?></h3>
              <p><?= e($post['summary']) ?></p>
              <a class="post-link" href="<?= e(url('yazi.php?slug=' . urlencode($post['slug']))) ?>">Devamını Oku →</a>
            </article>
          <?php endforeach; ?>
        </div>

        <div class="pagination">
          <a class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>"
             href="<?= e(url('yazilar.php' . page_link($linkBase, max(1, $currentPage - 1)))) ?>">‹ Önceki</a>
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a class="page-link <?= $p === $currentPage ? 'active' : '' ?>"
               href="<?= e(url('yazilar.php' . page_link($linkBase, $p))) ?>"><?= $p ?></a>
          <?php endfor; ?>
          <a class="page-link <?= $currentPage >= $totalPages ? 'disabled' : '' ?>"
             href="<?= e(url('yazilar.php' . page_link($linkBase, min($totalPages, $currentPage + 1)))) ?>">Sonraki ›</a>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
