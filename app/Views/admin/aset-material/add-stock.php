<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('admin/aset-material/detail/' . $aset['id']) ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Stok</h1>
            <p class="text-gray-500 dark:text-gray-400"><?= esc($aset['nama']) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
            <form action="<?= site_url('admin/aset-material/add-stock/' . $aset['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php if ($aset['tipe'] === 'kabel'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tambah Roll *</label>
                        <input type="number" name="tambah_roll" required min="1" value="1" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-lg">
                        <p class="text-sm text-gray-500 mt-1">1 roll = <?= number_format((int) $aset['meter_per_roll']) ?> meter</p>
                    </div>
                <?php else: ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tambah Pcs *</label>
                        <input type="number" name="tambah_pcs" required min="1" value="1" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-lg">
                    </div>
                <?php endif ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="Catatan penambahan stok..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto Dokumentasi</label>
                    <input type="file" name="foto" accept="image/*" class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-600 hover:file:bg-green-100">
                </div>

                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">Tambah Stok</button>
                    <a href="<?= site_url('admin/aset-material/detail/' . $aset['id']) ?>" class="px-6 py-3 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Batal</a>
                </div>
            </form>
        </section>

        <aside class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Informasi Aset</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400">Gudang</span>
                    <span class="font-medium text-gray-900 dark:text-white"><?= esc($aset['gudang_nama'] ?: '-') ?></span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400">Tipe</span>
                    <span class="font-medium text-gray-900 dark:text-white"><?= esc(strtoupper($aset['tipe'])) ?></span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-gray-500 dark:text-gray-400">Core</span>
                    <span class="font-medium text-gray-900 dark:text-white"><?= esc($aset['core']) ?> Core</span>
                </div>
                <div class="flex justify-between items-center py-3">
                    <span class="text-gray-500 dark:text-gray-400">Stok Saat Ini</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        <?= $aset['tipe'] === 'kabel' ? esc($aset['stok_roll']) . ' Roll (' . number_format((int) $aset['stok_meter']) . ' m)' : esc($aset['stok_pcs']) . ' Pcs' ?>
                    </span>
                </div>
            </div>
        </aside>
    </div>
</div>

<?= $this->endSection() ?>
