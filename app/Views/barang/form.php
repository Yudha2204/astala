<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$isEdit = $mode === 'edit';
$action = $isEdit ? site_url('barang/edit/' . $barang['id']) : site_url('barang/add');
?>

<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= site_url('barang') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= $isEdit ? 'Edit Barang' : 'Tambah Barang Baru' ?></h1>
                <p class="text-gray-500 dark:text-gray-400"><?= $isEdit ? esc($barang['nama_barang']) : 'Lengkapi form di bawah untuk menambah barang' ?></p>
            </div>
        </div>
        <?php if ($isEdit): ?>
            <div class="flex gap-3">
                <button type="button" onclick="openDeleteModal()" class="px-4 py-2 bg-red-100 text-red-600 font-medium rounded-lg hover:bg-red-200 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 transition-colors">Hapus</button>
                <button type="submit" form="barangForm" class="px-4 py-2 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 shadow-lg shadow-blue-500/25 transition-all">Simpan Perubahan</button>
            </div>
        <?php endif ?>
    </div>

    <form id="barangForm" action="<?= $action ?>" method="POST" enctype="multipart/form-data" class="w-full">
        <input type="hidden" name="deleted_photos" id="deleted_photos" value="[]">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 space-y-6 shadow-sm">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Barang *</label>
                <input type="text" name="nama_barang" id="nama_barang" required list="nama_list" value="<?= esc($barang['nama_barang'] ?? '') ?>" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Ketik nama barang atau pilih dari daftar">
                <datalist id="nama_list">
                    <?php foreach ($existingNames as $name): ?><option value="<?= esc($name) ?>"><?php endforeach ?>
                </datalist>
                <?php if (! $isEdit): ?><p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ketik nama baru atau pilih dari daftar yang sudah ada</p><?php endif ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nomor Seri / Barcode *</label>
                <div class="flex gap-2">
                    <input type="text" name="nomor_seri" id="nomor_seri" required value="<?= esc($barang['nomor_seri'] ?? '') ?>" class="flex-1 min-w-0 px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan nomor seri atau scan barcode">
                    <button type="button" onclick="openScanner('nomor_seri')" class="px-4 py-3 bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500 dark:hover:bg-blue-500/20 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori</label>
                    <input type="text" name="kategori" value="<?= esc($barang['kategori'] ?? '') ?>" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: Elektronik, Tools, dll">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kondisi Barang *</label>
                    <select name="status_kondisi" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="baik" <?= ($barang['status_kondisi'] ?? 'baik') === 'baik' ? 'selected' : '' ?>>Baik</option>
                        <option value="rusak" <?= ($barang['status_kondisi'] ?? '') === 'rusak' ? 'selected' : '' ?>>Rusak</option>
                    </select>
                </div>
            </div>

            <?php if ($isEdit): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ketersediaan</label>
                    <select name="status_ketersediaan" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="tersedia" <?= ($barang['status_ketersediaan'] ?? 'tersedia') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="dipinjam" <?= ($barang['status_ketersediaan'] ?? '') === 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                    </select>
                </div>
            <?php endif ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lokasi Penyimpanan</label>
                <input type="text" name="lokasi_penyimpanan" value="<?= esc($barang['lokasi_penyimpanan'] ?? '') ?>" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: Gudang A, Rak 1, dll">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="Deskripsi barang (opsional)"><?= esc($barang['deskripsi'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto Barang (Maks 5 foto)</label>
                <?php if ($isEdit && ! empty($barang['fotos'])): ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Foto saat ini (<?= count($barang['fotos']) ?>/5):</p>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4" id="existing_photos">
                        <?php foreach ($barang['fotos'] as $foto): ?>
                            <div class="relative aspect-square bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden" data-photo-id="<?= esc($foto['id']) ?>">
                                <img src="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" class="w-full h-full object-cover">
                                <button type="button" onclick="markPhotoForDeletion(<?= esc($foto['id']) ?>, this)" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600">x</button>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <div class="space-y-4">
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500 dark:hover:bg-blue-500/20 rounded-lg transition-colors cursor-pointer">
                        Upload Foto
                        <input type="file" name="fotos[]" multiple accept="image/*" class="hidden" id="foto_input" onchange="previewPhotos(this)">
                    </label>
                    <div id="photo_preview" class="grid grid-cols-2 md:grid-cols-5 gap-4 hidden"></div>
                </div>
            </div>

            <?php if (! $isEdit): ?>
                <div class="flex justify-end gap-4 pt-4">
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/25">Simpan Barang</button>
                    <a href="<?= site_url('barang') ?>" class="px-6 py-3 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">Batal</a>
                </div>
            <?php endif ?>
        </div>
    </form>

    <?php if ($isEdit): ?>
        <div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl border border-gray-200 dark:border-gray-700">
                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Hapus Barang?</h3>
                    <p class="text-gray-600 dark:text-gray-400">Apakah Anda yakin ingin menghapus <strong><?= esc($barang['nama_barang']) ?></strong>?<br>Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <form action="<?= site_url('barang/delete/' . $barang['id']) ?>" method="POST" class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">Ya, Hapus</button>
                </form>
            </div>
        </div>
    <?php endif ?>
</div>

<script>
let photoCount = 0;
let deletedPhotos = [];

function previewPhotos(input) {
    const preview = document.getElementById('photo_preview');
    preview.innerHTML = '';
    photoCount = 0;
    if (input.files.length > 0) preview.classList.remove('hidden');
    Array.from(input.files).slice(0, 5).forEach(file => {
        const reader = new FileReader();
        reader.onload = event => {
            const div = document.createElement('div');
            div.className = 'relative aspect-square bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden';
            div.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
            preview.appendChild(div);
            photoCount++;
        };
        reader.readAsDataURL(file);
    });
}

function markPhotoForDeletion(photoId, btn) {
    deletedPhotos.push(photoId);
    document.getElementById('deleted_photos').value = JSON.stringify(deletedPhotos);
    btn.parentElement.remove();
}

function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
<?= $this->endSection() ?>
