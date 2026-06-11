<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$isEdit = (bool) $gudang;
$action = $isEdit ? site_url('admin/gudang/edit/' . $gudang['id']) : site_url('admin/gudang/add');
$value = static fn (string $key, $default = '') => esc(old($key) ?? ($gudang[$key] ?? $default));
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('admin/gudang') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= $isEdit ? 'Edit Gudang' : 'Tambah Gudang' ?></h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
        <form action="<?= $action ?>" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Gudang *</label>
                <input type="text" name="nama" value="<?= $value('nama') ?>" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="Contoh: Gudang Kantor Batam">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lokasi</label>
                <input type="text" name="lokasi" value="<?= $value('lokasi') ?>" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="Contoh: Jl. Gajah Mada No. 123">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Gudang</label>
                <?php $selectedTipe = old('tipe') ?? ($gudang['tipe'] ?? 'indoor'); ?>
                <select name="tipe" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                    <option value="indoor" <?= $selectedTipe === 'indoor' ? 'selected' : '' ?>>Indoor</option>
                    <option value="outdoor" <?= $selectedTipe === 'outdoor' ? 'selected' : '' ?>>Outdoor</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="Keterangan tambahan..."><?= $value('deskripsi') ?></textarea>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Gudang' ?></button>
                <a href="<?= site_url('admin/gudang') ?>" class="px-6 py-3 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
