<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="w-full max-w-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-8 space-y-6">
    <div class="text-center space-y-2">
        <div class="w-16 h-16 mx-auto rounded-xl flex items-center justify-center overflow-hidden">
            <img src="<?= base_url('img/logo_astala.png') ?>" alt="ASTALA" class="w-full h-full object-contain">
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Akun ASTALA</h1>
        <p class="text-xs text-gray-500 dark:text-gray-400">Buat akun karyawan baru</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 rounded-xl text-xs">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif ?>

    <form method="post" action="<?= site_url('auth/signup') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap *</label>
            <input type="text" name="nama" value="<?= esc(old('nama')) ?>" required autofocus placeholder="Contoh: Budi Santoso"
                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email *</label>
            <input type="email" name="email" value="<?= esc(old('email')) ?>" required placeholder="nama@lintasarta.co.id"
                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">No Handphone</label>
            <input type="text" name="no_hp" value="<?= esc(old('no_hp')) ?>" placeholder="08xxxxxxxxxx"
                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password *</label>
            <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Konfirmasi Password *</label>
            <input type="password" name="confirm_password" required minlength="6" placeholder="Ulangi password"
                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
        </div>

        <button type="submit"
                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm transition-all shadow-lg shadow-blue-500/25 active:scale-[0.98]">
            Daftar Sekarang
        </button>

        <div class="text-center pt-2">
            <a href="<?= site_url('auth/login') ?>" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                Sudah punya akun? Masuk
            </a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
