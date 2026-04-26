<?php
$pageTitle = 'Booking Arena - Velodrome Diponegoro';
$currentPage = 'booking';
require_once __DIR__ . '/../config/koneksi.php';

// Must be logged in as penyewa to book
if (!isLoggedIn()) {
    flash('auth', 'Silakan login terlebih dahulu untuk melakukan booking.', 'warning');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
requirePenyewa();

$arenas = $pdo->query("SELECT * FROM arena ORDER BY id")->fetchAll();
$selectedArenaId = (int)($_GET['arena_id'] ?? $_POST['arena_id'] ?? 0);

// Get tarifs for selected arena
$tarifs = [];
if ($selectedArenaId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM tarif WHERE arena_id = ? ORDER BY jenis_sewa");
    $stmt->execute([$selectedArenaId]);
    $tarifs = $stmt->fetchAll();
}

$errors = [];
$newBooking = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    $arenaId     = (int)$_POST['arena_id'];
    $tarifId     = (int)$_POST['tarif_id'];
    $tglMulai    = $_POST['tanggal_mulai'] ?? '';
    $waktuMulai  = $_POST['waktu_mulai'] ?? null;
    $waktuSelesai= $_POST['waktu_selesai'] ?? null;

    // Get tarif info
    $tarif = $pdo->prepare("SELECT * FROM tarif WHERE id = ? AND arena_id = ?");
    $tarif->execute([$tarifId, $arenaId]);
    $tarif = $tarif->fetch();

    if (!$tarif) $errors[] = 'Tarif tidak valid.';
    if (!$tglMulai) $errors[] = 'Tanggal mulai wajib diisi.';
    if ($tglMulai < date('Y-m-d')) $errors[] = 'Tanggal tidak boleh di masa lalu.';

    $jenisSewa = $tarif['jenis_sewa'] ?? 'per_jam';

    // Calculate dates & cost
    if ($jenisSewa === 'event') {
        $tglSelesai = date('Y-m-d', strtotime($tglMulai . ' +2 days'));
        $totalBiaya = $tarif['harga'];
        $waktuMulai = null;
        $waktuSelesai = null;
    } else {
        $tglSelesai = $tglMulai;
        if (!$waktuMulai || !$waktuSelesai) $errors[] = 'Waktu mulai dan selesai wajib diisi.';
        if ($waktuMulai >= $waktuSelesai) $errors[] = 'Waktu mulai harus sebelum waktu selesai.';
        $durasi = ((int)substr($waktuSelesai,0,2) - (int)substr($waktuMulai,0,2));
        $totalBiaya = $durasi * $tarif['harga'];
    }

    // Check conflict
    if (empty($errors)) {
        if (checkScheduleConflict($pdo, $arenaId, $jenisSewa, $tglMulai, $tglSelesai, $waktuMulai, $waktuSelesai)) {
            $errors[] = 'Jadwal yang dipilih sudah terisi. Silakan pilih tanggal/waktu lain.';
        }
    }

    if (empty($errors)) {
        $kode = generateKode();
        $stmt = $pdo->prepare("INSERT INTO booking (penyewa_id,arena_id,tarif_id,kode_booking,tanggal_mulai,tanggal_selesai,waktu_mulai,waktu_selesai,total_biaya,status) VALUES (?,?,?,?,?,?,?,?,?,'pending')");
        $stmt->execute([$_SESSION['user_id'], $arenaId, $tarifId, $kode, $tglMulai, $tglSelesai, $waktuMulai, $waktuSelesai, $totalBiaya]);
        $bookingId = $pdo->lastInsertId();

        $arenaName = $pdo->prepare("SELECT nama_arena FROM arena WHERE id=?"); $arenaName->execute([$arenaId]);
        $newBooking = [
            'id' => $bookingId, 'kode' => $kode, 'arena' => $arenaName->fetchColumn(),
            'jenis' => $jenisSewa, 'tgl_mulai' => $tglMulai, 'tgl_selesai' => $tglSelesai,
            'waktu_mulai' => $waktuMulai, 'waktu_selesai' => $waktuSelesai,
            'total' => $totalBiaya,
        ];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">

<?php if ($newBooking): ?>
<!-- ══════ SUCCESS ══════ -->
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-2xl border border-surface-100 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-8 text-center text-white">
            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-check text-2xl"></i></div>
            <h2 class="text-lg font-bold">Booking Berhasil Dibuat</h2>
            <p class="text-emerald-100 text-sm mt-1">Upload bukti pembayaran untuk konfirmasi</p>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-surface-50 rounded-xl p-4 text-center">
                <p class="text-[10px] text-surface-400 uppercase tracking-wider">Kode Booking</p>
                <p class="text-2xl font-mono font-bold text-maroon-700 mt-1"><?= $newBooking['kode'] ?></p>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-[11px] text-surface-400">Arena</p><p class="font-medium text-surface-900"><?= sanitize($newBooking['arena']) ?></p></div>
                <div><p class="text-[11px] text-surface-400">Jenis Sewa</p><p class="font-medium text-surface-900"><?= $newBooking['jenis'] === 'event' ? 'Event (3 Hari)' : 'Per Jam' ?></p></div>
                <div><p class="text-[11px] text-surface-400">Tanggal</p><p class="font-medium text-surface-900"><?= formatTgl($newBooking['tgl_mulai']) ?><?= $newBooking['tgl_mulai'] !== $newBooking['tgl_selesai'] ? ' s/d ' . formatTgl($newBooking['tgl_selesai']) : '' ?></p></div>
                <?php if ($newBooking['waktu_mulai']): ?>
                <div><p class="text-[11px] text-surface-400">Waktu</p><p class="font-medium text-surface-900"><?= substr($newBooking['waktu_mulai'],0,5) ?> - <?= substr($newBooking['waktu_selesai'],0,5) ?></p></div>
                <?php endif; ?>
            </div>
            <div class="border-t border-surface-100 pt-4 flex justify-between items-center">
                <span class="text-sm text-surface-500">Total Biaya</span>
                <span class="text-xl font-bold text-surface-900"><?= formatRupiah($newBooking['total']) ?></span>
            </div>
            <div class="flex gap-3">
                <a href="<?= BASE_URL ?>/penyewa/riwayat.php?upload=<?= $newBooking['id'] ?>" class="flex-1 bg-maroon-700 hover:bg-maroon-800 text-white py-2.5 rounded-xl text-xs font-semibold text-center transition-all"><i class="fa-solid fa-upload mr-1"></i> Upload Bukti Bayar</a>
                <a href="<?= BASE_URL ?>/penyewa/riwayat.php" class="flex-1 bg-surface-100 hover:bg-surface-200 text-surface-700 py-2.5 rounded-xl text-xs font-medium text-center transition-all">Lihat Riwayat</a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ══════ BOOKING FORM ══════ -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-surface-900">Booking Arena</h1>
    <p class="text-sm text-surface-500 mt-1">Pilih arena, jenis sewa, dan jadwal yang tersedia</p>
</div>

<?php if ($errors): ?>
<div class="p-4 rounded-xl mb-6 bg-red-50 border border-red-200">
    <div class="flex items-start gap-2"><i class="fa-solid fa-circle-exclamation text-red-500 text-xs mt-0.5"></i><div class="text-sm text-red-700"><?php foreach($errors as $e) echo sanitize($e).'<br>';?></div></div>
</div>
<?php endif; ?>

<?php if (!$selectedArenaId): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <?php
    $covers = [
        'https://images.unsplash.com/photo-1534318542-7e47f2c05e5c?w=600&h=350&fit=crop',
        'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=600&h=350&fit=crop',
    ];
    
    foreach ($arenas as $i => $ar): 
        // Logika untuk menentukan source gambar
        $defaultImg = $covers[$i] ?? $covers[0];
        
        // Sesuaikan '/assets/uploads/' dengan nama folder tempat kamu menyimpan gambar dari admin
        $uploadDir = BASE_URL . '/assets/uploads/'; 
        
        // Jika foto_cover ada di database, gabungkan direktori dengan nama file. Jika kosong, pakai default.
        $imgSrc = !empty($ar['foto_cover']) ? $uploadDir . $ar['foto_cover'] : $defaultImg;
    ?>
    <a href="?arena_id=<?= $ar['id'] ?>" class="group bg-white rounded-2xl overflow-hidden border border-surface-100 hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <div class="h-40 overflow-hidden">
            <img src="<?= sanitize($imgSrc) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="<?= sanitize($ar['nama_arena']) ?>" loading="lazy">
        </div>
        <div class="p-5">
            <h3 class="font-bold text-surface-900 mb-1"><?= sanitize($ar['nama_arena']) ?></h3>
            <p class="text-xs text-surface-400 flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-maroon-400"></i>
                <?= sanitize($ar['lokasi']) ?>
            </p>
            <p class="text-xs text-surface-500 mt-2 line-clamp-2"><?= sanitize(substr($ar['deskripsi'],0,100)) ?>...</p>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php else: ?>
<!-- Step 2: Choose Tarif & Date -->
<?php
$selArena = null;
foreach ($arenas as $ar) { if ($ar['id'] == $selectedArenaId) { $selArena = $ar; break; } }
?>

<div class="bg-white rounded-2xl border border-surface-100 p-6">
    <div class="flex items-center gap-3 mb-6 pb-5 border-b border-surface-50">
        <a href="<?=BASE_URL?>/penyewa/booking.php" class="w-8 h-8 rounded-lg bg-surface-100 flex items-center justify-center text-surface-500 hover:bg-surface-200 transition-colors"><i class="fa-solid fa-arrow-left text-xs"></i></a>
        <div>
            <h2 class="font-bold text-surface-900"><?= sanitize($selArena['nama_arena']) ?></h2>
            <p class="text-xs text-surface-400"><?= sanitize($selArena['lokasi']) ?></p>
        </div>
    </div>

    <form method="POST" id="booking-form" class="space-y-5">
        <input type="hidden" name="arena_id" value="<?= $selectedArenaId ?>">
        <input type="hidden" name="submit_booking" value="1">

        <!-- Tarif Selection -->
        <div>
            <label class="block text-sm font-medium text-surface-700 mb-3">Pilih Jenis Sewa</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($tarifs as $t): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="tarif_id" value="<?= $t['id'] ?>" data-jenis="<?= $t['jenis_sewa'] ?>" data-harga="<?= $t['harga'] ?>" class="peer sr-only" required>
                    <div class="p-4 rounded-xl border-2 border-surface-200 peer-checked:border-maroon-600 peer-checked:bg-maroon-50/50 transition-all hover:border-surface-300">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fa-solid <?= $t['jenis_sewa']==='event'?'fa-calendar-star':'fa-clock' ?> text-maroon-600 text-xs"></i>
                            <span class="text-xs font-semibold text-surface-500 uppercase tracking-wider"><?= $t['jenis_sewa']==='event'?'Event (3 Hari)':'Per Jam' ?></span>
                        </div>
                        <p class="text-lg font-bold text-maroon-700"><?= formatRupiah($t['harga']) ?><span class="text-xs font-normal text-surface-400"><?= $t['jenis_sewa']==='event'?'/event':'/jam' ?></span></p>
                        <?php if ($t['keterangan']): ?><p class="text-xs text-surface-500 mt-1.5"><?= sanitize($t['keterangan']) ?></p><?php endif; ?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Date -->
        <div>
            <label class="block text-sm font-medium text-surface-700 mb-1.5">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" required min="<?= date('Y-m-d') ?>" class="w-full px-4 py-2.5 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none" id="tgl-mulai">
            <p class="text-xs text-surface-400 mt-1" id="event-hint" style="display:none"><i class="fa-solid fa-circle-info mr-1"></i>Event akan memblokir arena selama 3 hari (tanggal terpilih + 2 hari berikutnya).</p>
        </div>

        <!-- Time (only for per_jam) -->
        <div id="time-section" style="display:none">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1.5">Waktu Mulai</label>
                    <select name="waktu_mulai" class="w-full px-4 py-2.5 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none" id="waktu-mulai">
                        <option value="">Pilih</option>
                        <?php for($h=6;$h<=20;$h++): ?><option value="<?=sprintf('%02d:00',$h)?>"><?=sprintf('%02d:00',$h)?></option><?php endfor;?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1.5">Waktu Selesai</label>
                    <select name="waktu_selesai" class="w-full px-4 py-2.5 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none" id="waktu-selesai">
                        <option value="">Pilih</option>
                        <?php for($h=7;$h<=22;$h++): ?><option value="<?=sprintf('%02d:00',$h)?>"><?=sprintf('%02d:00',$h)?></option><?php endfor;?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-surface-50 rounded-xl p-4 border border-surface-100">
            <h4 class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Estimasi Biaya</h4>
            <div class="flex justify-between items-center">
                <span class="text-sm text-surface-600">Total</span>
                <span class="text-xl font-bold text-maroon-700" id="cost-display">Rp 0</span>
            </div>
        </div>

        <button type="submit" class="w-full bg-maroon-700 hover:bg-maroon-800 text-white py-3 rounded-xl text-sm font-semibold transition-all shadow-lg shadow-maroon-700/20 active:scale-[.98]">
            <i class="fa-solid fa-calendar-check mr-1.5 text-xs"></i> Buat Booking
        </button>
    </form>
</div>

<script>
document.querySelectorAll('input[name="tarif_id"]').forEach(r => {
    r.addEventListener('change', updateUI);
});
document.getElementById('waktu-mulai')?.addEventListener('change', updateCost);
document.getElementById('waktu-selesai')?.addEventListener('change', updateCost);

function updateUI() {
    const selected = document.querySelector('input[name="tarif_id"]:checked');
    if (!selected) return;
    const jenis = selected.dataset.jenis;
    document.getElementById('time-section').style.display = jenis === 'per_jam' ? 'block' : 'none';
    document.getElementById('event-hint').style.display = jenis === 'event' ? 'block' : 'none';
    updateCost();
}
function updateCost() {
    const selected = document.querySelector('input[name="tarif_id"]:checked');
    if (!selected) return;
    const harga = parseFloat(selected.dataset.harga);
    const jenis = selected.dataset.jenis;
    let total = 0;
    if (jenis === 'event') {
        total = harga;
    } else {
        const s = document.getElementById('waktu-mulai').value;
        const e = document.getElementById('waktu-selesai').value;
        if (s && e) {
            const dur = parseInt(e) - parseInt(s);
            if (dur > 0) total = dur * harga;
        }
    }
    document.getElementById('cost-display').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
</script>
<?php endif; ?>
<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
