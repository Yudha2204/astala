<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifikasi</h1>
        <p class="text-gray-500 dark:text-gray-400">Daftar notifikasi terbaru untuk akun Anda.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm divide-y divide-gray-200 dark:divide-gray-700">
        <?php if ($notifications): ?>
            <?php foreach ($notifications as $notification): ?>
                <a href="<?= esc($notification['link'] ?: site_url('notifications')) ?>" class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 <?= ! $notification['is_read'] ? 'bg-blue-50 dark:bg-blue-500/10' : '' ?>">
                    <div class="flex items-start gap-3">
                        <span class="mt-2 w-2 h-2 rounded-full <?= match ($notification['tipe']) {
                            'danger' => 'bg-red-500',
                            'warning' => 'bg-yellow-500',
                            'success' => 'bg-green-500',
                            default => 'bg-blue-500',
                        } ?>"></span>
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-white"><?= esc($notification['judul']) ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= esc($notification['pesan']) ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach ?>
        <?php else: ?>
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">Tidak ada notifikasi.</div>
        <?php endif ?>
    </div>
</div>
<?= $this->endSection() ?>
