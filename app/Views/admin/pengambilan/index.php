<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$query = $query ?? [];
$pagination = $pagination ?? ['page' => 1, 'totalPages' => 1, 'hasPrev' => false, 'hasNext' => false, 'total' => 0];
$canEdit = ($user['role'] ?? '') === 'admin' || (in_array($user['role'] ?? '', ['manager', 'karyawan'], true) && ($user['sub_user'] ?? '') === 'editor');
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
$statusText = static fn ($status): string => [
    'request' => 'Request',
    'waiting' => 'Menunggu',
    'pickup' => 'Siap Pickup',
    'confirmation' => 'Konfirmasi',
    'done' => 'Selesai',
    'rejected' => 'Ditolak',
][$status] ?? $status;
$statusClass = static fn ($status): string => [
    'request' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    'waiting' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-400',
    'pickup' => 'bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400',
    'confirmation' => 'bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
    'done' => 'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400',
    'rejected' => 'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400',
][$status] ?? 'bg-gray-100 text-gray-600';
$pageUrl = static function (int $page) use ($query): string {
    $params = array_filter([
        'page' => $page,
        'status' => $query['status'] ?? '',
        'search' => $query['search'] ?? '',
    ], static fn ($value) => $value !== '' && $value !== null);

    return site_url('pengambilan/admin') . '?' . http_build_query($params);
};
$exportUrl = static function () use ($query): string {
    $params = array_filter([
        'status' => $query['status'] ?? '',
        'search' => $query['search'] ?? '',
    ], static fn ($value) => $value !== '' && $value !== null);

    return site_url('admin/report/pdf/pengambilan') . ($params ? '?' . http_build_query($params) : '');
};
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengambilan Aset</h1>
            <p class="text-gray-500 dark:text-gray-400">Kelola request pengambilan material</p>
        </div>
        <a href="<?= esc($exportUrl()) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 border border-gray-200 transition-all shadow-sm h-fit">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            Export PDF
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="<?= site_url('pengambilan/admin?status=request') ?>" class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-xl p-4 transition-transform hover:scale-[1.02]">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?= esc($stats['pending'] ?? 0) ?></p>
            <p class="text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wider">Request Baru</p>
        </a>
        <a href="<?= site_url('pengambilan/admin?status=waiting') ?>" class="bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/30 rounded-xl p-4 transition-transform hover:scale-[1.02]">
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400"><?= esc($stats['waiting'] ?? 0) ?></p>
            <p class="text-xs font-medium text-orange-700 dark:text-orange-300 uppercase tracking-wider">Dalam Proses</p>
        </a>
        <a href="<?= site_url('pengambilan/admin?status=confirmation') ?>" class="bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 rounded-xl p-4 transition-transform hover:scale-[1.02]">
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400"><?= esc($stats['confirmation'] ?? 0) ?></p>
            <p class="text-xs font-medium text-purple-700 dark:text-purple-300 uppercase tracking-wider">Menunggu Konfirmasi</p>
        </a>
        <a href="<?= site_url('pengambilan/admin?status=done') ?>" class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-xl p-4 transition-transform hover:scale-[1.02]">
            <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?= esc($stats['done'] ?? 0) ?></p>
            <p class="text-xs font-medium text-green-700 dark:text-green-300 uppercase tracking-wider">Selesai</p>
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
        <form action="<?= site_url('pengambilan/admin') ?>" method="GET" class="flex flex-col md:flex-row items-center gap-3">
            <input type="text" name="search" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari ID, mitra, gudang, atau petugas..." class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all text-xs">
            <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-all text-xs shadow-sm shadow-blue-500/20 active:scale-95">Cari</button>
            <select name="status" onchange="this.form.submit()" class="w-full md:w-auto px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer min-w-[150px] outline-none text-xs">
                <option value="">Semua Status</option>
                <?php foreach (['request' => 'Request', 'pickup' => 'Siap Pickup', 'confirmation' => 'Konfirmasi', 'done' => 'Selesai', 'rejected' => 'Ditolak'] as $value => $label): ?>
                    <option value="<?= esc($value) ?>" <?= ($query['status'] ?? '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach ?>
            </select>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto max-h-[600px] relative">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-900/80 sticky top-0 z-20">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mitra</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gudang</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider sticky right-0 bg-gray-50 dark:bg-gray-900/80 z-30">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if ($pengambilans): ?>
                        <?php foreach ($pengambilans as $row): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-4 text-gray-900 dark:text-white text-xs">#<?= esc($row['id']) ?></td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-medium text-gray-900 dark:text-white"><?= esc($row['mitra']['nama'] ?? $row['nama_mitra']) ?></p>
                                    <p class="text-xs text-gray-500">Petugas: <?= esc($row['nama_petugas'] ?: '-') ?></p>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300 text-xs"><?= esc($row['gudang']['nama'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300 text-xs"><?= count($row['items'] ?? []) ?> item</td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs"><?= esc($fmt($row['tanggal_request'])) ?></td>
                                <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-medium rounded <?= $statusClass($row['status']) ?>"><?= esc($statusText($row['status'])) ?></span></td>
                                <td class="px-6 py-4 text-center sticky right-0 bg-white dark:bg-gray-800 z-10">
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($canEdit && $row['status'] === 'request'): ?>
                                            <form action="<?= site_url('pengambilan/admin/approve/' . $row['id']) ?>" method="POST" class="inline">
                                                <button type="submit" class="px-3 py-1.5 text-xs bg-green-50 text-green-600 hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-green-500/20 rounded transition-colors">Approve</button>
                                            </form>
                                            <button type="button" onclick="showRejectModal(<?= (int) $row['id'] ?>)" class="px-3 py-1.5 text-xs bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 rounded transition-colors">Reject</button>
                                        <?php elseif ($canEdit && $row['status'] === 'confirmation'): ?>
                                            <a href="<?= site_url('pengambilan/admin/confirm/' . $row['id']) ?>" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20 rounded transition-colors">Konfirmasi</a>
                                        <?php endif ?>
                                        <a href="<?= site_url('pengambilan/admin/detail/' . $row['id']) ?>" class="px-3 py-1.5 text-xs bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-500/10 dark:text-gray-400 dark:hover:bg-gray-500/20 rounded transition-colors">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data pengambilan.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <div class="flex items-center justify-center gap-2">
            <?php if ($pagination['hasPrev']): ?>
                <a href="<?= esc($pageUrl($pagination['page'] - 1)) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-xs text-gray-700 dark:text-gray-200">Prev</a>
            <?php endif ?>
            <span class="px-4 py-2 text-gray-500 text-xs">Halaman <?= esc($pagination['page']) ?> dari <?= esc($pagination['totalPages']) ?></span>
            <?php if ($pagination['hasNext']): ?>
                <a href="<?= esc($pageUrl($pagination['page'] + 1)) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-xs text-gray-700 dark:text-gray-200">Next</a>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>

<div id="reject-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tolak Request</h3>
        <form id="reject-form" method="POST">
            <textarea name="alasan" required rows="3" placeholder="Alasan penolakan..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white mb-4"></textarea>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Tolak</button>
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-lg">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(id) {
    document.getElementById('reject-form').action = '<?= site_url('pengambilan/admin/reject') ?>/' + id;
    document.getElementById('reject-modal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
}
</script>

<?= $this->endSection() ?>
