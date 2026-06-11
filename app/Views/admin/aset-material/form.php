<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$isEdit = $mode === 'edit';
$action = $isEdit ? site_url('admin/aset-material/edit/' . $aset['id']) : site_url('admin/aset-material/add');
$oldOr = static fn (string $key, $default = '') => old($key) !== null ? old($key) : $default;
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('admin/aset-material') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= $isEdit ? 'Edit Aset' : 'Tambah Aset Material' ?></h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
        <form action="<?= $action ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gudang *</label>
                <select name="gudang_id" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                    <option value="">Pilih Gudang</option>
                    <?php foreach ($gudangs as $gudang): ?>
                        <option value="<?= esc($gudang['id']) ?>" <?= (int) $oldOr('gudang_id', $aset['gudang_id'] ?? 0) === (int) $gudang['id'] ? 'selected' : '' ?>><?= esc($gudang['nama']) ?> (<?= esc($gudang['tipe']) ?>)</option>
                    <?php endforeach ?>
                </select>
            </div>

            <?php if (! $isEdit): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Aset *</label>
                    <select name="tipe" id="tipe-select" required onchange="toggleFields()" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        <option value="">Pilih Tipe</option>
                        <option value="kabel" <?= old('tipe') === 'kabel' ? 'selected' : '' ?>>Kabel ADSS</option>
                        <option value="odp" <?= old('tipe') === 'odp' ? 'selected' : '' ?>>ODP</option>
                        <option value="closure" <?= old('tipe') === 'closure' ? 'selected' : '' ?>>Closure</option>
                    </select>
                </div>
            <?php endif ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Aset *</label>
                <input type="text" name="nama" value="<?= esc($oldOr('nama', $aset['nama'] ?? '')) ?>" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="Contoh: Kabel ADSS 24 Core">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Core *</label>
                <input type="number" name="core" value="<?= esc($oldOr('core', $aset['core'] ?? '')) ?>" required min="1" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="12, 24, 48">
            </div>

            <?php if (! $isEdit): ?>
                <div id="kabel-fields" class="hidden space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stok Awal (Roll)</label>
                            <input type="number" name="stok_roll" value="<?= esc($oldOr('stok_roll', 0)) ?>" min="0" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min. Stok (Roll)</label>
                            <input type="number" name="min_stok_roll" value="<?= esc($oldOr('min_stok_roll', 0)) ?>" min="0" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meter per Roll</label>
                        <input type="number" name="meter_per_roll" value="<?= esc($oldOr('meter_per_roll', 4000)) ?>" min="1" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                    </div>
                </div>

                <div id="pcs-fields" class="hidden space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stok Awal (Pcs)</label>
                            <input type="number" name="stok_pcs" value="<?= esc($oldOr('stok_pcs', 0)) ?>" min="0" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min. Stok (Pcs)</label>
                            <input type="number" name="min_stok_pcs" value="<?= esc($oldOr('min_stok_pcs', 0)) ?>" min="0" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
            <?php elseif ($aset['tipe'] === 'kabel'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min. Stok (Roll)</label>
                    <input type="number" name="min_stok_roll" value="<?= esc($oldOr('min_stok_roll', $aset['min_stok_roll'] ?? 0)) ?>" min="0" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                </div>
            <?php else: ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min. Stok (Pcs)</label>
                    <input type="number" name="min_stok_pcs" value="<?= esc($oldOr('min_stok_pcs', $aset['min_stok_pcs'] ?? 0)) ?>" min="0" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                </div>
            <?php endif ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto Aset</label>
                <?php if ($isEdit && ! empty($aset['fotos'])): ?>
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 mb-2">Foto saat ini (centang untuk menghapus):</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php foreach ($aset['fotos'] as $foto): ?>
                                <label class="relative block cursor-pointer">
                                    <img src="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" alt="Foto aset" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    <?php if ($foto['is_primary']): ?><span class="absolute top-1 left-1 px-1.5 py-0.5 text-xs bg-blue-500 text-white rounded">Utama</span><?php endif ?>
                                    <input type="checkbox" name="delete_fotos[]" value="<?= esc($foto['id']) ?>" class="absolute bottom-1 right-1 w-4 h-4 text-red-600 bg-white border-gray-300 rounded">
                                </label>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endif ?>
                <input type="file" name="fotos[]" multiple accept="image/*" class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-2">Maksimal 5 foto, format gambar.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="Keterangan tambahan..."><?= esc($oldOr('deskripsi', $aset['deskripsi'] ?? '')) ?></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Aset' ?></button>
                <a href="<?= site_url('admin/aset-material') ?>" class="px-6 py-3 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFields() {
    const tipe = document.getElementById('tipe-select')?.value;
    const kabelFields = document.getElementById('kabel-fields');
    const pcsFields = document.getElementById('pcs-fields');
    if (!kabelFields || !pcsFields) return;
    kabelFields.classList.toggle('hidden', tipe !== 'kabel');
    pcsFields.classList.toggle('hidden', tipe !== 'odp' && tipe !== 'closure');
}
document.addEventListener('DOMContentLoaded', toggleFields);
</script>

<?= $this->endSection() ?>
