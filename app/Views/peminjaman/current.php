<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
$img = static fn ($loan): ?string => ! empty($loan['barang']['fotos'][0]['foto_path']) ? base_url(ltrim($loan['barang']['fotos'][0]['foto_path'], '/')) : null;
?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Barang yang Sedang Dipinjam</h1>
        <p class="text-gray-500 dark:text-gray-400"><?= count($loans) ?> barang aktif</p>
    </div>

    <?php if ($loans): ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto max-h-[600px] relative">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-900/80 sticky top-0 z-20 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">No Seri</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal Pinjam</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal Kembali</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lokasi</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase sticky right-0 bg-gray-50 dark:bg-gray-900/80">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($loans as $loan): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                                            <?php if ($img($loan)): ?>
                                                <img src="<?= esc($img($loan)) ?>" alt="<?= esc($loan['barang']['nama_barang']) ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">-</div>
                                            <?php endif ?>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-gray-900 dark:text-white"><?= esc($loan['barang']['nama_barang']) ?></div>
                                            <?php if ($loan['isOverdue']): ?>
                                                <div class="text-xs text-red-500 font-medium mt-0.5">Terlambat</div>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono"><?= esc($loan['barang']['nomor_seri']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400"><?= esc($fmt($loan['tanggal_pinjam'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs <?= $loan['isOverdue'] ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400' ?>"><?= esc($fmt($loan['tanggal_kembali_rencana'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400"><?= esc($loan['lokasi_peminjaman']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center sticky right-0 bg-white dark:bg-gray-800">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= site_url('peminjaman/return/' . $loan['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 rounded-lg text-xs dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30">Kembalikan</a>
                                        <a href="<?= site_url('peminjaman/detail/' . $loan['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-600 hover:bg-purple-100 border border-purple-200 rounded-lg text-xs dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-12 text-center shadow-sm">
            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak ada barang yang sedang dipinjam</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Anda belum meminjam barang apapun</p>
            <a href="<?= site_url('peminjaman/items') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600">Pinjam Barang</a>
        </div>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
