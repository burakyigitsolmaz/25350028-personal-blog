<?php
/**
 * Yönetim paneli giriş ekranı.
 * Şifreler password_hash() ile saklanır, doğrulama password_verify() ile yapılır.
 */

require_once __DIR__ . '/includes/auth.php';

// Zaten giriş yapılmışsa panele gönder
if (current_admin() !== null) {
    redirect('admin/index.php');
}

$error    = '';
$username = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {

    // Basit kaba kuvvet koruması (oturum bazlı)
    $attempts = (int)($_SESSION['login_attempts'] ?? 0);
    $lockedAt = (int)($_SESSION['login_locked_at'] ?? 0);

    if ($attempts >= 5 && (time() - $lockedAt) < 60) {
        $error = 'Çok fazla hatalı deneme. Lütfen bir dakika sonra tekrar deneyin.';
    } elseif (!csrf_check()) {
        $error = 'Güvenlik doğrulaması başarısız oldu. Sayfayı yenileyip tekrar deneyin.';
    } else {
        $username = post_str('username', 50);
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Kullanıcı adı ve şifre zorunludur.';
        } else {
            $stmt = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, (string)$admin['password_hash'])) {
                // Başarılı giriş
                session_regenerate_id(true);
                $_SESSION['admin_id']       = (int)$admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                unset($_SESSION['login_attempts'], $_SESSION['login_locked_at']);

                log_activity('Giriş', 'Yönetim paneline giriş yapıldı.');

                flash_set('success', 'Hoş geldiniz, ' . $admin['username'] . '.');
                redirect('admin/index.php');
            }

            // Başarısız giriş
            usleep(400000);
            $_SESSION['login_attempts'] = $attempts + 1;
            $_SESSION['login_locked_at'] = time();
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Yönetici Girişi | Kişisel Blog</title>
  <link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body class="login-page">

  <main class="login-card">
    <h1>Yönetici Girişi</h1>
    <p class="login-sub">Kişisel blog yönetim paneline erişim.</p>

    <?php if ($error !== ''): ?>
      <p class="login-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= e(url('admin/login.php')) ?>" autocomplete="off">
      <?= csrf_field() ?>

      <div class="form-field">
        <label for="username">Kullanıcı Adı</label>
        <input type="text" id="username" name="username" maxlength="50"
               value="<?= e($username) ?>" required autofocus>
      </div>

      <div class="form-field">
        <label for="password">Şifre</label>
        <input type="password" id="password" name="password" maxlength="200" required>
      </div>

      <button type="submit" class="btn btn-primary">Giriş Yap</button>
    </form>

    <a class="login-back" href="<?= e(url()) ?>">← Siteye dön</a>
  </main>

</body>
</html>
