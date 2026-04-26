<?php
$pageTitle = 'Kelola Tarif';
$currentPage = 'tarif';
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if ($act === 'add') {
        $pdo->prepare("INSERT INTO tarif (arena_id,jenis_sewa,harga,keterangan) VALUES (?,?,?,?)")
            ->execute([
                (int)$_POST['arena_id'],
                $_POST['jenis_sewa'],
                (float)$_POST['harga'],
                trim($_POST['keterangan'] ?? '')
            ]);
        flash('tarif', 'Tarif baru berhasil ditambahkan.', 'success');

    } elseif ($act === 'edit') {
        $pdo->prepare("UPDATE tarif SET arena_id=?, jenis_sewa=?, harga=?, keterangan=? WHERE id=?")
            ->execute([
                (int)$_POST['arena_id'],
                $_POST['jenis_sewa'],
                (float)$_POST['harga'],
                trim($_POST['keterangan'] ?? ''),
                (int)$_POST['tarif_id']
            ]);
        flash('tarif', 'Tarif berhasil diperbarui.', 'success');

    } elseif ($act === 'delete') {
        $pdo->prepare("DELETE FROM tarif WHERE id=?")->execute([(int)$_POST['tarif_id']]);
        flash('tarif', 'Tarif berhasil dihapus.', 'success');
    }

    header('Location: ' . BASE_URL . '/admin/tarif.php');
    exit;
}

$tarifs = $pdo->query("SELECT t.*, a.nama_arena FROM tarif t JOIN arena a ON t.arena_id=a.id ORDER BY a.id, t.jenis_sewa")->fetchAll();
$arenas = $pdo->query("SELECT * FROM arena ORDER BY id")->fetchAll();
$flash  = getFlash('tarif');

require_once __DIR__ . '/../includes/admin_header.php';
?>

