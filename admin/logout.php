<?php
/**
 * Çıkış: oturumu temizler ve login sayfasına döner.
 */

require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    log_activity('Çıkış', 'Yönetim panelinden çıkış yapıldı.');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool)$params['secure'],
        (bool)$params['httponly']
    );
}

session_destroy();

session_start();
flash_set('info', 'Güvenli çıkış yapıldı.');
redirect('admin/login.php');
