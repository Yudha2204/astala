<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$canEdit = ($user['role'] ?? '') === 'admin' || (in_array($user['role'] ?? '', ['manager', 'karyawan'], true) && ($user['sub_user'] ?? '') === 'editor');
$badgeClass = match ($aset['tipe']) {
    'kabel' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    'odp' => 'bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400',
    default => 'bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
};
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= site_url('admin/aset-material') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <?= esc($aset['nama']) ?>
                    <span class="px-2 py-1 text-xs font-medium rounded <?= $badgeClass ?>"><?= esc(strtoupper($aset['tipe'])) ?></span>
                </h1>
                <p class="text-gray-500 dark:text-gray-400"><?= esc($aset['gudang_nama'] ?: 'Unknown Gudang') ?> - <?= esc($aset['core']) ?> Core</p>
            </div>
        </div>
        <?php if ($canEdit): ?>
            <div class="flex gap-2">
                <a href="<?= site_url('admin/aset-material/add-stock/' . $aset['id']) ?>" class="px-4 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600">Tambah Stok</a>
                <a href="<?= site_url('admin/aset-material/edit/' . $aset['id']) ?>" class="px-4 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Edit</a>
            </div>
        <?php endif ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Foto Aset</h2>
                </div>
                <div class="p-4">
                    <?php if ($aset['fotos']): ?>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach ($aset['fotos'] as $foto): ?>
                                <a href="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" target="_blank" class="relative group">
                                    <img src="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" alt="Foto aset" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                    <?php if ($foto['is_primary']): ?><span class="absolute top-1 left-1 px-1.5 py-0.5 text-xs bg-blue-500 text-white rounded">Utama</span><?php endif ?>
                                </a>
                            <?php endforeach ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-400"><p class="text-sm">Belum ada foto</p></div>
                    <?php endif ?>
                </div>
            </section>

            <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Stok Saat Ini</h2>
                </div>
                <div class="p-6">
                    <?php if ($aset['tipe'] === 'kabel'): ?>
                        <div class="text-center">
                            <div class="text-4xl font-bold <?= $isLowStock ? 'text-red-500' : 'text-gray-900 dark:text-white' ?>"><?= esc($aset['stok_roll']) ?></div>
                            <div class="text-gray-500 dark:text-gray-400">Roll</div>
                            <div class="text-sm text-gray-400 mt-1">(<?= number_format((int) $aset['stok_meter']) ?> meter)</div>
                            <?php if ((int) $aset['min_stok_roll'] > 0): ?><div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700"><span class="text-xs text-gray-500">Min. Stok: <?= esc($aset['min_stok_roll']) ?> roll</span></div><?php endif ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <div class="text-4xl font-bold <?= $isLowStock ? 'text-red-500' : 'text-gray-900 dark:text-white' ?>"><?= esc($aset['stok_pcs']) ?></div>
                            <div class="text-gray-500 dark:text-gray-400">Pcs</div>
                            <?php if ((int) $aset['min_stok_pcs'] > 0): ?><div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700"><span class="text-xs text-gray-500">Min. Stok: <?= esc($aset['min_stok_pcs']) ?> pcs</span></div><?php endif ?>
                        </div>
                    <?php endif ?>

                    <?php if ($isLowStock): ?>
                        <div class="mt-4 p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg">
                            <span class="text-sm font-medium text-red-600 dark:text-red-400">Stok Menipis!</span>
                            <p class="text-xs text-red-500 dark:text-red-300 mt-1">Segera lakukan penambahan stok</p>
                        </div>
                    <?php endif ?>
                </div>
            </section>

            <?php if ($aset['deskripsi']): ?>
                <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Deskripsi</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm"><?= esc($aset['deskripsi']) ?></p>
                </section>
            <?php endif ?>
        </div>

        <section class="lg:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">Riwayat Stok</h2>
            </div>
            <div class="p-6">
                <?php if ($stokHistories): ?>
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                        <div class="space-y-6">
                            <?php foreach ($stokHistories as $history): ?>
                                <?php $isMasuk = $history['tipe_aktivitas'] === 'masuk'; ?>
                                <article class="relative pl-10">
                                    <div class="absolute left-0 w-8 h-8 rounded-full flex items-center justify-center <?= $isMasuk ? 'bg-green-100 dark:bg-green-500/20' : 'bg-red-100 dark:bg-red-500/20' ?>">
                                        <span class="<?= $isMasuk ? 'text-green-600' : 'text-red-600' ?>"><?= $isMasuk ? '+' : '-' ?></span>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded <?= $isMasuk ? 'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400' ?>"><?= $isMasuk ? 'MASUK' : 'KELUAR' ?></span>
                                            <span class="text-xs text-gray-500"><?= esc($fmt($history['created_at'])) ?></span>
                                        </div>
                                        <div class="mb-2">
                                            <?php if ($aset['tipe'] === 'kabel'): ?>
                                                <span class="text-lg font-semibold <?= $isMasuk ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>"><?= $isMasuk ? '+' : '-' ?><?= number_format((int) $history['jumlah_meter']) ?> meter</span>
                                                <span class="text-sm text-gray-500 ml-2">(<?= esc($history['jumlah_roll']) ?> roll)</span>
                                            <?php else: ?>
                                                <span class="text-lg font-semibold <?= $isMasuk ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>"><?= $isMasuk ? '+' : '-' ?><?= esc($history['jumlah_pcs']) ?> pcs</span>
                                            <?php endif ?>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300"><?= esc($history['keterangan'] ?: '-') ?></p>
                                        <?php if ($history['user_nama']): ?><p class="text-xs text-gray-400 mt-2">Oleh: <?= esc($history['user_nama']) ?></p><?php endif ?>
                                        <?php if ($history['foto_path']): ?>
                                            <div class="mt-3">
                                                <a href="<?= base_url(ltrim($history['foto_path'], '/')) ?>" target="_blank">
                                                    <img src="<?= base_url(ltrim($history['foto_path'], '/')) ?>" alt="Foto dokumentasi" class="w-full max-w-xs h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700 hover:opacity-80">
                                                </a>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </article>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-400">
                        <p>Belum ada riwayat stok</p>
                        <p class="text-sm mt-1">Riwayat akan muncul saat ada penambahan atau pengambilan stok</p>
                    </div>
                <?php endif ?>
            </div>
        </section>
    </div>
</div>

<?= $this->endSection() ?>
