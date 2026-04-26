<?php
$pageTitle   = 'Kelola Artikel';
$currentPage = 'artikel';
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if ($act === 'add') {
        $judul  = trim($_POST['judul']);
        $slug   = slugify($judul) . '-' . time();
        $konten = trim($_POST['konten']);
        $foto   = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_EXT) && $_FILES['foto']['size'] <= MAX_FILE_SIZE) {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                $foto = 'art_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $foto);
            }
        }

        $pdo->prepare("INSERT INTO artikel (judul,slug,konten,foto_thumbnail) VALUES (?,?,?,?)")
            ->execute([$judul, $slug, $konten, $foto]);
        flash('artikel', 'Artikel berhasil ditambahkan.', 'success');

    } elseif ($act === 'edit') {
        $id     = (int)$_POST['artikel_id'];
        $judul  = trim($_POST['judul']);
        $konten = trim($_POST['konten']);

        // Fetch existing foto so we only overwrite if a new file is uploaded
        $existing = $pdo->prepare("SELECT foto_thumbnail FROM artikel WHERE id=?");
        $existing->execute([$id]);
        $foto = $existing->fetchColumn();

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_EXT) && $_FILES['foto']['size'] <= MAX_FILE_SIZE) {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                // Remove old file if it exists
                if ($foto && file_exists(UPLOAD_DIR . $foto)) unlink(UPLOAD_DIR . $foto);
                $foto = 'art_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $foto);
            }
        }

        // Allow clearing the existing thumbnail
        if (isset($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1') {
            if ($foto && file_exists(UPLOAD_DIR . $foto)) unlink(UPLOAD_DIR . $foto);
            $foto = null;
        }

        $pdo->prepare("UPDATE artikel SET judul=?, konten=?, foto_thumbnail=? WHERE id=?")
            ->execute([$judul, $konten, $foto, $id]);
        flash('artikel', 'Artikel berhasil diperbarui.', 'success');

    } elseif ($act === 'delete') {
        // Remove associated thumbnail file before deleting the record
        $row = $pdo->prepare("SELECT foto_thumbnail FROM artikel WHERE id=?");
        $row->execute([(int)$_POST['artikel_id']]);
        $thumb = $row->fetchColumn();
        if ($thumb && file_exists(UPLOAD_DIR . $thumb)) unlink(UPLOAD_DIR . $thumb);

        $pdo->prepare("DELETE FROM artikel WHERE id=?")->execute([(int)$_POST['artikel_id']]);
        flash('artikel', 'Artikel berhasil dihapus.', 'success');
    }

    header('Location: ' . BASE_URL . '/admin/artikel.php');
    exit;
}

$articles = $pdo->query("SELECT * FROM artikel ORDER BY created_at DESC")->fetchAll();
$flash    = getFlash('artikel');
require_once __DIR__ . '/../includes/admin_header.php';
?>

