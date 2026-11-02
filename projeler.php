<?php
/**
 * Projeler sayfası — GitHub depoları veritabanından listelenir.
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

blog_config_boot();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$projects = $pdo->query(
    'SELECT id, title, description, tech_stack, repo_url, demo_url, image
     FROM projects
     WHERE is_visible = 1
     ORDER BY sort_order ASC, id ASC'
)->fetchAll();

$pageTitle       = 'Projeler | ' . setting('site_title_short', 'Burak Yiğit Solmaz');
$pageDescription = 'GitHub üzerinde sürüm kontrolüyle paylaştığım yazılım projeleri ve ders çalışmaları.';

require __DIR__ . '/includes/header.php';
?>

    <section class="hero page-hero">
      <div class="container hero-inner">
        <span class="hero-line" aria-hidden="true"></span>
        <p class="hero-kicker">Portfolyo</p>
        <h1>Projeler</h1>
        <p class="hero-text">
          Çalışmalarımı GitHub üzerinde sürüm kontrolüyle paylaşıyorum. Aşağıdaki depolar
          doğrudan GitHub hesabıma bağlıdır.
        </p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="<?= e(setting('github_url', 'https://github.com/burakyigitsolmaz')) ?>"
             target="_blank" rel="noopener">Tümünü GitHub'da Gör</a>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <h2>Depolar</h2>
          <span class="section-line" aria-hidden="true"></span>
        </div>

        <?php if (empty($projects)): ?>
          <p class="empty-note">Henüz görüntülenecek proje eklenmemiş.</p>
        <?php else: ?>
          <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
              <article class="project-card">
                <h3><?= e($project['title']) ?></h3>
                <p class="project-desc"><?= e($project['description']) ?></p>

                <?php if ($project['tech_stack'] !== ''): ?>
                  <p class="project-stack"><?= e($project['tech_stack']) ?></p>
                <?php endif; ?>

                <div class="project-actions">
                  <?php if ($project['repo_url'] !== ''): ?>
                    <a class="btn btn-outline btn-sm" href="<?= e($project['repo_url']) ?>"
                       target="_blank" rel="noopener">Repoyu Aç →</a>
                  <?php endif; ?>
                  <?php if (!empty($project['demo_url'])): ?>
                    <a class="btn btn-primary btn-sm" href="<?= e($project['demo_url']) ?>"
                       target="_blank" rel="noopener">Canlı Demo</a>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
