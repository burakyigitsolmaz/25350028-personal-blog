<?php
/**
 * İletişim sayfası.
 * Form → CSRF + honeypot + 60 sn hız limiti → messages tablosuna INSERT (PRG).
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

blog_config_boot();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

$errors = [];
$values = ['adSoyad' => '', 'eposta' => '', 'konu' => '', 'mesaj' => ''];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {

    // 1) CSRF
    if (!csrf_check()) {
        $errors['genel'] = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    }

    // 2) Honeypot — botlar bu alanı doldurur, insanlar görmez.
    if (trim((string)($_POST['website'] ?? '')) !== '') {
        // Bot: sessizce başarılı gibi davran, hiçbir şey kaydetme.
        flash_set('success', 'Mesajınız başarıyla iletildi. Teşekkür ederim.');
        redirect('iletisim.php');
    }

    // 3) Değerleri topla
    $name    = post_str('adSoyad', 100);
    $email   = post_str('eposta', 190);
    $subject = post_str('konu', 190);
    $message = post_str('mesaj', 5000);

    $values = ['adSoyad' => $name, 'eposta' => $email, 'konu' => $subject, 'mesaj' => $message];

    if (mb_strlen($name, 'UTF-8') < 3) {
        $errors['adSoyad'] = 'Lütfen geçerli bir ad soyad giriniz.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['eposta'] = 'Geçerli bir e-posta adresi giriniz.';
    }
    if (mb_strlen($subject, 'UTF-8') < 3) {
        $errors['konu'] = 'Lütfen bir konu belirtiniz.';
    }
    if (mb_strlen($message, 'UTF-8') < 10) {
        $errors['mesaj'] = 'Mesajınız en az 10 karakter olmalıdır.';
    }

    // 4) Hız limiti: aynı IP 60 saniyede tek mesaj
    if (!$errors) {
        $limitStmt = $pdo->prepare(
            'SELECT created_at FROM messages WHERE ip_hash = ? ORDER BY created_at DESC LIMIT 1'
        );
        $limitStmt->execute([ip_hash()]);
        $last = $limitStmt->fetchColumn();

        if ($last && (time() - strtotime((string)$last)) < 60) {
            $errors['genel'] = 'Çok sık mesaj gönderiyorsunuz. Lütfen bir dakika sonra tekrar deneyin.';
        }
    }

    // 5) Kaydet
    if (!$errors) {
        $ins = $pdo->prepare(
            'INSERT INTO messages (name, email, subject, message, is_read, ip_hash, created_at)
             VALUES (?, ?, ?, ?, 0, ?, NOW())'
        );
        $ins->execute([$name, $email, $subject, $message, ip_hash()]);

        flash_set('success', 'Mesajınız başarıyla iletildi. Teşekkür ederim.');
        redirect('iletisim.php');
    }
}

$pageTitle       = 'İletişim | ' . setting('site_title_short', 'Burak Yiğit Solmaz');
$pageDescription = 'Burak Yiğit Solmaz ile iletişime geçmek için form.';

require __DIR__ . '/includes/header.php';
?>

    <section class="hero page-hero">
      <div class="container hero-inner">
        <span class="hero-line" aria-hidden="true"></span>
        <p class="hero-kicker">Bağlantı</p>
        <h1>İletişim</h1>
        <p class="hero-text">
          Aşağıdaki formu doldurarak bana resmi bir şekilde ulaşabilirsiniz.
          Mesajlarınız en kısa sürede dönüş yapılacak şekilde değerlendirilmektedir.
        </p>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="contact-grid">

          <div class="contact-info">
            <h2 class="contact-info-title">Bilgiler</h2>
            <ul class="contact-details">
              <li>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
                <a class="contact-mail" href="mailto:<?= e(setting('email', 'solmazburakss@gmail.com')) ?>">
                  <?= e(setting('email', 'solmazburakss@gmail.com')) ?>
                </a>
              </li>
              <li>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 12-9 12S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span><?= e(setting('location', 'Ankara, Türkiye')) ?></span>
              </li>
              <li>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12 .5A11.5 11.5 0 0 0 .5 12a11.5 11.5 0 0 0 7.86 10.92c.58.1.79-.25.79-.56v-2c-3.21.7-3.89-1.35-3.89-1.35-.53-1.34-1.29-1.7-1.29-1.7-1.05-.72.08-.71.08-.71 1.16.08 1.77 1.19 1.77 1.19 1.04 1.78 2.72 1.27 3.38.97.11-.75.41-1.27.74-1.56-2.56-.29-5.25-1.28-5.25-5.71 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.47.11-3.06 0 0 .97-.31 3.18 1.18a11 11 0 0 1 5.78 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.77.12 3.06.74.81 1.18 1.84 1.18 3.1 0 4.44-2.69 5.41-5.27 5.7.42.36.79 1.07.79 2.16v3.2c0 .31.2.67.8.56A11.5 11.5 0 0 0 23.5 12 11.5 11.5 0 0 0 12 .5z"/></svg>
                <a href="<?= e(setting('github_url', 'https://github.com/burakyigitsolmaz')) ?>" target="_blank" rel="noopener">
                  github.com/burakyigitsolmaz
                </a>
              </li>
              <li>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                <a href="<?= e(setting('instagram_url', 'https://www.instagram.com/burakyigitsolmaz')) ?>" target="_blank" rel="noopener">
                  instagram.com/burakyigitsolmaz
                </a>
              </li>
            </ul>
            <p class="contact-note">
              Gönderdiğiniz mesajlar veritabanına kaydedilir ve yalnızca site sahibi tarafından görüntülenir.
            </p>
          </div>

          <form class="contact-form" id="contactForm" method="post" action="<?= e(url('iletisim.php')) ?>" novalidate>
            <?= csrf_field() ?>

            <?php if (isset($errors['genel'])): ?>
              <p class="form-alert" role="alert"><?= e($errors['genel']) ?></p>
            <?php endif; ?>

            <div class="form-row">
              <div class="form-field <?= isset($errors['adSoyad']) ? 'error' : '' ?>">
                <label for="adSoyad">Ad Soyad</label>
                <input type="text" id="adSoyad" name="adSoyad" maxlength="100"
                       placeholder="Adınız ve soyadınız" required
                       value="<?= e($values['adSoyad']) ?>">
                <span class="form-error" data-for="adSoyad"><?= e($errors['adSoyad'] ?? '') ?></span>
              </div>
              <div class="form-field <?= isset($errors['eposta']) ? 'error' : '' ?>">
                <label for="eposta">E-posta</label>
                <input type="email" id="eposta" name="eposta" maxlength="190"
                       placeholder="ornek@eposta.com" required
                       value="<?= e($values['eposta']) ?>">
                <span class="form-error" data-for="eposta"><?= e($errors['eposta'] ?? '') ?></span>
              </div>
            </div>

            <div class="form-field <?= isset($errors['konu']) ? 'error' : '' ?>">
              <label for="konu">Konu</label>
              <input type="text" id="konu" name="konu" maxlength="190"
                     placeholder="Mesajınızın konusu" required
                     value="<?= e($values['konu']) ?>">
              <span class="form-error" data-for="konu"><?= e($errors['konu'] ?? '') ?></span>
            </div>

            <div class="form-field <?= isset($errors['mesaj']) ? 'error' : '' ?>">
              <label for="mesaj">Mesaj</label>
              <textarea id="mesaj" name="mesaj" rows="5" maxlength="5000"
                        placeholder="Mesajınızı yazın..." required><?= e($values['mesaj']) ?></textarea>
              <span class="form-error" data-for="mesaj"><?= e($errors['mesaj'] ?? '') ?></span>
            </div>

            <!-- Honeypot: insanlar görmez, botlar doldurur -->
            <div class="hp-field" aria-hidden="true">
              <label for="website">Web sitesi</label>
              <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary">Gönder</button>
          </form>

        </div>
      </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
