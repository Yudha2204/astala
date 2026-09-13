<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$isEdit = $mode === 'edit';
$targetId = $editUser['id'] ?? 0;
$currentUserId = (int) (session('user.id') ?? 0);
$isSelf = $isEdit && ((int) $targetId === $currentUserId);
$action = $isEdit ? site_url('admin/users/edit/' . $targetId) : site_url('admin/users/add');

$oldOr = static fn (string $key, $default = '') => old($key) !== null ? old($key) : ($editUser[$key] ?? $default);
$selectedRole = (string) $oldOr('role', 'karyawan');
$selectedSubUser = (string) $oldOr('sub_user', 'viewer');
$isActive = (bool) $oldOr('is_active', true);
$isVerified = (bool) $oldOr('is_verified', true);
?>

<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="<?= site_url('admin/users') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= $isEdit ? 'Edit User' : 'Tambah User Baru' ?></h1>
                <p class="text-sm text-gray-500 dark:text-gray-400"><?= $isEdit ? esc($editUser['nama']) . ' (' . esc($editUser['email']) . ')' : 'Isi formulir di bawah untuk menambahkan pengguna baru' ?></p>
            </div>
        </div>

        <?php if ($isEdit && ! $isSelf): ?>
            <button type="button" onclick="openDeleteModal()" class="px-4 py-2 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30 dark:hover:bg-red-500/20 font-medium rounded-lg text-sm transition-colors">
                Hapus User
            </button>
        <?php endif ?>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
        <form action="<?= $action ?>" method="POST" class="space-y-6">
            <!-- Nama & Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama" value="<?= esc($oldOr('nama')) ?>" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm" placeholder="Contoh: John Doe">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                    <input type="email" name="email" value="<?= esc($oldOr('email')) ?>" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm" placeholder="nama@lintasarta.co.id">
                </div>
            </div>

            <!-- No HP -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nomor Handphone / WhatsApp</label>
                <input type="text" name="no_hp" value="<?= esc($oldOr('no_hp')) ?>" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm" placeholder="Contoh: 08123456789">
            </div>

            <!-- Role & Sub User -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Peran (Role) *</label>
                    <?php if ($isSelf): ?>
                        <input type="text" value="Admin (Anda tidak dapat mengubah role sendiri)" disabled class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-500 dark:text-gray-400 text-sm cursor-not-allowed">
                        <input type="hidden" name="role" value="admin">
                    <?php else: ?>
                        <select name="role" id="role-select" required onchange="handleRoleChange(this.value)" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm cursor-pointer">
                            <option value="karyawan" <?= $selectedRole === 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="pj_gudang" <?= $selectedRole === 'pj_gudang' ? 'selected' : '' ?>>PJ Gudang</option>
                            <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin (Developer)</option>
                        </select>
                    <?php endif ?>
                </div>

                <div id="subuser-wrapper">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hak Akses (Sub User)</label>
                    <select name="sub_user" id="subuser-select" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm cursor-pointer">
                        <option value="viewer" <?= $selectedSubUser === 'viewer' ? 'selected' : '' ?>>Viewer (Hanya Melihat)</option>
                        <option value="editor" <?= $selectedSubUser === 'editor' ? 'selected' : '' ?>>Editor (Dapat Tambah & Edit Data)</option>
                    </select>
                    <p id="subuser-hint" class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Editor memiliki akses menambah & memperbarui barang/gudang.</p>
                </div>
            </div>

            <!-- Password Fields -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Autentikasi & Password</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        <?= $isEdit ? 'Kosongkan kolom password jika tidak ingin mengganti password pengguna.' : 'Tetapkan password awal untuk pengguna baru (minimal 6 karakter).' ?>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?= $isEdit ? 'Password Baru' : 'Password *' ?></label>
                        <input type="password" name="password" id="password" <?= $isEdit ? '' : 'required' ?> minlength="6" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm" placeholder="<?= $isEdit ? 'Kosongkan jika tidak diganti' : 'Minimal 6 karakter' ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?= $isEdit ? 'Konfirmasi Password Baru' : 'Konfirmasi Password *' ?></label>
                        <input type="password" name="confirm_password" id="confirm_password" <?= $isEdit ? '' : 'required' ?> minlength="6" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-sm" placeholder="<?= $isEdit ? 'Ulangi password baru' : 'Ulangi password' ?>">
                    </div>
                </div>
            </div>

            <!-- Status & Verification -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Pengaturan Status Akun</h3>

                <div class="flex flex-col sm:flex-row gap-6">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?> class="mt-1 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white block">Akun Aktif</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Pengguna dapat login dan mengakses fitur sistem sesuai hak akses.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_verified" value="1" <?= $isVerified ? 'checked' : '' ?> class="mt-1 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white block">Terverifikasi</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Tandai alamat email akun ini telah terverifikasi.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="<?= site_url('admin/users') ?>" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition-all shadow-sm shadow-blue-500/25 active:scale-95">
                    <?= $isEdit ? 'Simpan Perubahan' : 'Simpan User' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($isEdit && ! $isSelf): ?>
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 max-w-md w-full shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Hapus Akun Pengguna?</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Apakah Anda yakin ingin menghapus akun <strong><?= esc($editUser['nama']) ?></strong>?<br>Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form method="POST" action="<?= site_url('admin/users/delete/' . $targetId) ?>" class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg text-sm transition-colors">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition-colors shadow-sm shadow-red-500/20">Ya, Hapus</button>
            </form>
        </div>
    </div>
<?php endif ?>

<script>
function handleRoleChange(role) {
    const subUserSelect = document.getElementById('subuser-select');
    const subUserHint = document.getElementById('subuser-hint');

    if (role === 'admin') {
        subUserSelect.value = 'editor';
        subUserSelect.disabled = true;
        subUserHint.textContent = 'Admin (Developer) memiliki hak akses penuh ke semua fitur.';
    } else if (role === 'pj_gudang') {
        subUserSelect.value = 'editor';
        subUserSelect.disabled = true;
        subUserHint.textContent = 'PJ Gudang memiliki akses menambah/mengubah barang dan lokasi gudang.';
    } else {
        subUserSelect.value = 'viewer';
        subUserSelect.disabled = true;
        subUserHint.textContent = 'Karyawan hanya dapat melihat barang & gudang, serta meminjam/mengembalikan barang.';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role-select');
    if (roleSelect) {
        handleRoleChange(roleSelect.value);
    }
});

function openDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.remove('hidden');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.add('hidden');
}
</script>
<?= $this->endSection() ?>
