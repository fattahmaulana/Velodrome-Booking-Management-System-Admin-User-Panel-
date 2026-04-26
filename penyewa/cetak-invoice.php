<?php
require_once __DIR__ . '/../config/koneksi.php';
requirePenyewa();
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT b.*, a.nama_arena, a.lokasi, t.jenis_sewa, t.keterangan as tarif_ket, u.nama_lengkap, u.email, u.no_telepon, u.alamat FROM booking b JOIN arena a ON b.arena_id=a.id JOIN tarif t ON b.tarif_id=t.id JOIN users u ON b.penyewa_id=u.id WHERE b.id=? AND b.penyewa_id=?");
$stmt->execute([$id, $uid]);
$b = $stmt->fetch();
if (!$b) { header('Location: '.BASE_URL.'/penyewa/riwayat.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Invoice <?=$b['kode_booking']?> - Velodrome Diponegoro</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{maroon:{50:'#fef2f2',100:'#fee2e2',700:'#991b1b',800:'#7f1d1d'},surface:{50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',400:'#94a3b8',500:'#64748b',700:'#334155',900:'#0f172a'}},fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>*{font-family:'Inter',system-ui,sans-serif}@media print{.no-print{display:none!important}body{background:#fff}}</style>
</head>
<body class="bg-surface-50 antialiased">
<div class="max-w-2xl mx-auto px-4 py-10">
    <div class="flex gap-3 mb-4 no-print">
        <button onclick="window.print()" class="bg-maroon-700 hover:bg-maroon-800 text-white px-5 py-2 rounded-xl text-xs font-semibold transition-all"><i class="fa-solid fa-print mr-1.5"></i>Cetak</button>
        <a href="<?=BASE_URL?>/penyewa/riwayat.php" class="bg-surface-200 hover:bg-surface-300 text-surface-700 px-5 py-2 rounded-xl text-xs font-medium transition-all">Kembali</a>
    </div>
    <div class="bg-white rounded-2xl border border-surface-100 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-maroon-700 to-maroon-800 px-8 py-6 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center"><i class="fa-solid fa-person-biking text-lg"></i></div>
                <div><h2 class="font-bold text-lg">Velodrome Diponegoro</h2><p class="text-maroon-200 text-[11px]">Invoice / Bukti Booking</p></div>
            </div>
            <div class="text-right"><p class="text-xs text-maroon-200">Status</p><span class="text-sm font-bold uppercase"><?=$b['status']?></span></div>
        </div>
        <div class="p-8">
            <div class="bg-surface-50 rounded-xl p-4 text-center mb-6">
                <p class="text-[10px] text-surface-400 uppercase tracking-wider">Kode Booking</p>
                <p class="text-3xl font-mono font-bold text-maroon-700 mt-1"><?=$b['kode_booking']?></p>
            </div>
            <div class="grid grid-cols-2 gap-6 text-sm mb-6">
                <div>
                    <h3 class="text-[10px] font-semibold text-surface-400 uppercase tracking-wider mb-2">Data Penyewa</h3>
                    <p class="font-medium text-surface-900"><?=sanitize($b['nama_lengkap'])?></p>
                    <p class="text-surface-500"><?=sanitize($b['email'])?></p>
                    <p class="text-surface-500"><?=sanitize($b['no_telepon']??'-')?></p>
                </div>
                <div>
                    <h3 class="text-[10px] font-semibold text-surface-400 uppercase tracking-wider mb-2">Detail Sewa</h3>
                    <p class="text-surface-700"><strong>Arena:</strong> <?=sanitize($b['nama_arena'])?></p>
                    <p class="text-surface-700"><strong>Jenis:</strong> <?=$b['jenis_sewa']==='event'?'Event (3 Hari)':'Per Jam'?></p>
                    <p class="text-surface-700"><strong>Tanggal:</strong> <?=formatTgl($b['tanggal_mulai'])?><?=$b['tanggal_mulai']!==$b['tanggal_selesai']?' s/d '.formatTgl($b['tanggal_selesai']):''?></p>
                    <?php if($b['waktu_mulai']):?><p class="text-surface-700"><strong>Waktu:</strong> <?=substr($b['waktu_mulai'],0,5)?> - <?=substr($b['waktu_selesai'],0,5)?></p><?php endif;?>
                </div>
            </div>
            <div class="border-t border-surface-200 pt-4 flex justify-between items-center">
                <span class="font-semibold text-surface-700">Total Biaya</span>
                <span class="text-2xl font-bold text-maroon-700"><?=formatRupiah($b['total_biaya'])?></span>
            </div>
            <div class="mt-6 text-center text-[11px] text-surface-400">
                <p>Dicetak pada <?=formatTgl(date('Y-m-d'))?> &middot; Velodrome Diponegoro, Semarang</p>
            </div>
        </div>
    </div>
</div>
</body></html>
