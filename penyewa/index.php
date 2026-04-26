<?php
$pageTitle = 'Dashboard - Velodrome Diponegoro';
$currentPage = 'dashboard';
require_once __DIR__ . '/../config/koneksi.php';
requirePenyewa();
$uid = $_SESSION['user_id'];

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$user = $user->fetch();

$totalBooking = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE penyewa_id=?");
$totalBooking->execute([$uid]);
$totalBooking = $totalBooking->fetchColumn();

$activeBooking = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE penyewa_id=? AND status IN ('pending','confirmed')");
$activeBooking->execute([$uid]);
$activeBooking = $activeBooking->fetchColumn();

$totalSpend = $pdo->prepare("SELECT COALESCE(SUM(total_biaya),0) FROM booking WHERE penyewa_id=? AND status IN ('confirmed','completed')");
$totalSpend->execute([$uid]);
$totalSpend = $totalSpend->fetchColumn();

$recent = $pdo->prepare("SELECT b.*, a.nama_arena FROM booking b JOIN arena a ON b.arena_id=a.id WHERE b.penyewa_id=? ORDER BY b.created_at DESC LIMIT 5");
$recent->execute([$uid]);
$recent = $recent->fetchAll();

$profileFlash = getFlash('profile');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $pdo->prepare("UPDATE users SET nama_lengkap=?,email=?,no_telepon=?,alamat=? WHERE id=?")
        ->execute([trim($_POST['nama_lengkap']), trim($_POST['email']), trim($_POST['no_telepon']), trim($_POST['alamat']), $uid]);
    $_SESSION['nama_lengkap'] = trim($_POST['nama_lengkap']);
    flash('profile', 'Profil berhasil diperbarui.', 'success');
    header('Location: ' . BASE_URL . '/penyewa/');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* ------------------------------------------------------------------ */
    /* Animation keyframes                                                  */
    /* ------------------------------------------------------------------ */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(14px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }

    /* ------------------------------------------------------------------ */
    /* Page wrapper                                                         */
    /* ------------------------------------------------------------------ */
    .dashboard-wrap {
        animation: fadeIn 0.35s ease both;
    }

    /* ------------------------------------------------------------------ */
    /* Welcome header                                                       */
    /* ------------------------------------------------------------------ */
    .welcome-header {
        animation: fadeInUp 0.45s cubic-bezier(.22,.68,0,1.2) both;
    }

    /* ------------------------------------------------------------------ */
    /* Flash notice                                                         */
    /* ------------------------------------------------------------------ */
    .flash-notice {
        animation: fadeInUp 0.4s ease both;
    }

    /* ------------------------------------------------------------------ */
    /* Stat cards                                                           */
    /* ------------------------------------------------------------------ */
    .stat-card {
        background: #ffffff;
        border: 1px solid #f0f0f2;
        border-radius: 18px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow 0.28s ease, transform 0.28s ease, border-color 0.28s ease;
        animation: fadeInUp 0.5s cubic-bezier(.22,.68,0,1.2) both;
    }
    .stat-card:hover {
        box-shadow: 0 8px 28px rgba(0,0,0,0.07);
        transform: translateY(-3px);
        border-color: #e4e4ea;
    }
    .stat-card:nth-child(1) { animation-delay: 0.08s; }
    .stat-card:nth-child(2) { animation-delay: 0.16s; }
    .stat-card:nth-child(3) { animation-delay: 0.24s; }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 15px;
    }
    .stat-icon-maroon  { background: #fdf2f2; color: #8b1a1a; }
    .stat-icon-amber   { background: #fffbeb; color: #b45309; }
    .stat-icon-emerald { background: #ecfdf5; color: #059669; }

    .stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #111118;
        line-height: 1.1;
    }
    .stat-label {
        font-size: 11px;
        color: #9a9aaa;
        margin-top: 2px;
    }

    /* ------------------------------------------------------------------ */
    /* Main panels                                                          */
    /* ------------------------------------------------------------------ */
    .panel {
        background: #ffffff;
        border: 1px solid #f0f0f2;
        border-radius: 20px;
        overflow: hidden;
        animation: fadeInUp 0.55s cubic-bezier(.22,.68,0,1.2) both;
    }
    .panel-booking  { animation-delay: 0.3s; }
    .panel-profile  { animation-delay: 0.38s; }

    .panel-header {
        padding: 16px 22px;
        border-bottom: 1px solid #f5f5f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .panel-title {
        font-weight: 600;
        font-size: 13.5px;
        color: #111118;
        letter-spacing: -0.01em;
    }
    .panel-link {
        font-size: 11.5px;
        color: #8b1a1a;
        font-weight: 500;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: color 0.2s ease;
    }
    .panel-link:hover { color: #6b1212; }

    /* ------------------------------------------------------------------ */
    /* Table                                                                */
    /* ------------------------------------------------------------------ */
    .booking-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .booking-table thead tr {
        background: #fafafa;
    }
    .booking-table thead th {
        padding: 10px 20px;
        text-align: left;
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #b0b0bf;
    }
    .booking-table tbody tr {
        border-top: 1px solid #f5f5f7;
        transition: background 0.18s ease;
    }
    .booking-table tbody tr:hover {
        background: #fafafa;
    }
    .booking-table tbody td {
        padding: 11px 20px;
        vertical-align: middle;
    }

    /* Row staggered entrance */
    .booking-table tbody tr {
        animation: fadeInUp 0.4s ease both;
    }
    .booking-table tbody tr:nth-child(1) { animation-delay: 0.38s; }
    .booking-table tbody tr:nth-child(2) { animation-delay: 0.44s; }
    .booking-table tbody tr:nth-child(3) { animation-delay: 0.50s; }
    .booking-table tbody tr:nth-child(4) { animation-delay: 0.56s; }
    .booking-table tbody tr:nth-child(5) { animation-delay: 0.62s; }

    .booking-code {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        font-weight: 600;
        font-size: 11.5px;
        color: #8b1a1a;
        letter-spacing: 0.02em;
    }
    .booking-arena  { color: #2a2a38; font-weight: 500; }
    .booking-date   { color: #888899; }
    .booking-amount { font-weight: 600; color: #111118; }

    /* Action column - invoice button */
    .td-action { white-space: nowrap; }
    .btn-invoice {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: #f5f5f7;
        color: #888899;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        font-size: 10px;
    }
    .btn-invoice:hover {
        background: #fff0f0;
        color: #8b1a1a;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(139,26,26,0.12);
    }

    /* ------------------------------------------------------------------ */
    /* Status badges                                                        */
    /* ------------------------------------------------------------------ */
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .badge-pending   { background: #fffbeb; color: #b45309; }
    .badge-confirmed { background: #eff6ff; color: #1d4ed8; }
    .badge-completed { background: #ecfdf5; color: #059669; }
    .badge-cancelled { background: #fef2f2; color: #dc2626; }

    /* ------------------------------------------------------------------ */
    /* Profile form                                                         */
    /* ------------------------------------------------------------------ */
    .profile-form {
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .form-label {
        display: block;
        font-size: 10.5px;
        font-weight: 500;
        color: #b0b0bf;
        margin-bottom: 5px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .form-input {
        width: 100%;
        padding: 9px 13px;
        background: #fafafa;
        border: 1px solid #ebebf0;
        border-radius: 12px;
        font-size: 13px;
        color: #111118;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        box-sizing: border-box;
    }
    .form-input:focus {
        border-color: #8b1a1a;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(139,26,26,0.08);
    }
    .form-textarea {
        resize: none;
        min-height: 64px;
    }
    .btn-save {
        width: 100%;
        padding: 10px;
        background: #8b1a1a;
        color: #ffffff;
        border: none;
        border-radius: 12px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        letter-spacing: 0.02em;
        transition: background 0.22s ease, box-shadow 0.22s ease, transform 0.18s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }
    .btn-save:hover {
        background: #6b1212;
        box-shadow: 0 6px 18px rgba(139,26,26,0.22);
        transform: translateY(-1px);
    }
    .btn-save:active {
        transform: translateY(0);
        box-shadow: none;
    }

    /* ------------------------------------------------------------------ */
    /* Empty state                                                          */
    /* ------------------------------------------------------------------ */
    .empty-state {
        padding: 36px 20px;
        text-align: center;
        color: #b0b0bf;
        font-size: 12px;
    }
    .empty-state i {
        display: block;
        font-size: 22px;
        margin-bottom: 8px;
        opacity: 0.35;
    }
</style>

<div class="dashboard-wrap max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Welcome header -->
    <div class="welcome-header mb-8">
        <h1 style="font-size:1.5rem;font-weight:700;color:#111118;letter-spacing:-0.02em;">
            Selamat Datang, <?= sanitize($user['nama_lengkap']) ?>
        </h1>
        <p style="font-size:13px;color:#9a9aaa;margin-top:4px;">
            Kelola penyewaan arena Anda dari sini
        </p>
    </div>

    <!-- Flash notice -->
    <?php if ($profileFlash): ?>
    <div data-flash class="flash-notice" style="
        padding: 12px 16px;
        border-radius: 14px;
        margin-bottom: 24px;
        font-size: 13px;
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        color: #065f46;
        display: flex;
        align-items: center;
        gap: 10px;
    ">
        <i class="fa-solid fa-circle-check" style="color:#10b981;font-size:13px;"></i>
        <?= sanitize($profileFlash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="stat-card">
            <div class="stat-icon stat-icon-maroon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalBooking ?></div>
                <div class="stat-label">Total Booking</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-amber">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <div class="stat-value"><?= $activeBooking ?></div>
                <div class="stat-label">Booking Aktif</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-emerald">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <div class="stat-value"><?= formatRupiah($totalSpend) ?></div>
                <div class="stat-label">Total Pengeluaran</div>
            </div>
        </div>
    </div>

    <!-- Main panels -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Booking table panel -->
        <div class="panel panel-booking lg:col-span-2">
            <div class="panel-header">
                <span class="panel-title">
                    <i class="fa-solid fa-clock-rotate-left" style="font-size:12px;margin-right:7px;color:#8b1a1a;"></i>
                    Booking Terbaru
                </span>
                <a href="<?= BASE_URL ?>/penyewa/riwayat.php" class="panel-link">
                    Lihat Semua
                    <i class="fa-solid fa-arrow-right" style="font-size:9px;"></i>
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Arena</th>
                            <th>Tanggal</th>
                            <th>Biaya</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fa-regular fa-folder-open"></i>
                                    Belum ada riwayat booking
                                </div>
                            </td>
                        </tr>
                        <?php else: foreach ($recent as $b): ?>
                        <tr>
                            <td class="booking-code"><?= $b['kode_booking'] ?></td>
                            <td class="booking-arena"><?= sanitize($b['nama_arena']) ?></td>
                            <td class="booking-date"><?= date('d M Y', strtotime($b['tanggal_mulai'])) ?></td>
                            <td class="booking-amount"><?= formatRupiah($b['total_biaya']) ?></td>
                            <td>
                                <span class="badge badge-<?= $b['status'] ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                            <td class="td-action">
                                <a href="<?= BASE_URL ?>/penyewa/cetak-invoice.php?id=<?= $b['id'] ?>"
                                   target="_blank"
                                   class="btn-invoice"
                                   title="Cetak Invoice">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Profile panel -->
        <div class="panel panel-profile">
            <div class="panel-header">
                <span class="panel-title">
                    <i class="fa-solid fa-user-circle" style="font-size:12px;margin-right:7px;color:#8b1a1a;"></i>
                    Profil Saya
                </span>
            </div>
            <form method="POST" class="profile-form">
                <input type="hidden" name="update_profile" value="1">

                <div>
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text"
                           name="nama_lengkap"
                           value="<?= sanitize($user['nama_lengkap']) ?>"
                           class="form-input"
                           placeholder="Nama lengkap Anda">
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           value="<?= sanitize($user['email']) ?>"
                           class="form-input"
                           placeholder="email@contoh.com">
                </div>

                <div>
                    <label class="form-label">No. Telepon</label>
                    <input type="text"
                           name="no_telepon"
                           value="<?= sanitize($user['no_telepon'] ?? '') ?>"
                           class="form-input"
                           placeholder="08xxxxxxxxxx">
                </div>

                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat"
                              rows="2"
                              class="form-input form-textarea"
                              placeholder="Alamat lengkap"><?= sanitize($user['alamat'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan
                </button>
            </form>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>