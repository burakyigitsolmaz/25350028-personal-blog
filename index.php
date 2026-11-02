<?php
/**
 * Ana sayfa: hero + CV indir, hakkımda (ayarlar),
 * son 3 yazı (DB), yetenekler (DB), projeler kısa bağlantı.
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

blog_config_boot();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

// Son 3 yayınlanmış yazı
$lastPosts = $pdo->query(
    "SELECT a.id, a.slug, a.title, a.summary, a.created_at, c.name AS category_name
     FROM v_article_list a
     LEFT JOIN categories c ON c.id = a.category_id
     ORDER BY a.created_at DESC
     LIMIT 3"
)->fetchAll();

// Yetenek grupları
$skills = $pdo->query(
    'SELECT name, level, group_name FROM skills ORDER BY group_name ASC, sort_order ASC'
)->fetchAll();
$skillGroups = [];
foreach ($skills as $skill) {
    $skillGroups[$skill['group_name']][] = $skill;
}

require __DIR__ . '/includes/header.php';
?>

    <!-- HERO -->
    <section id="hero" class="hero">
      <span class="hero-glow hero-glow-1" aria-hidden="true"></span>
      <span class="hero-glow hero-glow-2" aria-hidden="true"></span>

      <div class="container hero-grid">
        <div class="hero-copy">
          <span class="hero-line" aria-hidden="true"></span>
          <p class="hero-kicker">Kişisel Blog ve Tanıtım Sitesi</p>
          <h1>Burak Yiğit Solmaz<span class="hero-dot">.</span></h1>
          <p class="hero-title">Bilgisayar Programcılığı Öğrencisi</p>
          <p class="hero-text"><?= e(setting('hero_text')) ?></p>
          <div class="hero-actions">
            <a href="<?= e(url('yazilar.php')) ?>" class="btn btn-primary">Yazılarım</a>
            <a href="<?= e(url('download.php')) ?>" class="btn btn-outline">CV İndir (PDF)</a>
            <a href="<?= e(url('iletisim.php')) ?>" class="btn btn-outline">İletişim</a>
          </div>
          <ul class="hero-chips" aria-label="Öne çıkan teknolojiler">
            <li>PHP</li>
            <li>MySQL</li>
            <li>C#</li>
            <li>Unity</li>
            <li>Python</li>
          </ul>
        </div>

        <aside class="hero-terminal" aria-label="Kod kartı">
          <div class="terminal-bar" aria-hidden="true">
            <span class="dot-red"></span>
            <span class="dot-yellow"></span>
            <span class="dot-green"></span>
            <em>whoami.php</em>
          </div>
          <pre class="terminal-body"><code><span class="t-comment">// merhaba, ben Burak</span>
<span class="t-key">$</span> whoami
<span class="t-out">Burak Yiğit Solmaz</span>
<span class="t-key">$</span> cat skills.json
{
  <span class="t-str">"diller"</span>: [<span class="t-str">"C"</span>, <span class="t-str">"C++"</span>, <span class="t-str">"C#"</span>, <span class="t-str">"Python"</span>],
  <span class="t-str">"web"</span>: [<span class="t-str">"HTML5"</span>, <span class="t-str">"CSS3"</span>, <span class="t-str">"JavaScript"</span>],
  <span class="t-str">"veritabani"</span>: [<span class="t-str">"MySQL"</span>, <span class="t-str">"MSSQL"</span>],
  <span class="t-str">"araclar"</span>: [<span class="t-str">"Unity 6"</span>, <span class="t-str">"Blender"</span>, <span class="t-str">"Git"</span>]
}
<span class="t-key">$</span> <span class="t-cursor">▌</span></code></pre>
        </aside>
      </div>
    </section>

    <!-- HAKKIMDA -->
    <section id="hakkimda" class="section">
      <div class="container">
        <div class="section-head">
          <h2>Hakkımda</h2>
          <span class="section-line" aria-hidden="true"></span>
        </div>
        <div class="about-grid">
          <div class="about-text">
            <?php foreach (preg_split('/\n\n+/', setting('about_text')) as $paragraph): ?>
              <?php if (trim($paragraph) !== ''): ?>
                <p><?= e($paragraph) ?></p>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <aside class="info-card" aria-label="Kısa bilgi kartı">
            <h3>Kısa Bilgiler</h3>
            <ul>
              <li><strong>Ad Soyad:</strong> Burak Yiğit Solmaz</li>
              <li><strong>Okul:</strong> Ankara Üniversitesi Nallıhan MYO</li>
              <li><strong>Bölüm:</strong> Bilgisayar Programcılığı</li>
              <li><strong>Sınıf:</strong> 2. Sınıf</li>
              <li><strong>Konum:</strong> <?= e(setting('location', 'Ankara, Türkiye')) ?></li>
            </ul>
          </aside>
        </div>
      </div>
    </section>

    <!-- YAZILAR -->
    <section id="yazilar" class="section section-alt">
      <div class="container">
        <div class="section-head">
          <h2>Son Yazılar</h2>
          <span class="section-line" aria-hidden="true"></span>
        </div>
        <?php if (empty($lastPosts)): ?>
          <p class="empty-note">Henüz yayınlanmış yazı yok.</p>
        <?php else: ?>
          <div class="posts-grid">
            <?php foreach ($lastPosts as $post): ?>
              <?php $articleDate = tr_date($post['created_at']); ?>
              <article class="post-card">
                <div class="post-meta">
                  <span class="post-tag"><?= e($post['category_name'] ?? 'Genel') ?></span>
                  <time datetime="<?= e(date('Y-m-d', strtotime($post['created_at']))) ?>"><?= e($articleDate) ?></time>
                </div>
                <h3><?= e($post['title']) ?></h3>
                <p><?= e($post['summary']) ?></p>
                <a class="post-link" href="<?= e(url('yazi.php?slug=' . urlencode($post['slug']))) ?>">Devamını Oku →</a>
              </article>
            <?php endforeach; ?>
          </div>
          <p class="section-more">
            <a class="btn btn-outline" href="<?= e(url('yazilar.php')) ?>">Tüm Yazılar →</a>
          </p>
        <?php endif; ?>
      </div>
    </section>

    <!-- YETENEKLER -->
    <section id="yetenekler" class="section">
      <div class="container">
        <div class="section-head">
          <h2>Yetenekler</h2>
          <span class="section-line" aria-hidden="true"></span>
        </div>
        <div class="skills-grid">
          <?php foreach ($skillGroups as $groupName => $groupSkills): ?>
            <div class="skill-group">
              <h3><?= e($groupName) ?></h3>
              <?php foreach ($groupSkills as $skill): ?>
                <div class="skill">
                  <div class="skill-head">
                    <span><?= e($skill['name']) ?></span>
                    <span class="skill-pct" data-pct="<?= (int)$skill['level'] ?>">%0</span>
                  </div>
                  <div class="skill-bar"><div class="skill-fill" data-fill="<?= (int)$skill['level'] ?>"></div></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- PROJELER (kısa bağlantı) -->
    <section id="projeler" class="section section-alt">
      <div class="container">
        <div class="section-head">
          <h2>Projeler</h2>
          <span class="section-line" aria-hidden="true"></span>
        </div>
        <div class="projects-teaser">
          <p class="projects-teaser-text">
            HTML/CSS/JS alıştırmaları, C++ pratik projeleri, veritabanı ders çalışmaları ve
            deneysel denemelerim GitHub hesabımda sürüm kontrolüyle paylaşılmaktadır.
          </p>
          <div class="projects-teaser-actions">
            <a class="btn btn-primary" href="<?= e(url('projeler.php')) ?>">Projeleri İncele →</a>
            <a class="btn btn-outline" href="<?= e(setting('github_url')) ?>" target="_blank" rel="noopener">GitHub Profilim</a>
          </div>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
