<?php
/**
 * Yazı detay sayfası.
 *  - okunma sayacı (oturum başına 1 kez)
 *  - etiketler
 *  - beğeni (POST + CSRF, IP başına tek)
 *  - ilgili yazılar
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

blog_config_boot();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo  = db();
$slug = trim((string)($_GET['slug'] ?? ''));

if ($slug === '') {
    page_not_found();
}

$stmt = $pdo->prepare('SELECT * FROM v_article_list WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    page_not_found();
}

$articleId = (int)$article['id'];

// --- Okunma sayacı: aynı oturumda bir kez ---
$alreadyViewed = isset($_SESSION['viewed'][$articleId]);
if (!$alreadyViewed) {
    $upd = $pdo->prepare('UPDATE articles SET view_count = view_count + 1 WHERE id = ?');
    $upd->execute([$articleId]);
    $_SESSION['viewed'][$articleId] = 1;
    $article['view_count'] = (int)$article['view_count'] + 1;
}

// --- Etiketler ---
$tagStmt = $pdo->prepare(
    'SELECT t.name, t.slug
     FROM tags t
     INNER JOIN article_tags at ON at.tag_id = t.id
     WHERE at.article_id = ?
     ORDER BY t.name ASC'
);
$tagStmt->execute([$articleId]);
$articleTags = $tagStmt->fetchAll();

// --- Beğeni sayısı ---
$likeStmt = $pdo->prepare('SELECT COUNT(*) FROM article_likes WHERE article_id = ?');
$likeStmt->execute([$articleId]);
$likeCount = (int)$likeStmt->fetchColumn();

// --- Bu ziyaretçi beğenmiş mi? ---
$mineStmt = $pdo->prepare('SELECT 1 FROM article_likes WHERE article_id = ? AND ip_hash = ? LIMIT 1');
$mineStmt->execute([$articleId, ip_hash()]);
$alreadyLiked = (bool)$mineStmt->fetchColumn();

// --- İlgili yazılar (aynı kategori, kendisi hariç) ---
$related = [];
if (!empty($article['category_id'])) {
    $relStmt = $pdo->prepare(
        'SELECT id, slug, title, summary, created_at
         FROM v_article_list
         WHERE category_id = ? AND id <> ?
         ORDER BY created_at DESC
         LIMIT 3'
    );
    $relStmt->execute([(int)$article['category_id'], $articleId]);
    $related = $relStmt->fetchAll();
}

$pageTitle       = $article['title'] . ' | ' . setting('site_title_short', 'Burak Yiğit Solmaz');
$pageDescription = $article['summary'] !== '' ? $article['summary'] : mb_substr(strip_tags($article['content']), 0, 155, 'UTF-8');

$paragraphs = preg_split('/\n\s*\n+/', (string)$article['content']) ?: [];

$readingMinutes = max(1, (int)ceil(mb_strlen(strip_tags((string)$article['content']), 'UTF-8') / 900));

require __DIR__ . '/includes/header.php';
?>

    <article class="section article-page">
      <div class="container article-container">

        <p class="article-back">
          <a href="<?= e(url('yazilar.php')) ?>">← Tüm yazılar</a>
        </p>

        <header class="article-head">
          <div class="post-meta">
            <?php if (!empty($article['category_name'])): ?>
              <a class="post-tag" href="<?= e(url('yazilar.php?kategori=' . urlencode((string)$article['category_slug']))) ?>">
                <?= e($article['category_name']) ?>
              </a>
            <?php endif; ?>
            <time datetime="<?= e(date('Y-m-d', strtotime((string)$article['created_at']))) ?>">
              <?= e(tr_date((string)$article['created_at'])) ?>
            </time>
          </div>

          <h1><?= e($article['title']) ?></h1>

          <?php if ($article['summary'] !== ''): ?>
            <p class="article-lede"><?= e($article['summary']) ?></p>
          <?php endif; ?>

          <ul class="article-stats">
            <li title="Okunma sayısı">👁 <?= (int)$article['view_count'] ?> okunma</li>
            <li title="Beğeni sayısı">♥ <?= $likeCount ?> beğeni</li>
            <li title="Tahmini okuma süresi">⏱ <?= $readingMinutes ?> dk okuma</li>
          </ul>
        </header>

        <div class="article-body">
          <?php foreach ($paragraphs as $paragraph): ?>
            <?php if (trim($paragraph) !== ''): ?>
              <p><?= e($paragraph) ?></p>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <?php if ($articleTags): ?>
          <div class="article-tags">
            <h2 class="article-tags-title">Etiketler</h2>
            <ul class="tag-list">
              <?php foreach ($articleTags as $tag): ?>
                <li>
                  <a class="tag-chip" href="<?= e(url('yazilar.php?etiket=' . urlencode((string)$tag['slug']))) ?>">
                    #<?= e($tag['name']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="article-actions">
          <form method="post" action="<?= e(url('like.php')) ?>" class="like-form">
            <?= csrf_field() ?>
            <input type="hidden" name="article_id" value="<?= $articleId ?>">
            <input type="hidden" name="slug" value="<?= e($article['slug']) ?>">
            <button type="submit" class="btn <?= $alreadyLiked ? 'btn-outline' : 'btn-primary' ?>"
                    <?= $alreadyLiked ? 'disabled' : '' ?>>
              <?= $alreadyLiked ? '♥ Beğendiniz' : '♡ Bu yazıyı beğen' ?>
            </button>
            <span class="like-count"><?= $likeCount ?> beğeni</span>
          </form>

          <a class="btn btn-outline" href="<?= e(url('iletisim.php')) ?>">Görüşünüzü iletin</a>
        </div>

      </div>
    </article>

    <?php if ($related): ?>
      <section class="section section-alt">
        <div class="container">
          <div class="section-head">
            <h2>İlgili Yazılar</h2>
            <span class="section-line" aria-hidden="true"></span>
          </div>
          <div class="posts-grid">
            <?php foreach ($related as $post): ?>
              <article class="post-card">
                <div class="post-meta">
                  <span class="post-tag"><?= e($article['category_name'] ?? 'Genel') ?></span>
                  <time datetime="<?= e(date('Y-m-d', strtotime((string)$post['created_at']))) ?>">
                    <?= e(tr_date((string)$post['created_at'])) ?>
                  </time>
                </div>
                <h3><?= e($post['title']) ?></h3>
                <p><?= e($post['summary']) ?></p>
                <a class="post-link" href="<?= e(url('yazi.php?slug=' . urlencode((string)$post['slug']))) ?>">Devamını Oku →</a>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
