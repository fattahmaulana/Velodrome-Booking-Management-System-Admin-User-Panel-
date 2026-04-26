<?php
$pageTitle = 'Riwayat Transaksi - Velodrome Diponegoro';
$currentPage = 'riwayat';
require_once __DIR__ . '/../config/koneksi.php';
requirePenyewa();
$uid = $_SESSION['user_id'];

// Handle upload bukti
$uploadFlash = getFlash('upload');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_bayar'])) {
    $bookingId = (int)$_POST['booking_id'];
    $file = $_FILES['bukti_bayar'];

    // Verify ownership
    $check = $pdo->prepare("SELECT id FROM booking WHERE id=? AND penyewa_id=?");
    $check->execute([$bookingId, $uid]);
    if ($check->fetch()) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file['error'] === UPLOAD_ERR_OK && in_array($ext, ALLOWED_EXT) && $file['size'] <= MAX_FILE_SIZE) {
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            $filename = 'bukti_' . $bookingId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)) {
                $pdo->prepare("UPDATE booking SET file_bukti_bayar=? WHERE id=?")->execute([$filename, $bookingId]);
                flash('upload', 'Bukti pembayaran berhasil diupload.', 'success');
            } else { flash('upload', 'Gagal menyimpan file.', 'warning'); }
        } else { flash('upload', 'File tidak valid. Gunakan JPG/PNG/WEBP maks 2MB.', 'warning'); }
    }
    header('Location: ' . BASE_URL . '/penyewa/riwayat.php'); exit;
}

// Get bookings
$stmt = $pdo->prepare("SELECT b.*, a.nama_arena, t.jenis_sewa FROM booking b JOIN arena a ON b.arena_id=a.id JOIN tarif t ON b.tarif_id=t.id WHERE b.penyewa_id=? ORDER BY b.created_at DESC");
$stmt->execute([$uid]); $bookings = $stmt->fetchAll();

$showUpload = (int)($_GET['upload'] ?? 0);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-surface-900">Riwayat Transaksi</h1>
            <p class="text-sm text-surface-500 mt-1">Semua booking dan status pembayaran Anda</p>
        </div>
        <a href="<?=BASE_URL?>/penyewa/booking.php" class="bg-maroon-700 hover:bg-maroon-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all"><i class="fa-solid fa-plus mr-1"></i>Booking Baru</a>
    </div>

    <?php if($uploadFlash):?><div data-flash class="p-3.5 rounded-xl mb-6 text-sm transition-all <?=$uploadFlash['type']==='success'?'bg-emerald-50 border border-emerald-200 text-emerald-800':'bg-amber-50 border border-amber-200 text-amber-800'?> flex items-center gap-2"><i class="fa-solid <?=$uploadFlash['type']==='success'?'fa-circle-check text-emerald-500':'fa-triangle-exclamation text-amber-500'?> text-xs"></i><?=sanitize($uploadFlash['message'])?></div><?php endif;?>

    <?php if (empty($bookings)): ?>
    <div class="bg-white rounded-2xl border border-surface-100 p-12 text-center">
        <i class="fa-solid fa-calendar-xmark text-3xl text-surface-200 mb-3"></i>
        <p class="text-surface-400 text-sm mb-4">Belum ada riwayat booking</p>
        <a href="<?=BASE_URL?>/penyewa/booking.php" class="inline-flex items-center gap-1.5 bg-maroon-700 text-white px-5 py-2 rounded-xl text-xs font-semibold hover:bg-maroon-800 transition-colors"><i class="fa-solid fa-calendar-plus"></i>Buat Booking</a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($bookings as $b): ?>
        <div class="bg-white rounded-2xl border border-surface-100 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 <?=match($b['status']){'pending'=>'bg-amber-50','confirmed'=>'bg-emerald-50','rejected'=>'bg-red-50','completed'=>'bg-blue-50',default=>'bg-surface-50'}?>">
                            <i class="<?=match($b['status']){'pending'=>'fa-solid fa-hourglass-half text-amber-600','confirmed'=>'fa-solid fa-circle-check text-emerald-600','rejected'=>'fa-solid fa-circle-xmark text-red-600','completed'=>'fa-solid fa-flag-checkered text-blue-600',default=>'fa-solid fa-circle text-surface-400'}?> text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                <span class="font-mono font-bold text-xs text-maroon-700"><?=$b['kode_booking']?></span>
                                <span class="badge badge-<?=$b['status']?>"><?=ucfirst($b['status'])?></span>
                                <span class="text-[10px] text-surface-400 bg-surface-50 px-2 py-0.5 rounded-full"><?=$b['jenis_sewa']==='event'?'Event':'Per Jam'?></span>
                            </div>
                            <p class="text-sm text-surface-600">
                                <?=sanitize($b['nama_arena'])?> &middot; <?=formatTgl($b['tanggal_mulai'])?>
                                <?=$b['tanggal_mulai']!==$b['tanggal_selesai']?' s/d '.formatTgl($b['tanggal_selesai']):''?>
                                <?=$b['waktu_mulai']?' &middot; '.substr($b['waktu_mulai'],0,5).' - '.substr($b['waktu_selesai'],0,5):''?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 sm:flex-shrink-0">
                        <div class="text-right">
                            <p class="text-base font-bold text-surface-900"><?=formatRupiah($b['total_biaya'])?></p>
                            <p class="text-[10px] text-surface-400"><?=$b['file_bukti_bayar']?'Bukti terkirim':'Belum upload bukti'?></p>
                        </div>
                        <div class="flex gap-1.5">
                            <?php if ($b['status']==='pending' && !$b['file_bukti_bayar']):?>
                            <button onclick="document.getElementById('upload-<?=$b['id']?>').classList.toggle('hidden')" class="w-8 h-8 rounded-lg bg-maroon-50 text-maroon-700 hover:bg-maroon-100 flex items-center justify-center transition-colors" title="Upload Bukti"><i class="fa-solid fa-upload text-[10px]"></i></button>
                            <?php endif;?>
                            <a href="<?=BASE_URL?>/penyewa/cetak-invoice.php?id=<?=$b['id']?>" target="_blank" class="w-8 h-8 rounded-lg bg-surface-50 text-surface-500 hover:bg-surface-100 flex items-center justify-center transition-colors" title="Cetak Invoice"><i class="fa-solid fa-print text-[10px]"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Upload Panel -->
            <div id="upload-<?=$b['id']?>" class="<?=$showUpload==$b['id']?'':'hidden'?> border-t border-surface-50 bg-surface-50/50 p-5">
                <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3 items-end">
                    <input type="hidden" name="booking_id" value="<?=$b['id']?>">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-medium text-surface-600 mb-1">File Bukti Transfer (JPG/PNG/WEBP, maks 2MB)</label>
                        <input type="file" name="bukti_bayar" required accept=".jpg,.jpeg,.png,.webp" class="w-full px-3 py-2 bg-white border border-surface-200 rounded-xl text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-maroon-50 file:text-maroon-700 file:font-medium file:text-xs">
                    </div>
                    <button type="submit" class="bg-maroon-700 hover:bg-maroon-800 text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition-all whitespace-nowrap"><i class="fa-solid fa-paper-plane mr-1"></i>Kirim</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
