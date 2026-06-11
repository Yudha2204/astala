<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$canEdit = ($user['role'] ?? '') === 'admin' || (($user['role'] ?? '') === 'karyawan' && ($user['sub_user'] ?? '') === 'editor') || (($user['role'] ?? '') === 'manager' && ($user['sub_user'] ?? '') === 'editor');
$canSeeTimeline = in_array($user['role'] ?? '', ['admin', 'manager'], true) || (($user['role'] ?? '') === 'karyawan' && ($user['sub_user'] ?? '') === 'editor');
function barang_dt(?string $date): string
{
    return $date ? date('d F Y H:i', strtotime($date)) : '-';
}
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('barang') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= esc($barang['nama_barang']) ?></h1>
            <p class="text-gray-500 dark:text-gray-400">SN: <?= esc($barang['nomor_seri']) ?></p>
        </div>
        <?php if ($canEdit): ?>
            <a href="<?= site_url('barang/edit/' . $barang['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500 dark:hover:bg-blue-500/20 rounded-lg transition-colors">Edit</a>
        <?php endif ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                <?php if (! empty($barang['fotos'])): ?>
                    <div class="aspect-video bg-gray-100 dark:bg-gray-900 group relative">
                        <img id="main-photo" src="<?= base_url(ltrim($barang['fotos'][0]['foto_path'], '/')) ?>" alt="<?= esc($barang['nama_barang']) ?>" class="w-full h-full object-contain cursor-zoom-in" onclick="openModal(this.src)">
                    </div>
                    <?php if (count($barang['fotos']) > 1): ?>
                        <div class="p-4 flex gap-2 overflow-x-auto">
                            <?php foreach ($barang['fotos'] as $index => $foto): ?>
                                <button onclick="changePhoto('<?= base_url(ltrim($foto['foto_path'], '/')) ?>')" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 <?= $index === 0 ? 'border-blue-500' : 'border-gray-200 dark:border-gray-700' ?> hover:border-blue-400 transition-colors">
                                    <img src="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" class="w-full h-full object-cover">
                                </button>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                <?php else: ?>
                    <div class="aspect-video bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                <?php endif ?>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 space-y-6 shadow-sm">
                <div class="flex gap-2">
                    <span class="px-3 py-1.5 text-sm font-medium rounded-lg <?= $barang['status_kondisi'] === 'baik' ? 'bg-green-50 text-green-600 border border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/50' : 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/50' ?>">
                        <?= $barang['status_kondisi'] === 'baik' ? 'Kondisi Baik' : 'Rusak' ?>
                    </span>
                    <span class="px-3 py-1.5 text-sm font-medium rounded-lg <?= $barang['status_ketersediaan'] === 'tersedia' ? 'bg-teal-50 text-teal-600 border border-teal-200 dark:bg-teal-500/10 dark:text-teal-400 dark:border-teal-500/50' : 'bg-yellow-50 text-yellow-600 border border-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-400 dark:border-yellow-500/50' ?>">
                        <?= $barang['status_ketersediaan'] === 'tersedia' ? 'Tersedia' : 'Sedang Dipinjam' ?>
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-xs text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-1">Nomor Seri</p><p class="text-gray-900 dark:text-white font-mono"><?= esc($barang['nomor_seri']) ?></p></div>
                    <div><p class="text-xs text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-1">Kategori</p><p class="text-gray-900 dark:text-white"><?= esc($barang['kategori'] ?: '-') ?></p></div>
                    <div><p class="text-xs text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-1">Lokasi Penyimpanan</p><p class="text-gray-900 dark:text-white"><?= esc($barang['lokasi_penyimpanan'] ?: '-') ?></p></div>
                    <div><p class="text-xs text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-1">Tanggal Input</p><p class="text-gray-900 dark:text-white"><?= esc(barang_dt($barang['created_at'] ?? null)) ?></p></div>
                </div>

                <?php if ($barang['deskripsi']): ?>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-2">Deskripsi</p>
                        <p class="text-gray-600 dark:text-gray-300"><?= esc($barang['deskripsi']) ?></p>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <div class="space-y-6">
            <?php if ($canSeeTimeline): ?>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">Timeline Riwayat Barang</h2>
                    <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-3 space-y-8 pl-8 py-2">
                        <?php foreach ($loans as $loan): ?>
                            <?php if ($loan['tanggal_kembali_aktual']): ?>
                                <div class="relative">
                                    <span class="absolute -left-[41px] top-1.5 w-4 h-4 rounded-full bg-green-500 border-4 border-white dark:border-gray-800 ring-2 ring-gray-100 dark:ring-gray-900"></span>
                                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700/50">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                                            <h3 class="text-green-600 dark:text-green-400 font-medium text-sm">Dikembalikan</h3>
                                            <span class="text-xs text-gray-500"><?= esc(barang_dt($loan['tanggal_kembali_aktual'])) ?></span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">Oleh <span class="text-gray-900 dark:text-white font-medium"><?= esc($loan['user_nama'] ?? 'Pengguna Terhapus') ?></span> (<?= esc($loan['user_role'] ?? '-') ?>)</p>
                                    </div>
                                </div>
                            <?php endif ?>
                            <div class="relative">
                                <span class="absolute -left-[41px] top-1.5 w-4 h-4 rounded-full bg-blue-500 border-4 border-white dark:border-gray-800 ring-2 ring-gray-100 dark:ring-gray-900"></span>
                                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700/50">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                                        <h3 class="text-blue-600 dark:text-blue-400 font-medium text-sm">Dipinjam</h3>
                                        <span class="text-xs text-gray-500"><?= esc(barang_dt($loan['tanggal_pinjam'])) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Oleh <span class="text-gray-900 dark:text-white font-medium"><?= esc($loan['user_nama'] ?? 'Pengguna Terhapus') ?></span> (<?= esc($loan['user_role'] ?? '-') ?>)</p>
                                    <?php if ($loan['lokasi_peminjaman']): ?><p class="text-sm text-gray-500 dark:text-gray-400">Lokasi Barang: <?= esc($loan['lokasi_peminjaman']) ?></p><?php endif ?>
                                </div>
                            </div>
                        <?php endforeach ?>

                        <div class="relative">
                            <span class="absolute -left-[41px] top-1.5 w-4 h-4 rounded-full bg-gray-500 border-4 border-white dark:border-gray-800 ring-2 ring-gray-100 dark:ring-gray-900"></span>
                            <div class="bg-gray-50 dark:bg-gray-900/30 rounded-lg p-4 border border-gray-200 dark:border-gray-700/30">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-1">
                                    <h3 class="text-gray-600 dark:text-gray-400 font-medium text-sm">Barang Masuk</h3>
                                    <span class="text-xs text-gray-500 dark:text-gray-600"><?= esc(barang_dt($barang['created_at'] ?? null)) ?></span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-500">Barang ditambahkan ke sistem.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>

<div id="imageModal" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity opacity-0" onclick="closeModal()">
    <button class="absolute top-4 right-4 text-white hover:text-gray-300 p-2">x</button>
    <img id="modalImage" src="" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl scale-95 transition-transform">
</div>

<script>
function changePhoto(src) {
    document.getElementById('main-photo').src = src;
}
function openModal(src) {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('modalImage');
    img.src = src;
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        img.classList.remove('scale-95');
        img.classList.add('scale-100');
    }, 10);
}
function closeModal() {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('modalImage');
    modal.classList.add('opacity-0');
    img.classList.remove('scale-100');
    img.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}
</script>
<?= $this->endSection() ?>
