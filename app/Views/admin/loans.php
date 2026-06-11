<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
$query = $query ?? [];
$paramsWithoutPage = static function (array $extra = []) use ($query): string {
    $params = array_filter([
        'search' => $query['search'] ?? '',
        'status' => $query['status'] ?? '',
        'is_late' => $query['is_late'] ?? '',
    ], static fn ($value) => $value !== '');
    return http_build_query(array_merge($params, $extra));
};
$exportUrl = static function () use ($query): string {
    $params = array_filter([
        'search' => $query['search'] ?? '',
        'status' => $query['status'] ?? '',
        'is_late' => $query['is_late'] ?? '',
    ], static fn ($value) => $value !== '' && $value !== null);

    return site_url('admin/report/pdf/loans') . ($params ? '?' . http_build_query($params) : '');
};
$statusBadge = static function (array $loan): string {
    return match ($loan['status_peminjaman']) {
        'selesai' => '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400 rounded">Selesai</span>',
        'aktif' => '<span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-400 rounded">Aktif</span>',
        default => '<span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-400 rounded">Dibatalkan</span>',
    };
};
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Semua Peminjaman</h1>
            <p class="text-gray-500 dark:text-gray-400">Total <?= esc($pagination['total']) ?> peminjaman</p>
        </div>
        <a href="<?= esc($exportUrl()) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 border border-gray-200 shadow-sm">Export PDF</a>
    </div>

    <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
        <form action="<?= site_url('admin/loans') ?>" method="GET" class="flex flex-col md:flex-row items-center gap-3">
            <input type="text" name="search" id="searchLoans" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari peminjaman..." class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 text-xs">
            <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs font-medium shadow-sm shadow-blue-500/20">Cari</button>
            <input type="hidden" name="status" id="statusFilter" value="<?= esc($query['status'] ?? '') ?>">
            <input type="hidden" name="is_late" id="lateFilter" value="<?= esc($query['is_late'] ?? '') ?>">
            <select id="unifiedFilter" onchange="handleLoanFilter(this.value)" class="w-full md:w-auto px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs cursor-pointer min-w-[160px] focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Filter</option>
                <option value="status-aktif" <?= ($query['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="status-selesai" <?= ($query['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="status-dibatalkan" <?= ($query['status'] ?? '') === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                <option value="late-true" <?= ($query['is_late'] ?? '') === 'true' ? 'selected' : '' ?>>Terlambat</option>
                <option value="late-false" <?= ($query['is_late'] ?? '') === 'false' ? 'selected' : '' ?>>Tepat Waktu</option>
            </select>
        </form>
    </section>

    <div id="loans-table-container" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto max-h-[600px] relative">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-900/80 sticky top-0 z-20">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Peminjam</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tgl Pinjam</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tgl Kembali</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Keterangan</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase sticky right-0 bg-gray-50 dark:bg-gray-900/80">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($loans as $loan): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900 dark:text-white text-xs"><?= esc($loan['user']['nama']) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($loan['user']['role']) ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-900 dark:text-white text-xs"><?= esc($loan['barang']['nama_barang']) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">SN: <?= esc($loan['barang']['nomor_seri']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 text-xs"><?= esc($fmt($loan['tanggal_pinjam'])) ?></td>
                                <td class="px-6 py-4 text-xs">
                                    <p class="text-gray-700 dark:text-gray-300"><?= esc($fmt($loan['tanggal_kembali_rencana'])) ?></p>
                                    <?php if ($loan['tanggal_kembali_aktual']): ?>
                                        <p class="text-[10px] <?= $loan['is_late'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>">Aktual: <?= esc($fmt($loan['tanggal_kembali_aktual'])) ?></p>
                                    <?php endif ?>
                                </td>
                                <td class="px-6 py-4"><?= $statusBadge($loan) ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($loan['is_late']): ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400 rounded">Terlambat</span>
                                    <?php elseif ($loan['status_peminjaman'] === 'selesai'): ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400 rounded">Tepat Waktu</span>
                                    <?php else: ?>
                                        <span class="text-gray-500">-</span>
                                    <?php endif ?>
                                </td>
                                <td class="px-6 py-4 text-center sticky right-0 bg-white dark:bg-gray-800">
                                    <a href="<?= site_url('peminjaman/detail/' . $loan['id']) ?>" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/50 rounded-lg">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if (! $loans): ?>
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Tidak ada data peminjaman</td></tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="flex items-center justify-center gap-2">
                <?php if ($pagination['hasPrev']): ?>
                    <a href="<?= site_url('admin/loans') . '?' . $paramsWithoutPage(['page' => $pagination['page'] - 1]) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Prev</a>
                <?php endif ?>
                <span class="px-4 py-2 text-gray-500 dark:text-gray-400">Halaman <?= esc($pagination['page']) ?> dari <?= esc($pagination['totalPages']) ?></span>
                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= site_url('admin/loans') . '?' . $paramsWithoutPage(['page' => $pagination['page'] + 1]) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Next</a>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
</div>

<script>
function handleLoanFilter(value) {
    const statusInput = document.getElementById('statusFilter');
    const lateInput = document.getElementById('lateFilter');
    if (!value) {
        statusInput.value = '';
        lateInput.value = '';
    } else if (value.startsWith('status-')) {
        statusInput.value = value.replace('status-', '');
        lateInput.value = '';
    } else if (value.startsWith('late-')) {
        lateInput.value = value.replace('late-', '');
        statusInput.value = '';
    }
    document.getElementById('unifiedFilter').closest('form').submit();
}
</script>

<?= $this->endSection() ?>
