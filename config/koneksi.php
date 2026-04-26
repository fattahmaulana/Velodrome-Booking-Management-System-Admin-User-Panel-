<?php
/**
 * Koneksi Database & Helper Functions
 * Sistem Informasi Penyewaan Arena Velodrome Diponegoro
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'velodrome_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', '/satrio');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024);
define('ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp']);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ── Auth Helpers ─────────────────────────── */

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}
function isAdmin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}
function isPenyewa(): bool {
    return ($_SESSION['role'] ?? '') === 'penyewa';
}

function requireLogin() {
    if (!isLoggedIn()) {
        flash('auth', 'Silakan login terlebih dahulu untuk melanjutkan.', 'warning');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}
function requirePenyewa() {
    requireLogin();
    if (!isPenyewa()) {
        header('Location: ' . BASE_URL . '/admin/');
        exit;
    }
}

/* ── Flash Messages ─────────────────────────── */

function flash(string $key, string $msg, string $type = 'success') {
    $_SESSION['flash'][$key] = ['message' => $msg, 'type' => $type];
}
function getFlash(string $key): ?array {
    $f = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $f;
}

/* ── Formatting ─────────────────────────── */

function formatRupiah($n): string {
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}
function formatTgl(string $d): string {
    $bln = ['','Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'];
    $dt = new DateTime($d);
    return (int)$dt->format('d') . ' ' . $bln[(int)$dt->format('m')] . ' ' . $dt->format('Y');
}
function sanitize($s): string {
    return htmlspecialchars(trim((string)$s), ENT_QUOTES, 'UTF-8');
}
function generateKode(): string {
    return 'VLD-' . strtoupper(substr(uniqid(), -6)) . date('dm');
}
function slugify(string $text): string {
    $text = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $text), '-'));
    return $text;
}

/* ── Schedule Conflict Checking ─────────────────────────── */

/**
 * Check if a booking would conflict with existing schedules.
 * For EVENT: check if any day within the 3-day span is already booked
 * For PER_JAM: check if the specific time slot on the specific date overlaps
 *
 * @return bool true if conflict exists
 */
function checkScheduleConflict(PDO $pdo, int $arenaId, string $jenisSewa,
    string $tglMulai, string $tglSelesai,
    ?string $waktuMulai = null, ?string $waktuSelesai = null): bool
{
    if ($jenisSewa === 'event') {
        // Event blocks full days – check if ANY schedule exists in the range
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM jadwal
            WHERE arena_id = ?
              AND tanggal BETWEEN ? AND ?
        ");
        $stmt->execute([$arenaId, $tglMulai, $tglSelesai]);
        return $stmt->fetchColumn() > 0;
    }

    // per_jam – check overlapping time on same date
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM jadwal
        WHERE arena_id = ?
          AND tanggal = ?
          AND (
              (jenis = 'event')
              OR
              (waktu_mulai < ? AND waktu_selesai > ?)
          )
    ");
    $stmt->execute([$arenaId, $tglMulai, $waktuSelesai, $waktuMulai]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Insert jadwal entries for a confirmed booking.
 * EVENT: insert 1 row per day for 3 days (full-day block)
 * PER_JAM: insert 1 row for the specific time slot
 */
function insertJadwal(PDO $pdo, int $bookingId, int $arenaId, string $jenisSewa,
    string $tglMulai, string $tglSelesai,
    ?string $waktuMulai = null, ?string $waktuSelesai = null): void
{
    if ($jenisSewa === 'event') {
        $start = new DateTime($tglMulai);
        $end   = new DateTime($tglSelesai);
        $end->modify('+1 day');
        $interval = new DateInterval('P1D');
        $period   = new DatePeriod($start, $interval, $end);

        $stmt = $pdo->prepare("
            INSERT INTO jadwal (arena_id, booking_id, tanggal, waktu_mulai, waktu_selesai, jenis, status)
            VALUES (?, ?, ?, '00:00:00', '23:59:59', 'event', 'booked')
        ");
        foreach ($period as $day) {
            $stmt->execute([$arenaId, $bookingId, $day->format('Y-m-d')]);
        }
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO jadwal (arena_id, booking_id, tanggal, waktu_mulai, waktu_selesai, jenis, status)
            VALUES (?, ?, ?, ?, ?, 'per_jam', 'booked')
        ");
        $stmt->execute([$arenaId, $bookingId, $tglMulai, $waktuMulai, $waktuSelesai]);
    }
}
