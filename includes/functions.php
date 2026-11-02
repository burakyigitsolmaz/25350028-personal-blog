<?php
/**
 * Yardımcı fonksiyonlar:
 * kaçış, CSRF, flash mesaj, ip_hash, slug, tarih, ziyaret kaydı vs.
 */

/** HTML kaçışı — tüm çıktılar bu fonksiyondan geçer. */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Site içi tam adres üretir. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Yönlendirme (PRG pattern). Çıktı başlamadan çağrılmalıdır. */
function redirect(string $path = ''): void
{
    $target = $path;
    if ($path === '' ) {
        $target = url();
    } elseif (!preg_match('#^https?://#', $path) && $path[0] !== '/') {
        $target = url($path);
    }
    header('Location: ' . $target, true, 302);
    exit;
}

/* ----------------------------------------
   CSRF
   ---------------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Form içine gizli alan olarak gömülür. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** POST'ta token doğrulaması. */
function csrf_check(?string $token = null): bool
{
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    return is_string($token)
        && $token !== ''
        && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/* ----------------------------------------
   Flash mesaj (PRG sonrası) + oturum
   ---------------------------------------- */

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* ----------------------------------------
   IP — asla düz saklanmaz
   ---------------------------------------- */

/** İstemci IP'sini SALT ile SHA-256 hash'ine çevirir. */
function ip_hash(): string
{
    blog_config_boot();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return hash('sha256', $ip . APP_SALT);
}

/* ----------------------------------------
   Yoğun kullanılan yardımcılar
   ---------------------------------------- */

/** Başlıklardan URL uyumlu slug üretir (Türkçe karakter destekli). */
function slugify(string $text): string
{
    $from = ['ç', 'ğ', 'ı', 'İ', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'Ö', 'Ş', 'Ü'];
    $to   = ['c', 'g', 'i', 'i', 'o', 's', 'u', 'c', 'g', 'i', 'o', 's', 'u'];

    $text = str_replace($from, $to, mb_strtolower($text, 'UTF-8'));
    $text = preg_replace('#[^a-z0-9]+#', '-', $text);
    $text = trim((string)$text, '-');
    return $text !== '' ? $text : 'yazi';
}

/** Türkçe tarih formatı: 12 Ekim 2026 */
function tr_date(?string $mysqlDate): string
{
    if (!$mysqlDate) {
        return '';
    }
    $months = [1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs',
               6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül',
               10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'];
    $ts = strtotime($mysqlDate);
    if ($ts === false) {
        return e($mysqlDate);
    }
    return date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/** AYcci YYYY gibi arşiv etiketi: Ekim 2026 */
function tr_month_year(int $y, int $m): string
{
    $months = [1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs',
               6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül',
               10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'];
    return ($months[$m] ?? '') . ' ' . $y;
}

/** Ayar tablosundan değer okur (yoksa varsayılan). */
function setting(string $key, string $default = ''): string
{
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        try {
            $rows = db()->query(
                'SELECT setting_key, setting_value FROM settings'
            )->fetchAll(PDO::FETCH_KEY_PAIR);
            $cache = $rows;
        } catch (Throwable $ex) {
            // Ayarlar yoksa sitede düşmemek için boş geç
            $cache = [];
        }
    }

    return $cache[$key] ?? $default;
}

/**
 * Ziyaret kaydı: page + ip_hash.
 * Site sayfalarında çağrılır; hata ziyaretçiyi düşürmez.
 */
function log_visit(string $page): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO visits (page, ip_hash, created_at) VALUES (?, ?, NOW())'
        );
        $stmt->execute([$page, ip_hash()]);
    } catch (Throwable $ex) {
        // yoksay
    }
}

/** 404 sayfası: durum kodu + şablon */
function page_not_found(): never
{
    http_response_code(404);
    require dirname(__DIR__) . '/404.php';
    exit;
}

/** POST alanını güvenli string olarak okur. */
function post_str(string $key, int $maxLength = 190): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    if (mb_strlen($value, 'UTF-8') > $maxLength) {
        $value = mb_substr($value, 0, $maxLength, 'UTF-8');
    }
    return $value;
}

/** Simge durumunu gibi kategorize eden minik yardımcı. */
function status_text(bool $published): string
{
    return $published ? 'Yayında' : 'Taslak';
}
