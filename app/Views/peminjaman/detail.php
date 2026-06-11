<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$fmt = static fn ($date): string => $date ? date('d F Y H:i', strtotime($date)) : '-';
$image = ! empty($peminjaman['barang']['fotos'][0]['foto_path']) ? base_url(ltrim($peminjaman['barang']['fotos'][0]['foto_path'], '/')) : null;
$statusBadge = match ($peminjaman['status_peminjaman']) {
    'selesai' => '<span class="px-3 py-1.5 text-sm font-medium bg-green-50 text-green-600 border border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/50 rounded-lg">Selesai</span>',
    'aktif' => '<span class="px-3 py-1.5 text-sm font-medium bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/50 rounded-lg">Aktif</span>',
    default => '<span class="px-3 py-1.5 text-sm font-medium bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-500/10 dark:text-gray-400 dark:border-gray-500/50 rounded-lg">Dibatalkan</span>',
};
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="javascript:history.back()" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Peminjaman</h1>
            <p class="text-gray-500 dark:text-gray-400">#<?= esc($peminjaman['id']) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm flex flex-col h-full">
            <div class="w-full flex-1 relative min-h-[250px]">
                <?php if ($image): ?>
                    <img src="<?= esc($image) ?>" alt="<?= esc($peminjaman['barang']['nama_barang']) ?>" class="absolute inset-0 w-full h-full object-cover">
                <?php else: ?>
                    <div class="absolute inset-0 bg-gray-100 dark:bg-gray-900 flex items-center justify-center text-gray-400">No image</div>
                <?php endif ?>
            </div>
            <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                <h3 class="font-semibold text-gray-900 dark:text-white text-xl mb-2"><?= esc($peminjaman['barang']['nama_barang']) ?></h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full w-fit">SN: <?= esc($peminjaman['barang']['nomor_seri']) ?></p>
            </div>
        </section>

        <section class="lg:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm h-full">
            <div class="flex flex-wrap gap-2 mb-6">
                <?= $statusBadge ?>
                <?php if ($peminjaman['is_late']): ?>
                    <span class="px-3 py-1.5 text-sm font-medium bg-red-50 text-red-600 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/50 rounded-lg">Terlambat</span>
                <?php endif ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Peminjam</p>
                    <p class="text-gray-900 dark:text-white"><?= esc($peminjaman['user']['nama']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Email</p>
                    <p class="text-gray-900 dark:text-white"><?= esc($peminjaman['user']['email']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Lokasi Peminjaman</p>
                    <p class="text-gray-900 dark:text-white"><?= esc($peminjaman['lokasi_peminjaman']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Keperluan</p>
                    <p class="text-gray-900 dark:text-white"><?= esc($peminjaman['keperluan'] ?: '-') ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Tanggal Pinjam</p>
                    <p class="text-gray-900 dark:text-white"><?= esc($fmt($peminjaman['tanggal_pinjam'])) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase mb-1">Deadline Kembali</p>
                    <p class="text-gray-900 dark:text-white"><?= esc($fmt($peminjaman['tanggal_kembali_rencana'])) ?></p>
                </div>
                <?php if ($peminjaman['tanggal_kembali_aktual']): ?>
                    <div>
                        <p class="text-xs text-gray-500 uppercase mb-1">Tanggal Dikembalikan</p>
                        <p class="<?= $peminjaman['is_late'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>">
                            <?= esc($fmt($peminjaman['tanggal_kembali_aktual'])) ?><?= $peminjaman['is_late'] ? ' (Terlambat)' : '' ?>
                        </p>
                    </div>
                <?php endif ?>
            </div>
        </section>
    </div>

    <?php if ($peminjaman['fotos']): ?>
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Foto Dokumentasi</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($peminjaman['fotos'] as $foto): ?>
                    <div class="relative">
                        <img src="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" alt="Foto peminjaman" class="w-full aspect-square object-cover rounded-lg">
                        <span class="absolute bottom-1 left-1 px-2 py-0.5 text-xs bg-gray-900/80 text-white rounded"><?= $foto['tipe'] === 'pinjam' ? 'Saat Pinjam' : 'Saat Kembali' ?></span>
                    </div>
                <?php endforeach ?>
            </div>
        </section>
    <?php endif ?>

    <?php if ($peminjaman['status_peminjaman'] === 'aktif' && (int) $peminjaman['user_id'] === (int) ($user['id'] ?? 0)): ?>
        <div class="flex justify-end">
            <a href="<?= site_url('peminjaman/return/' . $peminjaman['id']) ?>" class="block w-full sm:w-auto px-8 py-3 text-center bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 shadow-lg shadow-blue-500/25">Kembalikan Barang</a>
        </div>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