<style>
    /* ------------------------------------------------------------------ */
    /* Modal animations                                                     */
    /* ------------------------------------------------------------------ */
    @keyframes modalBackdropIn  { from { opacity: 0; } to { opacity: 1; } }
    @keyframes modalBackdropOut { from { opacity: 1; } to { opacity: 0; } }
    @keyframes modalPanelIn {
        from { opacity: 0; transform: translateY(22px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0)    scale(1); }
    }
    @keyframes modalPanelOut {
        from { opacity: 1; transform: translateY(0)    scale(1); }
        to   { opacity: 0; transform: translateY(16px) scale(0.97); }
    }

    .modal-wrap {
        position: fixed;
        inset: 0;
        z-index: 999;
        overflow-y: auto;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        animation: modalBackdropIn 0.22s ease both;
    }
    .modal-wrap.closing { animation: modalBackdropOut 0.2s ease both; }

    .modal-inner {
        display: flex;
        justify-content: center;
        min-height: 100%;
        padding: 48px 16px;
        box-sizing: border-box;
    }
    .modal-panel {
        background: #fff;
        border-radius: 20px;
        width: 100%;
        max-width: 560px;
        height: fit-content;
        box-shadow: 0 24px 64px rgba(0,0,0,0.16), 0 4px 16px rgba(0,0,0,0.08);
        animation: modalPanelIn 0.28s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .modal-wrap.closing .modal-panel { animation: modalPanelOut 0.2s ease both; }

    .modal-hidden { display: none; }

    /* Modal header */
    .modal-header {
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* ------------------------------------------------------------------ */
    /* Table row animations                                                 */
    /* ------------------------------------------------------------------ */
    @keyframes rowFadeIn {
        from { opacity: 0; transform: translateX(-8px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    tbody tr { animation: rowFadeIn 0.3s ease both; }
    tbody tr:nth-child(1)  { animation-delay: 0.04s; }
    tbody tr:nth-child(2)  { animation-delay: 0.08s; }
    tbody tr:nth-child(3)  { animation-delay: 0.12s; }
    tbody tr:nth-child(4)  { animation-delay: 0.16s; }
    tbody tr:nth-child(5)  { animation-delay: 0.20s; }
    tbody tr:nth-child(6)  { animation-delay: 0.24s; }
    tbody tr:nth-child(7)  { animation-delay: 0.28s; }
    tbody tr:nth-child(8)  { animation-delay: 0.32s; }
    tbody tr:nth-child(9)  { animation-delay: 0.36s; }
    tbody tr:nth-child(10) { animation-delay: 0.40s; }

    /* ------------------------------------------------------------------ */
    /* Action button micro-styles                                           */
    /* ------------------------------------------------------------------ */
    .tbl-btn {
        width: 28px; height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        background: transparent;
        transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }
    .tbl-btn:hover { transform: translateY(-1px); }
    .tbl-btn-edit   { color: #64748b; }
    .tbl-btn-edit:hover   { background: #eff6ff; color: #1d4ed8; }
    .tbl-btn-delete { color: #94a3b8; }
    .tbl-btn-delete:hover { background: #fef2f2; color: #dc2626; }

    /* ------------------------------------------------------------------ */
    /* Form inputs focus ring                                               */
    /* ------------------------------------------------------------------ */
    .f-input {
        width: 100%;
        padding: 9px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        color: #0f172a;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.25s ease, background 0.2s ease;
        font-family: inherit;
    }
    .f-input:focus {
        border-color: #991b1b;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(153,27,27,0.08);
    }
    textarea.f-input { resize: vertical; min-height: 140px; }

    /* File input */
    .f-file {
        width: 100%;
        padding: 8px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 12px;
        color: #475569;
        outline: none;
        cursor: pointer;
        transition: border-color 0.2s ease;
    }
    .f-file:focus { border-color: #991b1b; }
    .f-file::file-selector-button {
        margin-right: 10px;
        padding: 3px 10px;
        border-radius: 6px;
        border: none;
        background: #fef2f2;
        color: #991b1b;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.18s ease;
    }
    .f-file::file-selector-button:hover { background: #fee2e2; }

    /* Thumbnail preview */
    #edit-thumb-preview {
        width: 64px; height: 64px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }

    /* Flash notice */
    @keyframes flashSlide {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    [data-flash] { animation: flashSlide 0.35s ease both; }

    /* Label */
    .f-label {
        display: block;
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 6px;
    }
</style>

<!-- Page header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-surface-900">Kelola Artikel</h1>
        <p class="text-xs text-surface-500 mt-0.5">Tambah dan kelola berita serta informasi</p>
    </div>
    <button
        onclick="openModal('add')"
        class="bg-maroon-700 hover:bg-maroon-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-plus text-[10px]"></i>
        Artikel Baru
    </button>
</div>

<!-- Flash notice -->
<?php if ($flash): ?>
<div data-flash class="p-3.5 rounded-xl mb-5 text-sm bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2">
    <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
    <?= sanitize($flash['message']) ?>
</div>
<?php endif; ?>

<!-- Articles table -->
<div class="bg-white rounded-2xl border border-surface-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[10px] font-semibold text-surface-400 uppercase tracking-wider bg-surface-50">
                    <th class="px-5 py-3 text-left">Judul</th>
                    <th class="px-5 py-3 text-left">Tanggal</th>
                    <th class="px-5 py-3 text-left">Foto</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-50">
                <?php if (empty($articles)): ?>
                <tr>
                    <td colspan="4" class="px-5 py-12 text-center text-surface-400 text-xs">
                        <i class="fa-regular fa-newspaper text-2xl block mb-2 opacity-30"></i>
                        Belum ada artikel. Klik "Artikel Baru" untuk memulai.
                    </td>
                </tr>
                <?php else: foreach ($articles as $art): ?>
                <tr class="hover:bg-surface-50/60 transition-colors duration-150">
                    <td class="px-5 py-3 font-medium text-surface-900 max-w-xs">
                        <span class="line-clamp-2 leading-snug"><?= sanitize($art['judul']) ?></span>
                    </td>
                    <td class="px-5 py-3 text-surface-400 text-xs whitespace-nowrap">
                        <?= date('d M Y', strtotime($art['created_at'])) ?>
                    </td>
                    <td class="px-5 py-3">
                        <?php if ($art['foto_thumbnail']): ?>
                            <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium">
                                <i class="fa-solid fa-image text-emerald-500"></i> Ada
                            </span>
                        <?php else: ?>
                            <span class="text-surface-300 text-xs">Tidak ada</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1">
                            <!-- Edit button -->
                            <button
                                class="tbl-btn tbl-btn-edit"
                                title="Edit artikel"
                                onclick="openModal('edit', <?= htmlspecialchars(json_encode([
                                    'id'             => $art['id'],
                                    'judul'          => $art['judul'],
                                    'konten'         => $art['konten'],
                                    'foto_thumbnail' => $art['foto_thumbnail'],
                                ]), ENT_QUOTES) ?>)">
                                <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
                            </button>
                            <!-- Delete button -->
                            <form method="POST" onsubmit="return confirm('Hapus artikel ini? Tindakan tidak dapat dibatalkan.')" class="inline">
                                <input type="hidden" name="action"     value="delete">
                                <input type="hidden" name="artikel_id" value="<?= $art['id'] ?>">
                                <button type="submit" class="tbl-btn tbl-btn-delete" title="Hapus artikel">
                                    <i class="fa-solid fa-trash-can" style="font-size:10px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================== -->
<!-- Unified Modal (Add / Edit)                                          -->
<!-- ================================================================== -->

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<div id="modal-art" class="modal-hidden modal-wrap" onclick="handleBackdropClick(event)">
<div class="modal-inner" onclick="event.target===this.parentElement&&closeModal()">
    <div class="modal-panel" onclick="event.stopPropagation()">

        <!-- Sticky header -->
        <div class="modal-header">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-maroon-50 flex items-center justify-center">
                    <i id="modal-icon" class="fa-solid fa-plus text-maroon-700" style="font-size:11px;"></i>
                </div>
                <h2 id="modal-title" class="font-semibold text-surface-900 text-sm">Artikel Baru</h2>
            </div>
            <button onclick="closeModal()" class="w-7 h-7 rounded-lg bg-surface-50 hover:bg-surface-100 flex items-center justify-center text-surface-400 hover:text-surface-600 transition-colors">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <!-- Form body -->
        <form method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            <input type="hidden" name="action"     id="form-action"     value="add">
            <input type="hidden" name="artikel_id" id="form-artikel-id" value="">
            <input type="hidden" name="hapus_foto" id="form-hapus-foto" value="0">

            <!-- Judul -->
            <div>
                <label class="f-label">Judul Artikel</label>
                <input type="text" name="judul" id="form-judul" required
                       class="f-input" placeholder="Masukkan judul artikel">
            </div>

            <!-- Konten -->
            <div>
                <label class="f-label">Konten</label>
                <textarea name="konten" id="form-konten" required
                          class="f-input" placeholder="Tulis isi artikel di sini..."></textarea>
            </div>

            <!-- Foto thumbnail -->
            <div>
                <label class="f-label">Foto Thumbnail <span class="normal-case text-surface-300 font-normal">(opsional)</span></label>

                <!-- Current thumbnail preview (edit mode only) -->
                <div id="current-thumb-wrap" class="hidden flex items-center gap-3 mb-3 p-3 bg-surface-50 border border-surface-200 rounded-xl">
                    <img id="edit-thumb-preview" src="" alt="Thumbnail saat ini">
                    <div class="flex-1 min-w-0">
                        <p id="edit-thumb-name" class="text-xs font-medium text-surface-700 truncate"></p>
                        <p class="text-[10px] text-surface-400 mt-0.5">Thumbnail saat ini</p>
                    </div>
                    <button type="button" onclick="clearThumbnail()"
                        class="w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 flex items-center justify-center text-red-400 hover:text-red-600 transition-colors flex-shrink-0"
                        title="Hapus thumbnail">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <input type="file" name="foto" id="form-foto"
                       accept=".jpg,.jpeg,.png,.webp"
                       class="f-file">
                <p class="text-[10px] text-surface-400 mt-1.5">Format: JPG, PNG, WEBP. Maks. ukuran file sesuai konfigurasi.</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal()"
                    class="flex-1 py-2.5 bg-surface-100 hover:bg-surface-200 text-surface-700 rounded-xl text-xs font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" id="modal-submit"
                    class="flex-1 py-2.5 bg-maroon-700 hover:bg-maroon-800 text-white rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-1.5">
                    <i id="submit-icon" class="fa-solid fa-plus" style="font-size:9px;"></i>
                    <span id="submit-label">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
    var modal       = document.getElementById('modal-art');
    var modalTitle  = document.getElementById('modal-title');
    var modalIcon   = document.getElementById('modal-icon');
    var submitIcon  = document.getElementById('submit-icon');
    var submitLabel = document.getElementById('submit-label');

    var formAction    = document.getElementById('form-action');
    var formArtikelId = document.getElementById('form-artikel-id');
    var formJudul     = document.getElementById('form-judul');
    var formKonten    = document.getElementById('form-konten');
    var formFoto      = document.getElementById('form-foto');
    var formHapusFoto = document.getElementById('form-hapus-foto');

    var thumbWrap    = document.getElementById('current-thumb-wrap');
    var thumbPreview = document.getElementById('edit-thumb-preview');
    var thumbName    = document.getElementById('edit-thumb-name');

    // Base URL for upload directory passed from PHP
    var uploadBase = '<?= BASE_URL ?>/uploads/';

    function openModal(mode, data) {
        modal.classList.remove('closing');

        // Reset hapus_foto flag and file input
        formHapusFoto.value = '0';
        formFoto.value      = '';

        if (mode === 'add') {
            modalTitle.textContent  = 'Artikel Baru';
            modalIcon.className     = 'fa-solid fa-plus text-maroon-700';
            submitIcon.className    = 'fa-solid fa-plus';
            submitLabel.textContent = 'Simpan';

            formAction.value    = 'add';
            formArtikelId.value = '';
            formJudul.value     = '';
            formKonten.value    = '';

            thumbWrap.classList.add('hidden');

        } else if (mode === 'edit' && data) {
            modalTitle.textContent  = 'Edit Artikel';
            modalIcon.className     = 'fa-solid fa-pen-to-square text-maroon-700';
            submitIcon.className    = 'fa-solid fa-floppy-disk';
            submitLabel.textContent = 'Perbarui';

            formAction.value    = 'edit';
            formArtikelId.value = data.id;
            formJudul.value     = data.judul;
            formKonten.value    = data.konten;

            // Show existing thumbnail if available
            if (data.foto_thumbnail) {
                thumbPreview.src      = uploadBase + data.foto_thumbnail;
                thumbName.textContent = data.foto_thumbnail;
                thumbWrap.classList.remove('hidden');
            } else {
                thumbWrap.classList.add('hidden');
            }
        }

        modal.classList.remove('modal-hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('closing');
        setTimeout(function () {
            modal.classList.add('modal-hidden');
            modal.classList.remove('closing');
            document.body.style.overflow = '';
        }, 200);
    }

    function handleBackdropClick(e) {
        if (e.target === modal) closeModal();
    }

    function clearThumbnail() {
        formHapusFoto.value = '1';
        thumbWrap.classList.add('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('modal-hidden')) {
            closeModal();
        }
    });
</script>