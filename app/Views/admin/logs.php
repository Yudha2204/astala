<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$query = $query ?? [];
$pagination = $pagination ?? ['page' => 1, 'totalPages' => 1, 'total' => 0, 'hasPrev' => false, 'hasNext' => false];
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
$translateAction = static function (string $action): string {
    $dictionary = [
        'LOGIN' => 'Login',
        'LOGOUT' => 'Logout',
        'CREATE' => 'Buat Baru',
        'UPDATE' => 'Perbarui',
        'DELETE' => 'Hapus',
        'CREATE_USER' => 'Buat User',
        'UPDATE_ROLE' => 'Ubah Role',
        'RESET_PASSWORD' => 'Reset Password',
        'UPDATE_PROFILE' => 'Perbarui Profil',
        'CHANGE_PASSWORD' => 'Ganti Password',
    ];

    return $dictionary[$action] ?? ucwords(strtolower(str_replace('_', ' ', $action)));
};
$actionClass = static fn ($action): string => [
    'CREATE' => 'bg-green-100 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/50',
    'CREATE_USER' => 'bg-green-100 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/50',
    'UPDATE' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/50',
    'UPDATE_PROFILE' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/50',
    'CHANGE_PASSWORD' => 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/50',
    'DELETE' => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/50',
    'LOGIN' => 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/50',
    'LOGOUT' => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-500/10 dark:text-gray-400 dark:border-gray-500/50',
][$action] ?? 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-500/10 dark:text-gray-400';
$pageUrl = static function (int $page) use ($query): string {
    $params = array_filter([
        'search' => $query['search'] ?? '',
        'action' => $query['action'] ?? '',
        'page' => $page,
    ], static fn ($value) => $value !== '' && $value !== null);

    return site_url('admin/logs') . '?' . http_build_query($params);
};
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Log Aktivitas</h1>
            <p class="text-gray-500 dark:text-gray-400">Total <?= esc($pagination['total']) ?> aktivitas tercatat</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
        <form action="<?= site_url('admin/logs') ?>" method="GET" class="flex flex-col md:flex-row items-center gap-3">
            <input type="text" id="searchInput" name="search" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari user, email, deskripsi..." class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all text-xs">
            <button type="submit" id="searchBtn" class="w-full md:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-all text-xs shadow-sm shadow-blue-500/20 active:scale-95">Cari</button>
            <select name="action" onchange="this.form.submit()" class="w-full md:w-auto px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer min-w-[150px] outline-none text-xs">
                <option value="">Semua Aksi</option>
                <?php foreach ($actions as $action): ?>
                    <option value="<?= esc($action) ?>" <?= ($query['action'] ?? '') === $action ? 'selected' : '' ?>><?= esc($translateAction($action)) ?></option>
                <?php endforeach ?>
            </select>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto max-h-[600px] relative">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-900/80 sticky top-0 z-20">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Waktu</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Deskripsi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if ($logs): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 text-xs whitespace-nowrap"><?= esc($fmt($log['created_at'])) ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($log['user_nama']): ?>
                                        <p class="text-gray-900 dark:text-white text-xs font-medium"><?= esc($log['user_nama']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500"><?= esc($log['user_email']) ?></p>
                                    <?php else: ?>
                                        <span class="text-gray-500">-</span>
                                    <?php endif ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center">
                                        <span class="px-2 py-1 text-[10px] font-medium border rounded-md transition-colors <?= $actionClass($log['action']) ?>"><?= esc($translateAction($log['action'])) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 text-xs max-w-xs truncate"><?= esc($log['description'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs font-mono"><?= esc($log['ip_address'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada log aktivitas.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (($pagination['totalPages'] ?? 1) > 1): ?>
        <div class="flex items-center justify-center gap-2">
            <?php if ($pagination['hasPrev']): ?>
                <a href="<?= esc($pageUrl($pagination['page'] - 1)) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">Prev</a>
            <?php endif ?>
            <span class="px-4 py-2 text-gray-500 dark:text-gray-400">Halaman <?= esc($pagination['page']) ?> dari <?= esc($pagination['totalPages']) ?></span>
            <?php if ($pagination['hasNext']): ?>
                <a href="<?= esc($pageUrl($pagination['page'] + 1)) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">Next</a>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const rows = document.querySelectorAll('tbody tr');
    if (!searchInput) return;
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>
