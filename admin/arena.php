<?php
$pageTitle = 'Kelola Arena'; $currentPage = 'arena';
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'update') {
        $id = (int)$_POST['arena_id'];
        $pdo->prepare("UPDATE arena SET nama_arena=?, deskripsi=?, fasilitas=?, kapasitas_max=?, lokasi=? WHERE id=?")
            ->execute([trim($_POST['nama_arena']),trim($_POST['deskripsi']),trim($_POST['fasilitas']),(int)$_POST['kapasitas_max'],trim($_POST['lokasi']),$id]);

        // Handle cover upload
        if (isset($_FILES['foto_cover']) && $_FILES['foto_cover']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto_cover']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_EXT) && $_FILES['foto_cover']['size'] <= MAX_FILE_SIZE) {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                $fn = 'arena_' . $id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto_cover']['tmp_name'], UPLOAD_DIR . $fn);
                $pdo->prepare("UPDATE arena SET foto_cover=? WHERE id=?")->execute([$fn, $id]);
            }
        }
        flash('arena', 'Arena berhasil diperbarui.', 'success');
    }
    header('Location: ' . BASE_URL . '/admin/arena.php'); exit;
}

$arenas = $pdo->query("SELECT * FROM arena ORDER BY id")->fetchAll();
$flash = getFlash('arena');
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="mb-6"><h1 class="text-xl font-bold text-surface-900">Kelola Arena</h1><p class="text-xs text-surface-500 mt-0.5">Edit informasi dan foto arena</p></div>

<?php if($flash):?><div data-flash class="p-3.5 rounded-xl mb-5 text-sm bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2 transition-all"><i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i><?=sanitize($flash['message'])?></div><?php endif;?>

<div class="space-y-5">
<?php foreach($arenas as $a):?>
<div class="bg-white rounded-2xl border border-surface-100 overflow-hidden">
    <div class="p-5">
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="arena_id" value="<?=$a['id']?>">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-surface-100 rounded-xl overflow-hidden flex-shrink-0">
                    <?php if($a['foto_cover']):?><img src="<?=BASE_URL?>/assets/uploads/<?=$a['foto_cover']?>" class="w-full h-full object-cover"><?php else:?><div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-building text-surface-300"></i></div><?php endif;?>
                </div>
                <div><h3 class="font-bold text-surface-900"><?=sanitize($a['nama_arena'])?></h3><p class="text-xs text-surface-400">ID: <?=$a['id']?></p></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-xs font-medium text-surface-600 mb-1">Nama Arena</label><input type="text" name="nama_arena" value="<?=sanitize($a['nama_arena'])?>" required class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none"></div>
                <div><label class="block text-xs font-medium text-surface-600 mb-1">Lokasi</label><input type="text" name="lokasi" value="<?=sanitize($a['lokasi'])?>" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none"></div>
                <div><label class="block text-xs font-medium text-surface-600 mb-1">Kapasitas Max</label><input type="number" name="kapasitas_max" value="<?=$a['kapasitas_max']?>" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none"></div>
                <div><label class="block text-xs font-medium text-surface-600 mb-1">Foto Cover</label><input type="file" name="foto_cover" accept=".jpg,.jpeg,.png,.webp" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-xs file:mr-2 file:py-0.5 file:px-2 file:rounded file:border-0 file:bg-maroon-50 file:text-maroon-700 file:text-xs"></div>
            </div>
            <div><label class="block text-xs font-medium text-surface-600 mb-1">Deskripsi</label><textarea name="deskripsi" rows="2" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none resize-none"><?=sanitize($a['deskripsi'])?></textarea></div>
            <div><label class="block text-xs font-medium text-surface-600 mb-1">Fasilitas</label><textarea name="fasilitas" rows="2" class="w-full px-3 py-2 bg-surface-50 border border-surface-200 rounded-xl text-sm focus:ring-2 focus:ring-maroon-500/20 focus:border-maroon-500 outline-none resize-none"><?=sanitize($a['fasilitas'])?></textarea></div>
            <button type="submit" class="bg-maroon-700 hover:bg-maroon-800 text-white px-5 py-2 rounded-xl text-xs font-semibold transition-all"><i class="fa-solid fa-floppy-disk mr-1"></i>Simpan</button>
        </form>
    </div>
</div>
<?php endforeach;?>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
