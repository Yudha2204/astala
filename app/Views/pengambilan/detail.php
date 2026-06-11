<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$backUrl = $backUrl ?? '/pengambilan';
$adminMode = (bool) ($adminMode ?? false);
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
$statusText = static fn ($status): string => [
    'request' => 'Request',
    'waiting' => 'Menunggu Approval',
    'pickup' => 'Siap Pickup',
    'confirmation' => 'Menunggu Konfirmasi',
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
$canEdit = ($user['role'] ?? '') === 'admin' || (in_array($user['role'] ?? '', ['manager', 'karyawan'], true) && ($user['sub_user'] ?? '') === 'editor');
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url(ltrim($backUrl, '/')) ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Pengambilan #<?= esc($pengambilan['id']) ?></h1>
            <span class="px-2 py-1 text-xs font-medium rounded <?= $statusClass($pengambilan['status']) ?>"><?= esc($statusText($pengambilan['status'])) ?></span>
        </div>
        <?php if ($pengambilan['status'] === 'pickup' && ! $adminMode): ?>
            <a href="<?= site_url('pengambilan/pickup/' . $pengambilan['id']) ?>" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium">Lanjutkan Pickup</a>
        <?php endif ?>
        <?php if ($adminMode && $canEdit && $pengambilan['status'] === 'request'): ?>
            <form action="<?= site_url('pengambilan/admin/approve/' . $pengambilan['id']) ?>" method="POST">
                <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">Approve</button>
            </form>
            <button onclick="showRejectModal()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Reject</button>
        <?php elseif ($adminMode && $canEdit && $pengambilan['status'] === 'confirmation'): ?>
            <a href="<?= site_url('pengambilan/admin/confirm/' . $pengambilan['id']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Konfirmasi</a>
        <?php endif ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daftar Item</h2>
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">Aset</th>
                            <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">Core</th>
                            <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($pengambilan['items'] as $item): ?>
                            <tr>
                                <td class="py-3 text-xs font-medium text-gray-900 dark:text-white">
                                    <?= esc($item['aset']['nama']) ?>
                                    <span class="ml-2 px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600"><?= esc(strtoupper($item['aset']['tipe'])) ?></span>
                                </td>
                                <td class="py-3 text-xs text-gray-600 dark:text-gray-300"><?= esc($item['aset']['core']) ?> Core</td>
                                <td class="py-3 text-xs font-medium text-gray-900 dark:text-white">
                                    <?= $item['aset']['tipe'] === 'kabel' ? number_format((int) $item['jumlah_meter']) . ' meter' : esc($item['jumlah_pcs']) . ' pcs' ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </section>

            <?php if ($pengambilan['status'] === 'rejected' && $pengambilan['alasan_penolakan']): ?>
                <section class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl p-4">
                    <h3 class="font-semibold text-red-700 dark:text-red-400 mb-2">Alasan Penolakan</h3>
                    <p class="text-red-600 dark:text-red-300"><?= esc($pengambilan['alasan_penolakan']) ?></p>
                </section>
            <?php endif ?>

            <?php if ($pengambilan['fotos']): ?>
                <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Foto Bukti Pengambilan</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($pengambilan['fotos'] as $foto): ?>
                            <a href="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" target="_blank" class="aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900">
                                <img src="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" class="w-full h-full object-cover" alt="Bukti">
                            </a>
                        <?php endforeach ?>
                    </div>
                </section>
            <?php endif ?>

            <?php if ($pengambilan['ttd_petugas'] || $pengambilan['ttd_admin']): ?>
                <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tanda Tangan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if ($pengambilan['ttd_petugas']): ?>
                            <div><p class="text-sm text-gray-500 mb-2">Petugas: <strong><?= esc($pengambilan['nama_petugas']) ?></strong></p><div class="border rounded-lg p-2 bg-white"><img src="<?= esc($pengambilan['ttd_petugas']) ?>" class="max-h-60 mx-auto" alt="TTD Petugas"></div></div>
                        <?php endif ?>
                        <?php if ($pengambilan['ttd_admin']): ?>
                            <div><p class="text-sm text-gray-500 mb-2">Admin: <strong><?= esc($pengambilan['approver']['nama']) ?></strong></p><div class="border rounded-lg p-2 bg-white"><img src="<?= esc($pengambilan['ttd_admin']) ?>" class="max-h-60 mx-auto" alt="TTD Admin"></div></div>
                        <?php endif ?>
                    </div>
                </section>
            <?php endif ?>
        </div>

        <aside class="space-y-6">
            <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline</h2>
                <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-2 space-y-6 pl-6">
                    <div class="relative"><span class="absolute -left-[29px] w-3 h-3 rounded-full bg-blue-500"></span><p class="text-sm font-medium text-gray-900 dark:text-white">Request</p><p class="text-xs text-gray-500"><?= esc($fmt($pengambilan['tanggal_request'])) ?></p></div>
                    <?php if (in_array($pengambilan['status'], ['pickup', 'confirmation', 'done'], true)): ?>
                        <div class="relative"><span class="absolute -left-[29px] w-3 h-3 rounded-full bg-green-500"></span><p class="text-sm font-medium text-gray-900 dark:text-white">Approved</p><p class="text-xs text-gray-500"><?= esc($fmt($pengambilan['tanggal_approval'])) ?></p></div>
                    <?php elseif ($pengambilan['status'] === 'rejected'): ?>
                        <div class="relative"><span class="absolute -left-[29px] w-3 h-3 rounded-full bg-red-500"></span><p class="text-sm font-medium text-gray-900 dark:text-white">Rejected</p><p class="text-xs text-gray-500"><?= esc($fmt($pengambilan['tanggal_approval'])) ?></p></div>
                    <?php endif ?>
                    <?php if (in_array($pengambilan['status'], ['confirmation', 'done'], true)): ?><div class="relative"><span class="absolute -left-[29px] w-3 h-3 rounded-full bg-orange-500"></span><p class="text-sm font-medium text-gray-900 dark:text-white">Pickup</p><p class="text-xs text-gray-500"><?= esc($fmt($pengambilan['tanggal_pickup'])) ?></p></div><?php endif ?>
                    <?php if ($pengambilan['status'] === 'done'): ?><div class="relative"><span class="absolute -left-[29px] w-3 h-3 rounded-full bg-green-500"></span><p class="text-sm font-medium text-gray-900 dark:text-white">Selesai</p><p class="text-xs text-gray-500"><?= esc($fmt($pengambilan['tanggal_done'])) ?></p></div><?php endif ?>
                </div>
            </section>

            <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi</h2>
                <div class="space-y-3 text-sm">
                    <div><p class="text-gray-500">Gudang</p><p class="text-gray-900 dark:text-white font-medium"><?= esc($pengambilan['gudang']['nama']) ?></p></div>
                    <div><p class="text-gray-500">Nama Mitra</p><p class="text-gray-900 dark:text-white font-medium"><?= esc($pengambilan['nama_mitra']) ?></p></div>
                    <div><p class="text-gray-500">Nama Petugas</p><p class="text-gray-900 dark:text-white font-medium"><?= esc($pengambilan['nama_petugas']) ?></p></div>
                    <?php if ($pengambilan['deskripsi_keperluan']): ?><div><p class="text-gray-500">Keperluan</p><p class="text-gray-900 dark:text-white break-words"><?= esc($pengambilan['deskripsi_keperluan']) ?></p></div><?php endif ?>
                </div>
            </section>

            <?php if ($pengambilan['status'] === 'done'): ?>
                <a href="<?= site_url('pengambilan/download/' . $pengambilan['id']) ?>" class="w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium">Download Surat</a>
            <?php endif ?>
        </aside>
    </div>
</div>

<?php if ($adminMode): ?>
<div id="reject-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tolak Request</h3>
        <form action="<?= site_url('pengambilan/admin/reject/' . $pengambilan['id']) ?>" method="POST">
            <textarea name="alasan" required rows="3" placeholder="Alasan penolakan..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white mb-4"></textarea>
            <div class="flex gap-3"><button type="submit" class="flex-1 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Tolak</button><button type="button" onclick="closeRejectModal()" class="px-6 py-2.5 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-lg">Batal</button></div>
        </form>
    </div>
</div>
<script>
function showRejectModal(){document.getElementById('reject-modal').classList.remove('hidden')}
function closeRejectModal(){document.getElementById('reject-modal').classList.add('hidden')}
</script>
<?php endif ?>

<?= $this->endSection() ?>
