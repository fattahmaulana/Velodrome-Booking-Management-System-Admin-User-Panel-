<?php
$pageTitle = 'Laporan Transaksi'; $currentPage = 'laporan';
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$dari   = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-t');

$stmt = $pdo->prepare("
    SELECT b.*, u.nama_lengkap, a.nama_arena, t.jenis_sewa
    FROM booking b
    JOIN users u ON b.penyewa_id=u.id
    JOIN arena a ON b.arena_id=a.id
    JOIN tarif t ON b.tarif_id=t.id
    WHERE b.tanggal_mulai BETWEEN ? AND ?
    ORDER BY b.tanggal_mulai DESC
");
$stmt->execute([$dari, $sampai]);
$data = $stmt->fetchAll();

$summary = $pdo->prepare("
    SELECT COUNT(*) as total, COALESCE(SUM(CASE WHEN status IN ('confirmed','completed') THEN total_biaya ELSE 0 END),0) as pendapatan,
           SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) as confirmed,
           SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed
    FROM booking WHERE tanggal_mulai BETWEEN ? AND ?
");
$summary->execute([$dari,$sampai]);
$s = $summary->fetch();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div><h1 class="text-xl font-bold text-surface-900">Laporan Transaksi</h1><p class="text-xs text-surface-500 mt-0.5">Filter dan lihat rincian berdasarkan periode</p></div>
    <button onclick="window.print()" class="bg-surface-100 text-surface-700 px-4 py-2 rounded-xl text-xs font-medium hover:bg-surface-200 transition-colors"><i class="fa-solid fa-print mr-1"></i>Cetak</button>
</div>

<div class="bg-white rounded-2xl border border-surface-100 p-4 mb-5">
    <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1"><label class="block text-xs font-medium text-surface-600 mb-1">Dari</label><input type="date" name="dari" value="<?=$dari?>" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-sm outline-none"></div>
        <div class="flex-1"><label class="block text-xs font-medium text-surface-600 mb-1">Sampai</label><input type="date" name="sampai" value="<?=$sampai?>" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-sm outline-none"></div>
        <button class="bg-maroon-700 text-white px-5 py-2 rounded-xl text-xs font-medium hover:bg-maroon-800 transition-colors whitespace-nowrap"><i class="fa-solid fa-filter mr-1"></i>Tampilkan</button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-2xl p-4 border border-surface-100"><div class="flex items-center gap-3"><div class="w-9 h-9 bg-maroon-50 rounded-xl flex items-center justify-center"><i class="fa-solid fa-receipt text-maroon-700 text-xs"></i></div><div><p class="text-xl font-bold text-surface-900"><?=$s['total']?></p><p class="text-[11px] text-surface-400">Total Transaksi</p></div></div></div>
    <div class="bg-white rounded-2xl p-4 border border-surface-100"><div class="flex items-center gap-3"><div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center"><i class="fa-solid fa-money-bill-wave text-emerald-600 text-xs"></i></div><div><p class="text-xl font-bold text-surface-900"><?=formatRupiah($s['pendapatan'])?></p><p class="text-[11px] text-surface-400">Pendapatan</p></div></div></div>
    <div class="bg-white rounded-2xl p-4 border border-surface-100"><div class="flex items-center gap-3"><div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center"><i class="fa-solid fa-flag-checkered text-blue-600 text-xs"></i></div><div><p class="text-xl font-bold text-surface-900"><?=$s['completed']?></p><p class="text-[11px] text-surface-400">Selesai</p></div></div></div>
</div>

<div class="bg-white rounded-2xl border border-surface-100 overflow-hidden">
    <div class="px-5 py-3 border-b border-surface-50"><h2 class="font-semibold text-sm text-surface-900">Detail Transaksi</h2><p class="text-[10px] text-surface-400"><?=formatTgl($dari)?> - <?=formatTgl($sampai)?></p></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[10px] font-medium text-surface-400 uppercase bg-surface-50/50"><th class="px-5 py-2.5 text-left">No</th><th class="px-5 py-2.5 text-left">Kode</th><th class="px-5 py-2.5 text-left">Penyewa</th><th class="px-5 py-2.5 text-left">Arena</th><th class="px-5 py-2.5 text-left">Jenis</th><th class="px-5 py-2.5 text-left">Tanggal</th><th class="px-5 py-2.5 text-left">Biaya</th><th class="px-5 py-2.5 text-left">Status</th></tr></thead>
            <tbody class="divide-y divide-surface-50">
                <?php if(empty($data)):?><tr><td colspan="8" class="px-5 py-10 text-center text-xs text-surface-400"><i class="fa-solid fa-chart-line text-xl text-surface-200 block mb-2"></i>Tidak ada transaksi</td></tr>
                <?php else: foreach($data as $i=>$d):?>
                <tr class="hover:bg-surface-50/50"><td class="px-5 py-2.5 text-surface-400 text-xs"><?=$i+1?></td><td class="px-5 py-2.5 font-mono font-semibold text-maroon-700 text-xs"><?=$d['kode_booking']?></td><td class="px-5 py-2.5 text-surface-700"><?=sanitize($d['nama_lengkap'])?></td><td class="px-5 py-2.5 text-surface-500"><?=sanitize($d['nama_arena'])?></td><td class="px-5 py-2.5"><span class="text-[10px] bg-surface-100 px-2 py-0.5 rounded-full"><?=$d['jenis_sewa']==='event'?'Event':'Per Jam'?></span></td><td class="px-5 py-2.5 text-surface-500 text-xs"><?=date('d/m/Y',strtotime($d['tanggal_mulai']))?></td><td class="px-5 py-2.5 font-semibold"><?=formatRupiah($d['total_biaya'])?></td><td class="px-5 py-2.5"><span class="badge badge-<?=$d['status']?>"><?=ucfirst($d['status'])?></span></td></tr>
                <?php endforeach; endif;?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
