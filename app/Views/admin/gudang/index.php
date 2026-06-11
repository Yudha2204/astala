<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $canEdit = ($user['role'] ?? '') === 'admin' || (in_array($user['role'] ?? '', ['manager', 'karyawan'], true) && ($user['sub_user'] ?? '') === 'editor'); ?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Gudang</h1>
            <p class="text-gray-500 dark:text-gray-400">Total <?= count($gudangs) ?> gudang terdaftar</p>
        </div>
        <?php if ($canEdit): ?>
            <a href="<?= site_url('admin/gudang/add') ?>" class="px-4 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center gap-2 w-fit">Tambah Gudang</a>
        <?php endif ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($gudangs as $gudang): ?>
            <article class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?= esc($gudang['nama']) ?></h3>
                        <span class="px-2 py-1 text-xs font-medium rounded <?= $gudang['tipe'] === 'indoor' ? 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400' : 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400' ?>">
                            <?= $gudang['tipe'] === 'indoor' ? 'Indoor' : 'Outdoor' ?>
                        </span>
                    </div>
                    <?php if ($canEdit): ?>
                        <div class="flex gap-2">
                            <a href="<?= site_url('admin/gudang/edit/' . $gudang['id']) ?>" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 rounded">Edit</a>
                            <?php if ((int) $gudang['aset_count'] === 0): ?>
                                <form action="<?= site_url('admin/gudang/delete/' . $gudang['id']) ?>" method="POST" onsubmit="return confirm('Hapus gudang ini?')">
                                    <button type="submit" class="px-3 py-1.5 text-xs bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 rounded">Hapus</button>
                                </form>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                </div>
                <?php if ($gudang['lokasi']): ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3"><?= esc($gudang['lokasi']) ?></p>
                <?php endif ?>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <p class="text-xs text-gray-600 dark:text-gray-300"><span class="font-medium"><?= esc($gudang['aset_count']) ?></span> jenis aset</p>
                </div>
            </article>
        <?php endforeach ?>

        <?php if (! $gudangs): ?>
            <div class="col-span-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center shadow-sm">
                <p class="text-gray-500 dark:text-gray-400">Belum ada gudang. Silakan tambah gudang baru.</p>
            </div>
        <?php endif ?>
    </div>
</div>

<?= $this->endSection() ?>
