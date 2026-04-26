<?php
$pageTitle = 'Verifikasi Booking'; $currentPage = 'booking';
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $action    = $_POST['action'] ?? '';

    if ($bookingId > 0) {
        $booking = $pdo->prepare("SELECT b.*, t.jenis_sewa FROM booking b JOIN tarif t ON b.tarif_id=t.id WHERE b.id=?");
        $booking->execute([$bookingId]);
        $booking = $booking->fetch();

        if ($booking) {
            if ($action === 'confirm') {
                $pdo->prepare("UPDATE booking SET status='confirmed' WHERE id=?")->execute([$bookingId]);
                // Insert jadwal entries to block the schedule
                insertJadwal($pdo, $bookingId, $booking['arena_id'], $booking['jenis_sewa'],
                    $booking['tanggal_mulai'], $booking['tanggal_selesai'],
                    $booking['waktu_mulai'], $booking['waktu_selesai']);
                flash('booking_admin', 'Booking dikonfirmasi dan jadwal telah diblokir.', 'success');
            } elseif ($action === 'reject') {
                $pdo->prepare("UPDATE booking SET status='rejected' WHERE id=?")->execute([$bookingId]);
                // Remove jadwal if any
                $pdo->prepare("DELETE FROM jadwal WHERE booking_id=?")->execute([$bookingId]);
                flash('booking_admin', 'Booking ditolak.', 'warning');
            } elseif ($action === 'complete') {
                $pdo->prepare("UPDATE booking SET status='completed' WHERE id=?")->execute([$bookingId]);
                flash('booking_admin', 'Booking ditandai selesai.', 'success');
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/booking.php'); exit;
}

// Filter
$status = $_GET['status'] ?? '';
$search = $_GET['q'] ?? '';
$where = "1=1"; $params = [];
if ($status) { $where .= " AND b.status=?"; $params[] = $status; }
if ($search) { $where .= " AND (b.kode_booking LIKE ? OR u.nama_lengkap LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$stmt = $pdo->prepare("SELECT b.*, u.nama_lengkap, u.no_telepon, a.nama_arena, t.jenis_sewa FROM booking b JOIN users u ON b.penyewa_id=u.id JOIN arena a ON b.arena_id=a.id JOIN tarif t ON b.tarif_id=t.id WHERE $where ORDER BY FIELD(b.status,'pending','confirmed','completed','rejected'), b.created_at DESC");
$stmt->execute($params);
$bookings = $stmt->fetchAll();
$flash = getFlash('booking_admin');

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-bold text-surface-900">Verifikasi Booking</h1><p class="text-xs text-surface-500 mt-0.5">Kelola dan verifikasi pemesanan arena</p></div>
    <div class="flex gap-1.5 flex-wrap">
        <?php foreach(['' => 'Semua','pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','rejected'=>'Rejected'] as $k=>$v):?>
        <a href="?status=<?=$k?>" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors <?=$status===$k?'bg-maroon-700 text-white':'bg-surface-100 text-surface-600 hover:bg-surface-200'?>"><?=$v?></a>
        <?php endforeach;?>
    </div>
</div>

<?php if($flash):?><div data-flash class="p-3.5 rounded-xl mb-5 text-sm transition-all <?=$flash['type']==='success'?'bg-emerald-50 border border-emerald-200 text-emerald-800':'bg-amber-50 border border-amber-200 text-amber-800'?> flex items-center gap-2"><i class="fa-solid <?=$flash['type']==='success'?'fa-circle-check text-emerald-500':'fa-triangle-exclamation text-amber-500'?> text-xs"></i><?=sanitize($flash['message'])?></div><?php endif;?>

<div class="bg-white rounded-2xl border border-surface-100 p-4 mb-5">
    <form method="GET" class="flex gap-3">
        <div class="flex-1 relative"><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-surface-400 text-[10px]"></i><input type="text" name="q" value="<?=sanitize($search)?>" class="w-full pl-8 pr-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-xs focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none" placeholder="Cari kode atau nama..."></div>
        <button class="bg-maroon-700 text-white px-4 py-2 rounded-xl text-xs font-medium hover:bg-maroon-800 transition-colors"><i class="fa-solid fa-filter mr-1"></i>Cari</button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-surface-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[10px] font-medium text-surface-400 uppercase bg-surface-50/50">
                <th class="px-5 py-3 text-left">Kode</th><th class="px-5 py-3 text-left">Penyewa</th><th class="px-5 py-3 text-left">Arena</th><th class="px-5 py-3 text-left">Jenis</th><th class="px-5 py-3 text-left">Tanggal</th><th class="px-5 py-3 text-left">Biaya</th><th class="px-5 py-3 text-left">Bukti</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-surface-50">
                <?php if(empty($bookings)):?><tr><td colspan="9" class="px-5 py-10 text-center text-xs text-surface-400"><i class="fa-solid fa-inbox text-xl text-surface-200 block mb-2"></i>Tidak ada data</td></tr>
                <?php else: foreach($bookings as $b):?>
                <tr class="hover:bg-surface-50/50 transition-colors">
                    <td class="px-5 py-3 font-mono font-semibold text-maroon-700 text-xs"><?=$b['kode_booking']?></td>
                    <td class="px-5 py-3"><p class="text-surface-900 font-medium"><?=sanitize($b['nama_lengkap'])?></p><p class="text-[10px] text-surface-400"><?=sanitize($b['no_telepon']??'-')?></p></td>
                    <td class="px-5 py-3 text-surface-600"><?=sanitize($b['nama_arena'])?></td>
                    <td class="px-5 py-3"><span class="text-[10px] bg-surface-100 px-2 py-0.5 rounded-full"><?=$b['jenis_sewa']==='event'?'Event':'Per Jam'?></span></td>
                    <td class="px-5 py-3 text-surface-500 text-xs"><?=date('d/m/Y',strtotime($b['tanggal_mulai']))?><?=$b['waktu_mulai']?'<br>'.substr($b['waktu_mulai'],0,5).'-'.substr($b['waktu_selesai'],0,5):''?></td>
                    <td class="px-5 py-3 font-semibold"><?=formatRupiah($b['total_biaya'])?></td>
                    <td class="px-5 py-3">
                        <?php if($b['file_bukti_bayar']):?>
                        <a href="<?=BASE_URL?>/assets/uploads/<?=$b['file_bukti_bayar']?>" target="_blank" class="text-maroon-700 hover:text-maroon-800 text-xs font-medium"><i class="fa-solid fa-image mr-0.5"></i>Lihat</a>
                        <?php else:?><span class="text-surface-300 text-xs">-</span><?php endif;?>
                    </td>
                    <td class="px-5 py-3"><span class="badge badge-<?=$b['status']?>"><?=ucfirst($b['status'])?></span></td>
                    <td class="px-5 py-3">
                        <div class="flex gap-1">
                        <?php if($b['status']==='pending'):?>
                            <form method="POST" onsubmit="return confirm('Konfirmasi booking ini? Jadwal akan diblokir.')" class="inline"><input type="hidden" name="booking_id" value="<?=$b['id']?>"><input type="hidden" name="action" value="confirm"><button class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors" title="Approve"><i class="fa-solid fa-check text-[10px]"></i></button></form>
                            <form method="POST" onsubmit="return confirm('Tolak booking?')" class="inline"><input type="hidden" name="booking_id" value="<?=$b['id']?>"><input type="hidden" name="action" value="reject"><button class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Reject"><i class="fa-solid fa-xmark text-[10px]"></i></button></form>
                        <?php elseif($b['status']==='confirmed'):?>
                            <form method="POST" onsubmit="return confirm('Tandai selesai?')" class="inline"><input type="hidden" name="booking_id" value="<?=$b['id']?>"><input type="hidden" name="action" value="complete"><button class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" title="Selesai"><i class="fa-solid fa-flag-checkered text-[10px]"></i></button></form>
                        <?php else:?><span class="text-surface-300 text-xs">-</span><?php endif;?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif;?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
