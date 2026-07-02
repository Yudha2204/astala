<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$query = $query ?? [];
$imageUrl = static fn (array $barang): ?string => ! empty($barang['fotos'][0]['foto_path']) ? base_url(ltrim($barang['fotos'][0]['foto_path'], '/')) : null;
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pinjam Barang</h1>
            <p class="text-gray-500 dark:text-gray-400">Pilih barang yang ingin dipinjam</p>
        </div>
        <form action="<?= site_url('peminjaman/items') ?>" method="GET" class="flex flex-col sm:flex-row gap-2 flex-1 max-w-xl">
            <div class="relative flex-1">
                <input type="text" name="search" id="searchPeminjaman" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari barang..."
                    class="w-full pl-4 pr-12 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button type="button" onclick="openScanner('searchPeminjaman')" class="absolute right-1 top-1/2 -translate-y-1/2 p-1.5 bg-blue-500/10 text-blue-600 hover:bg-blue-500/20 rounded-md" title="Scan QR/Barcode">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </button>
            </div>
            <select name="status" id="statusPeminjaman" class="w-full sm:w-auto px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer">
                <option value="">Semua Status</option>
                <option value="tersedia" <?= ($query['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                <option value="dipinjam" <?= ($query['status'] ?? '') === 'dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam</option>
            </select>
            <button type="submit" class="px-6 py-2.5 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 shadow-lg shadow-blue-500/20">Cari</button>
        </form>
    </div>

    <div id="items-container" class="space-y-6">
        <section>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Barang Tersedia</h2>
            <?php if ($tersedia): ?>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto max-h-[600px] relative">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-50 dark:bg-gray-900/80 sticky top-0 z-20">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">No Seri</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lokasi</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase sticky right-0 bg-gray-50 dark:bg-gray-900/80">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($tersedia as $barang): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden border border-gray-200 dark:border-gray-600">
                                                    <?php if ($imageUrl($barang)): ?>
                                                        <img src="<?= esc($imageUrl($barang)) ?>" alt="<?= esc($barang['nama_barang']) ?>" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <div class="w-full h-full flex items-center justify-center text-gray-400">-</div>
                                                    <?php endif ?>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white truncate max-w-[220px] text-xs"><?= esc($barang['nama_barang']) ?></p>
                                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase"><?= esc($barang['kategori'] ?: '-') ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 font-mono"><?= esc($barang['nomor_seri']) ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300"><?= esc($barang['lokasi_penyimpanan'] ?: '-') ?></td>
                                        <td class="px-6 py-4 sticky right-0 bg-white dark:bg-gray-800 text-center">
                                            <a href="<?= site_url('peminjaman/form') . '?barang_id=' . $barang['id'] ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 rounded-lg text-xs dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30">Pinjam</a>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center shadow-sm">
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada barang tersedia saat ini</p>
                </div>
            <?php endif ?>
        </section>

        <?php if ($tidakTersedia): ?>
            <section>
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-500 mb-4">Tidak Tersedia</h2>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm opacity-75">
                    <div class="overflow-x-auto max-h-[600px] relative">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-50 dark:bg-gray-900/80 sticky top-0 z-20">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barang</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">No Seri</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lokasi</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase sticky right-0 bg-gray-50 dark:bg-gray-900/80">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($tidakTersedia as $barang): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden grayscale border border-gray-200 dark:border-gray-600">
                                                    <?php if ($imageUrl($barang)): ?>
                                                        <img src="<?= esc($imageUrl($barang)) ?>" alt="<?= esc($barang['nama_barang']) ?>" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <div class="w-full h-full flex items-center justify-center text-gray-400">-</div>
                                                    <?php endif ?>
                                                </div>
                                                <div class="opacity-60">
                                                    <p class="text-xs font-medium text-gray-900 dark:text-white truncate max-w-[220px]"><?= esc($barang['nama_barang']) ?></p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($barang['kategori'] ?: '-') ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 font-mono"><?= esc($barang['nomor_seri']) ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400"><?= esc($barang['lokasi_penyimpanan'] ?: '-') ?></td>
                                        <td class="px-6 py-4 sticky right-0 bg-white dark:bg-gray-800 text-center">
                                            <?php $isRusak = $barang['status_kondisi'] === 'rusak'; ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $isRusak ? 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400' ?>">
                                                <?= $isRusak ? 'Rusak' : 'Dipinjam' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif ?>
    </div>
</div>

<script>
const searchInput = document.getElementById('searchPeminjaman');
const statusDropdown = document.getElementById('statusPeminjaman');
const itemsContainer = document.getElementById('items-container');
let debounceTimer;

function fetchItems() {
    clearTimeout(debounceTimer);
    const query = searchInput ? searchInput.value : '';
    const status = statusDropdown ? statusDropdown.value : '';
    debounceTimer = setTimeout(() => {
        fetch(`<?= site_url('peminjaman/items') ?>?search=${encodeURIComponent(query)}&status=${encodeURIComponent(status)}`)
            .then(response => response.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.getElementById('items-container');
                if (next) itemsContainer.innerHTML = next.innerHTML;
                const url = new URL(window.location);
                if (query) url.searchParams.set('search', query);
                else url.searchParams.delete('search');
                if (status) url.searchParams.set('status', status);
                else url.searchParams.delete('status');
                window.history.replaceState({}, '', url);
            });
    }, 300);
}

if (searchInput && itemsContainer) {
    searchInput.addEventListener('input', fetchItems);
    if (statusDropdown) {
        statusDropdown.addEventListener('change', fetchItems);
    }
    searchInput.closest('form').addEventListener('submit', event => event.preventDefault());
}
</script>

<?= $this->endSection() ?>