<style>
    /* ------------------------------------------------------------------ */
    /* Modal animation                                                      */
    /* ------------------------------------------------------------------ */
    @keyframes modalBackdropIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes modalBackdropOut {
        from { opacity: 1; }
        to   { opacity: 0; }
    }
    @keyframes modalPanelIn {
        from { opacity: 0; transform: translateY(20px) scale(0.97); }
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
        background: rgba(0,0,0,0.38);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        animation: modalBackdropIn 0.22s ease both;
    }
    .modal-wrap.closing {
        animation: modalBackdropOut 0.2s ease both;
    }
    .modal-inner {
        display: flex;
        justify-content: center;
        min-height: 100%;
        padding: 48px 16px;
        box-sizing: border-box;
    }
    .modal-panel {
        background: #ffffff;
        border-radius: 20px;
        width: 100%;
        max-width: 440px;
        height: fit-content;
        box-shadow: 0 24px 64px rgba(0,0,0,0.15), 0 4px 16px rgba(0,0,0,0.08);
        animation: modalPanelIn 0.28s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .modal-wrap.closing .modal-panel {
        animation: modalPanelOut 0.2s ease both;
    }

    /* Hidden state */
    .modal-hidden { display: none; }

    /* Card action buttons */
    .card-action-btn {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
        background: transparent;
        flex-shrink: 0;
    }
    .card-action-btn:hover { transform: translateY(-1px); }

    .btn-edit  { color: #64748b; }
    .btn-edit:hover  { background: #eff6ff; color: #1d4ed8; }
    .btn-delete { color: #94a3b8; }
    .btn-delete:hover { background: #fef2f2; color: #dc2626; }

    /* Tarif card entrance */
    @keyframes cardFadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .tarif-card {
        animation: cardFadeIn 0.4s ease both;
    }

    /* Flash notice */
    @keyframes flashSlideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    [data-flash] { animation: flashSlideIn 0.35s ease both; }
</style>

<!-- Page header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-surface-900">Kelola Tarif</h1>
        <p class="text-xs text-surface-500 mt-0.5">Atur harga sewa per jam dan event</p>
    </div>
    <button
        onclick="openModal('add')"
        class="bg-maroon-700 hover:bg-maroon-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-plus text-[10px]"></i>
        Tambah Tarif
    </button>
</div>

<!-- Flash notice -->
<?php if ($flash): ?>
<div data-flash class="p-3.5 rounded-xl mb-5 text-sm bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2">
    <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
    <?= sanitize($flash['message']) ?>
</div>
<?php endif; ?>

<!-- Tarif cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
<?php foreach ($tarifs as $index => $t): ?>
<div class="tarif-card bg-white rounded-2xl border border-surface-100 p-5 hover:shadow-md hover:border-surface-200 transition-all duration-200"
     style="animation-delay: <?= $index * 0.06 ?>s">

    <div class="flex items-start justify-between mb-3">
        <div class="flex-1 min-w-0 pr-3">
            <span class="text-[10px] bg-surface-100 text-surface-500 px-2 py-0.5 rounded-full font-medium uppercase tracking-wide">
                <?= $t['jenis_sewa'] === 'event' ? 'Event' : 'Per Jam' ?>
            </span>
            <h3 class="font-semibold text-surface-900 mt-1.5 truncate"><?= sanitize($t['nama_arena']) ?></h3>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
            <!-- Edit button -->
            <button
                class="card-action-btn btn-edit"
                title="Edit tarif"
                onclick="openModal('edit', <?= htmlspecialchars(json_encode([
                    'id'         => $t['id'],
                    'arena_id'   => $t['arena_id'],
                    'jenis_sewa' => $t['jenis_sewa'],
                    'harga'      => $t['harga'],
                    'keterangan' => $t['keterangan'] ?? '',
                ]), ENT_QUOTES) ?>)">
                <i class="fa-solid fa-pen-to-square" style="font-size:11px;"></i>
            </button>
            <!-- Delete button -->
            <form method="POST" onsubmit="return confirm('Hapus tarif ini? Tindakan tidak dapat dibatalkan.')">
                <input type="hidden" name="action"   value="delete">
                <input type="hidden" name="tarif_id" value="<?= $t['id'] ?>">
                <button type="submit" class="card-action-btn btn-delete" title="Hapus tarif">
                    <i class="fa-solid fa-trash-can" style="font-size:10px;"></i>
                </button>
            </form>
        </div>
    </div>

    <p class="text-2xl font-bold text-maroon-700 mb-1.5">
        <?= formatRupiah($t['harga']) ?>
        <span class="text-xs font-normal text-surface-400"><?= $t['jenis_sewa'] === 'event' ? '/event' : '/jam' ?></span>
    </p>
    <?php if ($t['keterangan']): ?>
    <p class="text-xs text-surface-400 leading-relaxed"><?= sanitize($t['keterangan']) ?></p>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<?php if (empty($tarifs)): ?>
<div class="col-span-full py-16 text-center text-surface-400">
    <i class="fa-solid fa-tags text-3xl mb-3 opacity-30 block"></i>
    <p class="text-sm">Belum ada tarif. Klik "Tambah Tarif" untuk memulai.</p>
</div>
<?php endif; ?>
</div>

<!-- ================================================================== -->
<!-- Unified Modal (Add / Edit)                                          -->
<!-- ================================================================== -->

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
<div id="modal-tarif" class="modal-hidden modal-wrap" onclick="handleBackdropClick(event)">
<div class="modal-inner" onclick="event.target===this.parentElement&&closeModal()">
    <div class="modal-panel" onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="px-5 py-4 border-b border-surface-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-maroon-50 flex items-center justify-center">
                    <i id="modal-icon" class="fa-solid fa-plus text-maroon-700" style="font-size:11px;"></i>
                </div>
                <h2 id="modal-title" class="font-semibold text-surface-900 text-sm">Tambah Tarif</h2>
            </div>
            <button onclick="closeModal()" class="w-7 h-7 rounded-lg bg-surface-50 hover:bg-surface-100 flex items-center justify-center text-surface-400 hover:text-surface-600 transition-colors">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" class="p-5 space-y-4" id="modal-form">
            <input type="hidden" name="action"   id="form-action"   value="add">
            <input type="hidden" name="tarif_id" id="form-tarif-id" value="">

            <!-- Arena -->
            <div>
                <label class="block text-xs font-medium text-surface-600 mb-1.5">Arena</label>
                <select name="arena_id" id="form-arena-id" required
                    class="w-full px-3 py-2.5 bg-surface-50 border border-surface-200 rounded-xl text-sm outline-none focus:border-maroon-500 focus:ring-2 focus:ring-maroon-500/15 transition-all">
                    <?php foreach ($arenas as $ar): ?>
                    <option value="<?= $ar['id'] ?>"><?= sanitize($ar['nama_arena']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Jenis sewa -->
            <div>
                <label class="block text-xs font-medium text-surface-600 mb-1.5">Jenis Sewa</label>
                <select name="jenis_sewa" id="form-jenis-sewa" required
                    class="w-full px-3 py-2.5 bg-surface-50 border border-surface-200 rounded-xl text-sm outline-none focus:border-maroon-500 focus:ring-2 focus:ring-maroon-500/15 transition-all">
                    <option value="per_jam">Per Jam</option>
                    <option value="event">Event (3 Hari)</option>
                </select>
            </div>

            <!-- Harga -->
            <div>
                <label class="block text-xs font-medium text-surface-600 mb-1.5">Harga (Rp)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-surface-400 text-xs font-medium pointer-events-none">Rp</span>
                    <input type="number" name="harga" id="form-harga" required min="0" step="1000"
                        class="w-full pl-8 pr-3 py-2.5 bg-surface-50 border border-surface-200 rounded-xl text-sm outline-none focus:border-maroon-500 focus:ring-2 focus:ring-maroon-500/15 transition-all"
                        placeholder="0">
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-medium text-surface-600 mb-1.5">Keterangan <span class="text-surface-300 font-normal">(opsional)</span></label>
                <input type="text" name="keterangan" id="form-keterangan"
                    class="w-full px-3 py-2.5 bg-surface-50 border border-surface-200 rounded-xl text-sm outline-none focus:border-maroon-500 focus:ring-2 focus:ring-maroon-500/15 transition-all"
                    placeholder="Keterangan tambahan">
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModal()"
                    class="flex-1 py-2.5 bg-surface-100 hover:bg-surface-200 text-surface-700 rounded-xl text-xs font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" id="modal-submit"
                    class="flex-1 py-2.5 bg-maroon-700 hover:bg-maroon-800 text-white rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-1.5">
                    <i id="submit-icon" class="fa-solid fa-plus text-[9px]"></i>
                    <span id="submit-label">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
    var modal       = document.getElementById('modal-tarif');
    var modalTitle  = document.getElementById('modal-title');
    var modalIcon   = document.getElementById('modal-icon');
    var submitIcon  = document.getElementById('submit-icon');
    var submitLabel = document.getElementById('submit-label');

    // Open modal in add or edit mode
    function openModal(mode, data) {
        // Reset closing class if re-opened quickly
        modal.classList.remove('closing');

        if (mode === 'add') {
            modalTitle.textContent  = 'Tambah Tarif';
            modalIcon.className     = 'fa-solid fa-plus text-maroon-700';
            submitIcon.className    = 'fa-solid fa-plus';
            submitLabel.textContent = 'Simpan';

            document.getElementById('form-action').value    = 'add';
            document.getElementById('form-tarif-id').value  = '';
            document.getElementById('form-arena-id').value  = document.getElementById('form-arena-id').options[0]?.value ?? '';
            document.getElementById('form-jenis-sewa').value = 'per_jam';
            document.getElementById('form-harga').value     = '';
            document.getElementById('form-keterangan').value = '';

        } else if (mode === 'edit' && data) {
            modalTitle.textContent  = 'Edit Tarif';
            modalIcon.className     = 'fa-solid fa-pen-to-square text-maroon-700';
            submitIcon.className    = 'fa-solid fa-floppy-disk';
            submitLabel.textContent = 'Perbarui';

            document.getElementById('form-action').value     = 'edit';
            document.getElementById('form-tarif-id').value   = data.id;
            document.getElementById('form-arena-id').value   = data.arena_id;
            document.getElementById('form-jenis-sewa').value = data.jenis_sewa;
            document.getElementById('form-harga').value      = data.harga;
            document.getElementById('form-keterangan').value = data.keterangan ?? '';
        }

        modal.classList.remove('modal-hidden');
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    // Close modal with exit animation
    function closeModal() {
        modal.classList.add('closing');
        setTimeout(function () {
            modal.classList.add('modal-hidden');
            modal.classList.remove('closing');
            document.body.style.overflow = '';
        }, 200);
    }

    // Close when clicking the backdrop
    function handleBackdropClick(e) {
        if (e.target === modal) {
            closeModal();
        }
    }

    // Close with Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('modal-hidden')) {
            closeModal();
        }
    });
</script>