<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
$img = static fn ($loan): ?string => ! empty($loan['barang']['fotos'][0]['foto_path']) ? base_url(ltrim($loan['barang']['fotos'][0]['foto_path'], '/')) : null;
$query = $query ?? [];
$barangs = $barangs ?? [];
$hasFilters = ($query['search'] ?? '') !== '' || ($query['status'] ?? '') !== '' || ($query['barang_id'] ?? '') !== '';
$selectedBarangObj = null;
if (!empty($query['barang_id'])) {
    foreach ($barangs as $b) {
        if ((string)$b['id'] === (string)$query['barang_id']) {
            $selectedBarangObj = $b;
            break;
        }
    }
}
$paramsWithoutPage = static function (array $extra = []) use ($query): string {
    $params = array_filter([
        'search' => $query['search'] ?? '',
        'status' => $query['status'] ?? '',
        'barang_id' => $query['barang_id'] ?? '',
    ], static fn ($value) => $value !== '' && $value !== null);
    return http_build_query(array_merge($params, $extra));
};
$exportUrl = static function () use ($query): string {
    $params = array_filter([
        'search' => $query['search'] ?? '',
        'status' => $query['status'] ?? '',
        'barang_id' => $query['barang_id'] ?? '',
    ], static fn ($value) => $value !== '' && $value !== null);

    return site_url('peminjaman/export/pdf') . ($params ? '?' . http_build_query($params) : '');
};
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
        <a href="<?= esc($exportUrl()) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 border border-gray-200 shadow-sm">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            Export PDF
        </a>
    </div>

    <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm space-y-3">
        <form action="<?= site_url('peminjaman/history') ?>" method="GET" class="flex flex-col md:flex-row items-center gap-3">
            <div class="w-full md:flex-1 flex gap-2">
                <input type="text" name="search" id="searchHistory" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari riwayat peminjaman..." class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 text-xs">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs font-medium shadow-sm shadow-blue-500/20 whitespace-nowrap">Cari</button>
            </div>
            <input type="hidden" name="status" id="statusFilter" value="<?= esc($query['status'] ?? '') ?>">
            <div class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-3">
                <select name="barang_id" id="barangFilter" onchange="this.form.submit()" class="w-full sm:w-auto px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs cursor-pointer min-w-[180px] focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Barang</option>
                    <?php foreach ($barangs as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ((string)($query['barang_id'] ?? '')) === (string)$b['id'] ? 'selected' : '' ?>>
                            <?= esc($b['nama_barang']) ?><?= !empty($b['nomor_seri']) ? ' (' . esc($b['nomor_seri']) . ')' : '' ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <select id="unifiedFilter" onchange="handleHistoryFilter(this.value)" class="w-full sm:w-auto px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs cursor-pointer min-w-[150px] focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="status-aktif" <?= ($query['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="status-selesai" <?= ($query['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="status-dibatalkan" <?= ($query['status'] ?? '') === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
                <?php if ($hasFilters): ?>
                    <a href="<?= site_url('peminjaman/history') ?>" class="w-full sm:w-auto px-3 py-2.5 text-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors whitespace-nowrap">Reset</a>
                <?php endif ?>
            </div>
        </form>

        <?php if ($selectedBarangObj): ?>
            <div class="flex items-center gap-2 text-xs text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-500/10 px-3 py-1.5 rounded-lg border border-blue-200 dark:border-blue-500/30 w-fit">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Filter barang: <strong class="font-semibold"><?= esc($selectedBarangObj['nama_barang']) ?><?= !empty($selectedBarangObj['nomor_seri']) ? ' (' . esc($selectedBarangObj['nomor_seri']) . ')' : '' ?></strong></span>
                <a href="<?= site_url('peminjaman/history') . '?' . $paramsWithoutPage(['barang_id' => '']) ?>" class="ml-1 text-gray-400 hover:text-red-500 font-bold" title="Hapus filter barang">&times;</a>
            </div>
        <?php endif ?>
    </section>

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
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400"><?= esc($fmt($loan['tanggal_kembali_aktual'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?= $statusBadge($loan) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center sticky right-0 bg-white dark:bg-gray-800">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <?php if ($loan['status_peminjaman'] === 'aktif'): ?>
                                            <a href="<?= site_url('peminjaman/return/' . $loan['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-xs dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">Kembalikan</a>
                                        <?php endif ?>
                                        <a href="<?= site_url('peminjaman/detail/' . $loan['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-600 hover:bg-purple-100 border border-purple-200 rounded-lg text-xs dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30">Detail</a>
                                    </div>
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
                    <a href="<?= site_url('peminjaman/history') . '?' . $paramsWithoutPage(['page' => $pagination['page'] - 1]) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Prev</a>
                <?php endif ?>
                <span class="px-4 py-2 text-gray-500 dark:text-gray-400">Halaman <?= esc($pagination['page']) ?> dari <?= esc($pagination['totalPages']) ?></span>
                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= site_url('peminjaman/history') . '?' . $paramsWithoutPage(['page' => $pagination['page'] + 1]) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Next</a>
                <?php endif ?>
            </div>
        <?php endif ?>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-12 text-center shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"><?= $hasFilters ? 'Tidak ada riwayat peminjaman yang cocok' : 'Belum ada riwayat peminjaman' ?></h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4"><?= $hasFilters ? 'Coba sesuaikan filter pencarian Anda' : 'Anda belum pernah meminjam barang' ?></p>
            <?php if ($hasFilters): ?>
                <a href="<?= site_url('peminjaman/history') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">Reset Filter</a>
            <?php else: ?>
                <a href="<?= site_url('peminjaman/items') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600">Pinjam Barang</a>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>

<script>
function handleHistoryFilter(value) {
    const statusInput = document.getElementById('statusFilter');
    statusInput.value = value ? value.replace('status-', '') : '';
    document.getElementById('unifiedFilter').closest('form').submit();
}
</script>

<?= $this->endSection() ?>
