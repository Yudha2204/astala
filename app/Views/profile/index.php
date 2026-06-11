<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$profilePhoto = static function (?string $path): ?string {
    if (! $path) {
        return null;
    }

    return str_starts_with($path, '/')
        ? base_url(ltrim($path, '/'))
        : base_url('uploads/profiles/' . $path);
};
$photoUrl = $profilePhoto($user['foto'] ?? null);
?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profil Saya</h1>
        <p class="text-gray-500 dark:text-gray-400">Kelola informasi akun dan preferensi Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 h-full shadow-sm">
                <form action="<?= site_url('profile/update') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="flex flex-col items-center">
                        <div class="relative group">
                            <?php if ($photoUrl): ?>
                                <img src="<?= esc($photoUrl) ?>" alt="<?= esc($user['nama']) ?>" class="w-32 h-32 rounded-full object-cover border-4 border-gray-100 dark:border-gray-700 group-hover:border-blue-500 transition-colors">
                            <?php else: ?>
                                <div class="w-32 h-32 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center border-4 border-gray-100 dark:border-gray-700 group-hover:border-blue-500 transition-colors">
                                    <span class="text-4xl font-bold text-white"><?= esc(strtoupper(substr($user['nama'] ?? 'U', 0, 1))) ?></span>
                                </div>
                            <?php endif ?>

                            <label for="foto" class="absolute bottom-0 right-0 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 p-2 rounded-full text-gray-600 dark:text-white cursor-pointer hover:bg-blue-500 dark:hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </label>
                            <input type="file" id="foto" name="foto" accept="image/*" class="hidden" onchange="this.form.submit()">
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Klik ikon kamera untuk mengganti foto</p>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama" value="<?= esc($user['nama']) ?>" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" value="<?= esc($user['email']) ?>" disabled class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-500 cursor-not-allowed">
                            <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. HP</label>
                            <input type="tel" name="no_hp" value="<?= esc($user['no_hp'] ?? '') ?>" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                            <input type="text" value="<?= esc(strtoupper(str_replace('_', ' ', $user['role'] ?? '-'))) ?>" disabled class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-500 cursor-not-allowed">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-blue-600 dark:bg-blue-500 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors font-medium">Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Ganti Password</h3>
                <form action="<?= site_url('profile/password') ?>" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Saat Ini</label>
                        <input type="password" name="current_password" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                            <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-orange-600 dark:bg-orange-500 text-white rounded-lg hover:bg-orange-700 dark:hover:bg-orange-600 transition-colors font-medium">Ganti Password</button>
                    </div>
                </form>
            </div>

            <div class="mt-6">
                <button type="button" onclick="openLogoutModal()" class="block w-full py-2.5 bg-red-50 border border-red-200 text-red-600 dark:bg-red-500/10 dark:border-red-500/50 dark:text-red-500 text-center rounded-lg hover:bg-red-500 hover:text-white transition-all font-medium">Logout</button>
            </div>
        </div>
    </div>
</div>

<div id="logout-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/80 backdrop-blur-sm transition-opacity opacity-0">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-sm w-full mx-4 border border-gray-200 dark:border-gray-700 shadow-2xl transform scale-95 transition-transform duration-200" id="logout-modal-content">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600 dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Konfirmasi Logout</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Apakah Anda yakin ingin keluar dari aplikasi? Sesi Anda akan diakhiri.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeLogoutModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium">Batal</button>
                <a href="<?= site_url('auth/logout') ?>" class="px-5 py-2.5 bg-red-600 dark:bg-red-500 text-white rounded-lg hover:bg-red-700 dark:hover:bg-red-600 transition-colors font-medium shadow-lg shadow-red-500/30">Ya, Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
function openLogoutModal() {
    const modal = document.getElementById('logout-modal');
    const content = document.getElementById('logout-modal-content');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}
function closeLogoutModal() {
    const modal = document.getElementById('logout-modal');
    const content = document.getElementById('logout-modal-content');
    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 200);
}
document.getElementById('logout-modal').addEventListener('click', function (event) {
    if (event.target === this) {
        closeLogoutModal();
    }
});
</script>

<?= $this->endSection() ?>
