<?php
/**
 * Yönetim paneli oturum ve yetki katmanı.
 * Her admin sayfasının en başında require edilir.
 */

require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

blog_config_boot();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Oturumdaki yöneticiyi döndürür (yoksa null). */
function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    static $admin = null;
    if ($admin !== null) {
        return $admin;
    }

    $stmt = db()->prepare('SELECT id, username, created_at FROM admins WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['admin_id']]);
    $row = $stmt->fetch();

    if (!$row) {
        unset($_SESSION['admin_id'], $_SESSION['admin_username']);
        return null;
    }

    $admin = $row;
    return $admin;
}

/** Giriş yapılmamışsa login sayfasına gönderir. */
function require_login(): array
{
    $admin = current_admin();
    if ($admin === null) {
        flash_set('error', 'Bu sayfayı görüntülemek için giriş yapmalısınız.');
        redirect('admin/login.php');
    }
    return $admin;
}

/** Yönetici işlem günlüğü. */
function log_activity(string $action, string $detail = ''): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO activity_log (admin_id, action, detail, created_at) VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([
            (int)($_SESSION['admin_id'] ?? 0) ?: null,
            mb_substr($action, 0, 100, 'UTF-8'),
            mb_substr($detail, 0, 255, 'UTF-8'),
        ]);
    } catch (Throwable $ex) {
        // günlük yazılamazsa işlemi durdurma
    }
}
