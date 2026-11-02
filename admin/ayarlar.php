<?php
/**
 * Site ayarları (settings tablosu — anahtar/değer).
 * Hakkımda metni, iletişim bilgileri ve sosyal bağlantılar buradan yönetilir.
 */

require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

// Yalnızca bu anahtarlar düzenlenebilir (whitelist).
$editable = [
    'site_title'       => ['label' => 'Site Başlığı (sekme)',      'type' => 'text'],
    'site_title_short' => ['label' => 'Kısa Ad (logo/footer)',     'type' => 'text'],
    'site_description' => ['label' => 'Site Açıklaması (meta)',    'type' => 'textarea'],
    'hero_text'        => ['label' => 'Giriş (Hero) Metni',        'type' => 'textarea'],
    'about_text'       => ['label' => 'Hakkımda Metni (paragrafları boş satırla ayırın)', 'type' => 'textarea'],
    'email'            => ['label' => 'E-posta',                   'type' => 'email'],
    'github_url'       => ['label' => 'GitHub Adresi',             'type' => 'url'],
    'linkedin_url'     => ['label' => 'LinkedIn Adresi',           'type' => 'url'],
    'instagram_url'    => ['label' => 'Instagram Adresi',          'type' => 'url'],
    'cv_path'          => ['label' => 'CV Dosya Yolu',             'type' => 'text'],
    'location'         => ['label' => 'Konum',                     'type' => 'text'],
    'student_no'       => ['label' => 'Öğrenci Numarası',          'type' => 'text'],
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Güvenlik doğrulaması başarısız.');
        redirect('admin/ayarlar.php');
    }

    $errors = [];

    foreach ($editable as $key => $meta) {
        $value = post_str('setting_' . $key, 6000);

        if ($meta['type'] === 'url' && $value !== '' && !preg_match('#^https?://#i', $value)) {
            $errors[] = $meta['label'] . ' http:// veya https:// ile başlamalıdır.';
        }
        if ($meta['type'] === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi giriniz.';
        }
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect('admin/ayarlar.php');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );

    foreach (array_keys($editable) as $key) {
        $stmt->execute([$key, post_str('setting_' . $key, 6000)]);
    }

    log_activity('Site ayarları güncellendi', count($editable) . ' anahtar');
    flash_set('success', 'Ayarlar kaydedildi.');
    redirect('admin/ayarlar.php');
}

$values = $pdo->query('SELECT setting_key, setting_value FROM settings')
    ->fetchAll(PDO::FETCH_KEY_PAIR);

$adminTitle = 'Site Ayarları';
require __DIR__ . '/includes/layout-header.php';
?>

<section class="panel">
  <h2>Genel İçerik ve İletişim</h2>

  <form class="admin-form" method="post" action="<?= e(url('admin/ayarlar.php')) ?>">
    <?= csrf_field() ?>

    <?php foreach ($editable as $key => $meta): ?>
      <div class="form-field">
        <label for="setting_<?= e($key) ?>"><?= e($meta['label']) ?></label>

        <?php if ($meta['type'] === 'textarea'): ?>
          <textarea id="setting_<?= e($key) ?>" name="setting_<?= e($key) ?>"
                    style="min-height:120px"><?= e($values[$key] ?? '') ?></textarea>
        <?php else: ?>
          <input type="<?= e($meta['type']) ?>" id="setting_<?= e($key) ?>"
                 name="setting_<?= e($key) ?>" maxlength="6000"
                 value="<?= e($values[$key] ?? '') ?>">
        <?php endif; ?>

        <span class="mono"><?= e($key) ?></span>
      </div>
    <?php endforeach; ?>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Ayarları Kaydet</button>
      <a class="btn btn-outline" href="<?= e(url()) ?>" target="_blank" rel="noopener">Siteyi Kontrol Et ↗</a>
    </div>
  </form>
</section>

<section class="panel">
  <h2>CV İndirme Sayısı</h2>
  <?php
    $downloadTotal = (int)$pdo->query('SELECT COUNT(*) FROM downloads')->fetchColumn();
  ?>
  <p style="color:#a2a2ad;font-size:.9rem">
    <span class="badge"><?= $downloadTotal ?> indirme</span>
    — CV dosyası <span class="mono"><?= e($values['cv_path'] ?? 'assets/cv/Burak_Yigit_Solmaz_CV.pdf') ?></span>
    yolundan sunulur. Dosyayı değiştirmek için aynı ada sahip PDF'i bu klasöre kopyalayın.
  </p>
</section>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
