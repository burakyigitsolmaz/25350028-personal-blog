<?php
/**
 * CV indirme uç noktası.
 * Dosya yolu koddan/setting'ten gelir, kullanıcıdan ASLA alınmaz (path traversal yok).
 * İndirme sayısı downloads tablosuna yazılır (oturum başına 1 kez sayılır).
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

blog_config_boot();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$relative = setting('cv_path', 'assets/cv/Burak_Yigit_Solmaz_CV.pdf');
$fullPath = realpath(ROOT_PATH . '/' . ltrim($relative, '/'));

// Güvenlik: dosya gerçekten proje klasörünün içinde mi?
if ($fullPath === false || !is_file($fullPath) || strpos($fullPath, realpath(ROOT_PATH)) !== 0) {
    http_response_code(404);
    exit('CV dosyası bulunamadı.');
}

// İndirme sayacı — oturum başına bir kez
$key = 'dl_' . md5($relative);
if (empty($_SESSION[$key])) {
    try {
        $stmt = db()->prepare(
            'INSERT INTO downloads (file_name, ip_hash, created_at) VALUES (?, ?, NOW())'
        );
        $stmt->execute([basename($fullPath), ip_hash()]);
        $_SESSION[$key] = 1;
    } catch (Throwable $ex) {
        // Sayaç yazılamazsa indirme yine de çalışsın
    }
}

$fileName = 'Burak_Yigit_Solmaz_CV.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($fullPath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($fullPath);
exit;
