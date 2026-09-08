<?php
/**
 * VELOCITY SYSTEM AI - Helper Functions
 */

/**
 * Format angka menjadi Rupiah
 */
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function formatWhatsAppNumber($number) {
    $digits = preg_replace('/\D+/', '', (string) $number);
    if (str_starts_with($digits, '0')) {
        return '62' . substr($digits, 1);
    }
    if (str_starts_with($digits, '8')) {
        return '62' . $digits;
    }
    return $digits;
}

/** Menentukan apakah keahlian joki relevan dengan kategori atau layanan order. */
function isJokiCompatible(string $keahlian, string $categoryName, string $serviceTitle = ''): bool {
    $text = strtolower($categoryName . ' ' . $serviceTitle);
    if ($keahlian === 'IT') {
        return (bool) preg_match('/it|web|coding|program|database|teknis|development|website/', $text);
    }
    if ($keahlian === 'Akuntansi') {
        return (bool) preg_match('/akuntansi|account|keuangan|finance|laporan/', $text);
    }
    return !preg_match('/it|web|coding|program|database|akuntansi|account|keuangan|finance/', $text);
}

/**
 * Format harga layanan (handle range harga)
 */
function formatServicePrice($service) {
    $price = formatRupiah($service['price']);
    if (!empty($service['price_max'])) {
        $price .= ' - ' . formatRupiah($service['price_max']);
    }
    return $price . ' / ' . htmlspecialchars($service['unit']);
}

/**
 * Sanitasi input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate kode order unik
 */
function generateOrderCode() {
    return 'VEL-' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Ambil setting dari database
 */
function getSetting($key, $default = '') {
    static $settings = null;
    if ($settings === null) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings[$key] ?? $default;
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Flash message helper
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
