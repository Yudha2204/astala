<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$query = $query ?? [];
$fmt = static fn ($date): string => $date ? date('d M Y', strtotime($date)) : '-';
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
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengambilan Aset</h1>
            <p class="text-gray-500 dark:text-gray-400">Kelola request pengambilan material</p>
        </div>
        <a href="<?= site_url('pengambilan/request') ?>" class="px-4 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 w-fit">Request Pengambilan</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= esc($stats['total']) ?></p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Pengambilan</p>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?= esc($stats['pending']) ?></p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Menunggu Proses</p>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
            <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?= esc($stats['completed']) ?></p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Selesai</p>
        </div>
    </div>

    <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
        <form action="<?= site_url('pengambilan') ?>" method="GET" class="flex flex-col md:flex-row items-center gap-3">
            <input type="text" name="search" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari pengambilan (ID, Gudang, Petugas)..." class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs">
            <select name="status" onchange="this.form.submit()" class="w-full md:w-auto px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs">
                <option value="">Semua Status</option>
                <?php foreach (['request', 'pickup', 'confirmation', 'done', 'rejected'] as $status): ?>
                    <option value="<?= esc($status) ?>" <?= ($query['status'] ?? '') === $status ? 'selected' : '' ?>><?= esc($statusText($status)) ?></option>
                <?php endforeach ?>
            </select>
            <select name="month" onchange="this.form.submit()" class="w-full md:w-auto px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs">
                <option value="">Semua Bulan</option>
                <?php foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $month => $label): ?>
                    <option value="<?= esc($month) ?>" <?= (string) ($query['month'] ?? '') === (string) $month ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach ?>
            </select>
            <input type="number" name="year" min="2020" max="2100" value="<?= esc($query['year'] ?? '') ?>" placeholder="Tahun" class="w-full md:w-28 px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white text-xs">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs font-medium">Filter</button>
        </form>
    </section>

    <div class="space-y-4">
        <?php foreach ($pengambilans as $p): ?>
            <article class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:shadow-md">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-2 py-1 text-xs font-medium rounded <?= $statusClass($p['status']) ?>"><?= esc($statusText($p['status'])) ?></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">#<?= esc($p['id']) ?> - <?= esc($fmt($p['tanggal_request'])) ?></span>
                        </div>
                        <p class="font-medium text-gray-900 dark:text-white mb-1 text-xs"><?= esc($p['gudang']['nama']) ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= count($p['items']) ?> item - Petugas: <?= esc($p['nama_petugas']) ?></p>
                    </div>
                    <div class="flex gap-2">
                        <?php if ($p['status'] === 'pickup'): ?>
                            <a href="<?= site_url('pengambilan/pickup/' . $p['id']) ?>" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-xs font-medium">Pickup</a>
                        <?php endif ?>
                        <a href="<?= site_url('pengambilan/detail/' . $p['id']) ?>" class="px-4 py-2 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 text-xs">Detail</a>
                    </div>
                </div>
            </article>
        <?php endforeach ?>

        <?php if (! $pengambilans): ?>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center shadow-sm">
                <p class="text-gray-500 dark:text-gray-400">Belum ada data pengambilan.</p>
                <a href="<?= site_url('pengambilan/request') ?>" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Buat Request Pertama</a>
            </div>
        <?php endif ?>
    </div>
</div>

<?= $this->endSection() ?>
