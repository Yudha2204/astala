<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
$img = static fn ($loan): ?string => ! empty($loan['barang']['fotos'][0]['foto_path']) ? base_url(ltrim($loan['barang']['fotos'][0]['foto_path'], '/')) : null;
$statusBadge = static function (array $loan): string {
    return match ($loan['status_peminjaman']) {
        'selesai' => '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400">Selesai</span>',
        'aktif' => '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-400">Aktif</span>',
        default => '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-400">Dibatalkan</span>',
    };
};
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Riwayat Peminjaman</h1>
            <p class="text-gray-500 dark:text-gray-400">Total <?= esc($pagination['total']) ?> peminjaman</p>
        </div>
        <?php if (($user['role'] ?? '') === 'admin' || (($user['role'] ?? '') === 'karyawan' && ($user['sub_user'] ?? '') === 'editor')): ?>
            <a href="<?= site_url('admin/report/pdf/my-loans') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 border border-gray-200 shadow-sm">Export PDF</a>
        <?php endif ?>
    </div>

    <?php if ($loans): ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto max-h-[600px] relative">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-900/80 sticky top-0 z-20 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">No Seri</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pinjam</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Kembali</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase sticky right-0 bg-gray-50 dark:bg-gray-900/80">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($loans as $loan): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                            <?php if ($img($loan)): ?>
                                                <img src="<?= esc($img($loan)) ?>" alt="<?= esc($loan['barang']['nama_barang']) ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">-</div>
                                            <?php endif ?>
                                        </div>
                                        <div>
                                            <div class="text-[11px] font-medium text-gray-900 dark:text-white"><?= esc($loan['barang']['nama_barang']) ?></div>
                                            <div class="text-[10px] text-gray-500 uppercase"><?= esc($loan['barang']['kategori'] ?: '-') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300 font-mono"><?= esc($loan['barang']['nomor_seri']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400"><?= esc($fmt($loan['tanggal_pinjam'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400"><?= esc($fmt($loan['tanggal_kembali_aktual'] ?: $loan['tanggal_kembali_rencana'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <?= $statusBadge($loan) ?>
                                        <?php if ($loan['is_late']): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400">Terlambat</span>
                                        <?php endif ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center sticky right-0 bg-white dark:bg-gray-800">
                                    <a href="<?= site_url('peminjaman/detail/' . $loan['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-600 hover:bg-purple-100 border border-purple-200 rounded-lg text-xs dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="flex items-center justify-center gap-2">
                <?php if ($pagination['hasPrev']): ?>
                    <a href="<?= site_url('peminjaman/history') . '?page=' . ($pagination['page'] - 1) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Prev</a>
                <?php endif ?>
                <span class="px-4 py-2 text-gray-500 dark:text-gray-400">Halaman <?= esc($pagination['page']) ?> dari <?= esc($pagination['totalPages']) ?></span>
                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= site_url('peminjaman/history') . '?page=' . ($pagination['page'] + 1) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Next</a>
                <?php endif ?>
            </div>
        <?php endif ?>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-12 text-center shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum ada riwayat peminjaman</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Anda belum pernah meminjam barang</p>
            <a href="<?= site_url('peminjaman/items') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600">Pinjam Barang</a>
        </div>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
