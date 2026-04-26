<?php
$pageTitle = 'Dashboard Admin'; $currentPage = 'dashboard';
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$totalBooking   = $pdo->query("SELECT COUNT(*) FROM booking")->fetchColumn();
$bookPending    = $pdo->query("SELECT COUNT(*) FROM booking WHERE status='pending'")->fetchColumn();
$bookConfirmed  = $pdo->query("SELECT COUNT(*) FROM booking WHERE status='confirmed'")->fetchColumn();
$totalPendapatan= $pdo->query("SELECT COALESCE(SUM(total_biaya),0) FROM booking WHERE status IN ('confirmed','completed')")->fetchColumn();
$totalPenyewa   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='penyewa'")->fetchColumn();
$buktiPending   = $pdo->query("SELECT COUNT(*) FROM booking WHERE file_bukti_bayar IS NOT NULL AND status='pending'")->fetchColumn();

$recentBookings = $pdo->query("SELECT b.*,u.nama_lengkap,a.nama_arena FROM booking b JOIN users u ON b.penyewa_id=u.id JOIN arena a ON b.arena_id=a.id ORDER BY b.created_at DESC LIMIT 7")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="mb-6">
    <h1 class="text-xl font-bold text-surface-900">Dashboard</h1>
    <p class="text-xs text-surface-500 mt-0.5">Ringkasan data penyewaan arena velodrome</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-surface-100">
        <div class="flex items-center justify-between mb-3"><div class="w-9 h-9 bg-maroon-50 rounded-xl flex items-center justify-center"><i class="fa-solid fa-calendar-check text-maroon-700 text-xs"></i></div><span class="text-[10px] font-medium text-maroon-700 bg-maroon-50 px-2 py-0.5 rounded-full">Total</span></div>
        <p class="text-2xl font-bold text-surface-900"><?=$totalBooking?></p><p class="text-xs text-surface-400 mt-0.5">Total Booking</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-surface-100">
        <div class="flex items-center justify-between mb-3"><div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center"><i class="fa-solid fa-hourglass-half text-amber-600 text-xs"></i></div><?php if($bookPending>0):?><span class="text-[10px] font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full animate-pulse"><?=$bookPending?></span><?php endif;?></div>
        <p class="text-2xl font-bold text-surface-900"><?=$bookPending?></p><p class="text-xs text-surface-400 mt-0.5">Menunggu Verifikasi</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-surface-100">
        <div class="flex items-center justify-between mb-3"><div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center"><i class="fa-solid fa-wallet text-emerald-600 text-xs"></i></div></div>
        <p class="text-2xl font-bold text-surface-900"><?=formatRupiah($totalPendapatan)?></p><p class="text-xs text-surface-400 mt-0.5">Pendapatan</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-surface-100">
        <div class="flex items-center justify-between mb-3"><div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center"><i class="fa-solid fa-users text-blue-600 text-xs"></i></div></div>
        <p class="text-2xl font-bold text-surface-900"><?=$totalPenyewa?></p><p class="text-xs text-surface-400 mt-0.5">Total Penyewa</p>
    </div>
</div>

<?php if($buktiPending>0):?>
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-3 mb-6">
    <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-bell text-amber-600 text-xs"></i></div>
    <div class="flex-1"><p class="text-sm font-semibold text-amber-800">Terdapat <?=$buktiPending?> bukti pembayaran menunggu verifikasi</p></div>
    <a href="<?=BASE_URL?>/admin/booking.php?status=pending" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-xs font-medium hover:bg-amber-700 transition-colors flex-shrink-0">Verifikasi</a>
</div>
<?php endif;?>

<div class="bg-white rounded-2xl border border-surface-100 overflow-hidden">
    <div class="px-5 py-3.5 border-b border-surface-50 flex items-center justify-between">
        <h2 class="font-semibold text-sm text-surface-900">Booking Terbaru</h2>
        <a href="<?=BASE_URL?>/admin/booking.php" class="text-xs text-maroon-700 font-medium">Semua <i class="fa-solid fa-arrow-right text-[9px] ml-0.5"></i></a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[10px] font-medium text-surface-400 uppercase bg-surface-50/50"><th class="px-5 py-2.5 text-left">Kode</th><th class="px-5 py-2.5 text-left">Penyewa</th><th class="px-5 py-2.5 text-left">Arena</th><th class="px-5 py-2.5 text-left">Tanggal</th><th class="px-5 py-2.5 text-left">Biaya</th><th class="px-5 py-2.5 text-left">Bukti</th><th class="px-5 py-2.5 text-left">Status</th></tr></thead>
            <tbody class="divide-y divide-surface-50">
                <?php foreach($recentBookings as $b):?>
                <tr class="hover:bg-surface-50/50">
                    <td class="px-5 py-2.5 font-mono font-semibold text-maroon-700 text-xs"><?=$b['kode_booking']?></td>
                    <td class="px-5 py-2.5 text-surface-700"><?=sanitize($b['nama_lengkap'])?></td>
                    <td class="px-5 py-2.5 text-surface-500"><?=sanitize($b['nama_arena'])?></td>
                    <td class="px-5 py-2.5 text-surface-500"><?=date('d/m/Y',strtotime($b['tanggal_mulai']))?></td>
                    <td class="px-5 py-2.5 font-medium"><?=formatRupiah($b['total_biaya'])?></td>
                    <td class="px-5 py-2.5"><?=$b['file_bukti_bayar']?'<i class="fa-solid fa-image text-emerald-500 text-xs"></i>':'<span class="text-surface-300 text-xs">-</span>'?></td>
                    <td class="px-5 py-2.5"><span class="badge badge-<?=$b['status']?>"><?=ucfirst($b['status'])?></span></td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
