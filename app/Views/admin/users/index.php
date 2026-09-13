<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$currentUserId = (int) (session('user.id') ?? 0);
$query = $query ?? [];
$pageUrl = static function (int $page) use ($query): string {
    $params = array_filter([
        'search' => $query['search'] ?? '',
        'role'   => $query['role'] ?? '',
        'status' => $query['status'] ?? '',
        'page'   => $page,
    ], static fn ($val) => $val !== '' && $val !== null);

    return site_url('admin/users') . ($params ? '?' . http_build_query($params) : '');
};

$roleBadgeClass = static fn (string $role): string => match ($role) {
    'admin'     => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30',
    'pj_gudang' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
    'karyawan'  => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30',
    default     => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700/50 dark:text-gray-300 dark:border-gray-600',
};
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen User</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola akun pengguna, peran (role), dan hak akses sistem ASTALA</p>
        </div>
        <a href="<?= site_url('admin/users/add') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-all shadow-sm shadow-blue-500/20 active:scale-95 text-sm w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah User
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-500/30 rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/10 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 dark:text-white"><?= esc($stats['total'] ?? 0) ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Pengguna</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-500/30 rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-500/10 rounded-lg flex items-center justify-center text-green-600 dark:text-green-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 dark:text-white"><?= esc($stats['active'] ?? 0) ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pengguna Aktif</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-500/30 rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-500/10 rounded-lg flex items-center justify-center text-red-600 dark:text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 dark:text-white"><?= esc($stats['inactive'] ?? 0) ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Nonaktif</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-500/30 rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-500/10 rounded-lg flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 dark:text-white"><?= esc($stats['admin'] ?? 0) ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
        <form action="<?= site_url('admin/users') ?>" method="GET" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari nama, email, nomor HP..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="role" class="w-full md:w-auto px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer min-w-[130px] outline-none text-xs">
                <option value="">Semua Role</option>
                <option value="admin" <?= ($query['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="pj_gudang" <?= ($query['role'] ?? '') === 'pj_gudang' ? 'selected' : '' ?>>PJ Gudang</option>
                <option value="karyawan" <?= ($query['role'] ?? '') === 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
            </select>

            <select name="status" class="w-full md:w-auto px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer min-w-[130px] outline-none text-xs">
                <option value="">Semua Status</option>
                <option value="active" <?= ($query['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= ($query['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
            </select>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-all text-xs shadow-sm shadow-blue-500/20 active:scale-95">Filter</button>
                <?php if (! empty($query['search']) || ! empty($query['role']) || ! empty($query['status'])): ?>
                    <a href="<?= site_url('admin/users') ?>" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors">Reset</a>
                <?php endif ?>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-900/80 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Handphone</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Peran (Role)</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Terdaftar</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if ($users): ?>
                        <?php foreach ($users as $u): ?>
                            <?php $isSelf = (int) $u['id'] === $currentUserId; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <!-- User Info -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex-shrink-0 overflow-hidden bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm ring-2 ring-white dark:ring-gray-800">
                                            <?php if (! empty($u['foto'])): ?>
                                                <img src="<?= esc(str_starts_with($u['foto'], '/') ? base_url(ltrim($u['foto'], '/')) : base_url('uploads/profiles/' . $u['foto'])) ?>" alt="<?= esc($u['nama']) ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?= esc(strtoupper(substr($u['nama'], 0, 1))) ?>
                                            <?php endif ?>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-xs text-gray-900 dark:text-white"><?= esc($u['nama']) ?></span>
                                                <?php if ($isSelf): ?>
                                                    <span class="px-1.5 py-0.5 text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 rounded font-medium">Anda</span>
                                                <?php endif ?>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($u['email']) ?></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Phone -->
                                <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 font-mono">
                                    <?= esc($u['no_hp'] ?: '-') ?>
                                </td>

                                <!-- Role & Sub User -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="px-2.5 py-1 text-[11px] font-medium border rounded-md capitalize <?= $roleBadgeClass($u['role']) ?>">
                                            <?= esc(str_replace('_', ' ', $u['role'])) ?>
                                        </span>
                                        <?php if ($u['role'] === 'pj_gudang' && ! empty($u['sub_user'])): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-medium rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 capitalize">
                                                <?= esc($u['sub_user']) ?>
                                            </span>
                                        <?php endif ?>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center gap-1">
                                        <?php if ($u['is_active']): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                Aktif
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700/50 dark:text-gray-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Nonaktif
                                            </span>
                                        <?php endif ?>

                                        <?php if ($u['is_verified']): ?>
                                            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Verified
                                            </span>
                                        <?php endif ?>
                                    </div>
                                </td>

                                <!-- Registered Date -->
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    <?= ! empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '-' ?>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Edit -->
                                        <a href="<?= site_url('admin/users/edit/' . $u['id']) ?>" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-500/10 rounded-lg transition-colors" title="Edit User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>

                                        <?php if (! $isSelf): ?>
                                            <!-- Toggle Active -->
                                            <form action="<?= site_url('admin/users/toggle-active/' . $u['id']) ?>" method="POST" class="inline" onsubmit="return confirm('<?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?> akun user ini?')">
                                                <button type="submit" class="p-1.5 <?= $u['is_active'] ? 'text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-500/10' : 'text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-500/10' ?> rounded-lg transition-colors" title="<?= $u['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>">
                                                    <?php if ($u['is_active']): ?>
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    <?php else: ?>
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <?php endif ?>
                                                </button>
                                            </form>

                                            <!-- Delete -->
                                            <button type="button" onclick="openDeleteModal(<?= esc($u['id']) ?>, '<?= esc($u['nama'], 'js') ?>')" class="p-1.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 rounded-lg transition-colors" title="Hapus User">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        <?php endif ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Tidak ada data pengguna yang sesuai dengan filter.
                            </td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan <span class="font-medium"><?= count($users) ?></span> dari <span class="font-medium"><?= esc($pagination['total']) ?></span> pengguna
                </p>
                <div class="flex items-center gap-2">
                    <?php if ($pagination['hasPrev']): ?>
                        <a href="<?= esc($pageUrl($pagination['page'] - 1)) ?>" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors">Sebelumnya</a>
                    <?php endif ?>

                    <span class="text-xs text-gray-500 dark:text-gray-400 px-2">Halaman <?= esc($pagination['page']) ?> / <?= esc($pagination['totalPages']) ?></span>

                    <?php if ($pagination['hasNext']): ?>
                        <a href="<?= esc($pageUrl($pagination['page'] + 1)) ?>" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors">Berikutnya</a>
                    <?php endif ?>
                </div>
            </div>
        <?php endif ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-200 dark:border-gray-700 transform transition-all">
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Hapus Akun Pengguna?</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Apakah Anda yakin ingin menghapus akun <strong id="deleteUserName" class="text-gray-900 dark:text-white"></strong>?<br>Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <form id="deleteUserForm" method="POST" action="" class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg text-sm transition-colors">Batal</button>
            <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition-colors shadow-sm shadow-red-500/20">Ya, Hapus</button>
        </form>
    </div>
</div>

<script>
function openDeleteModal(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteUserForm').action = '<?= site_url('admin/users/delete/') ?>' + userId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
<?= $this->endSection() ?>
