<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$canEdit = ($user['role'] ?? '') === 'admin' || (in_array($user['role'] ?? '', ['manager', 'karyawan'], true) && ($user['sub_user'] ?? '') === 'editor');
$baseQuery = [
    'search' => $query['search'] ?? '',
    'status_kondisi' => $query['status_kondisi'] ?? '',
    'status_ketersediaan' => $query['status_ketersediaan'] ?? '',
];

function barang_page_url(int $page, array $query): string
{
    return site_url('barang') . '?' . http_build_query([...$query, 'page' => $page]);
}

function barang_export_pdf_url(array $query): string
{
    $params = array_filter($query, static fn ($value): bool => $value !== '' && $value !== null);

    return site_url('admin/report/pdf/inventory') . ($params ? '?' . http_build_query($params) : '');
}
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daftar Barang Inventaris</h1>
            <p class="text-gray-500 dark:text-gray-400">Total <?= esc($pagination['total']) ?> barang</p>
        </div>
        <?php if ($canEdit): ?>
            <div class="flex items-center gap-2">
                <a href="<?= site_url('barang/add') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-500 text-white font-medium rounded-lg hover:from-blue-600 hover:to-blue-600 transition-all shadow-lg shadow-blue-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Barang
                </a>
                <a href="<?= esc(barang_export_pdf_url($baseQuery)) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 border border-gray-200 transition-all shadow-sm">
                    Export PDF
                </a>
            </div>
        <?php endif ?>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
        <form id="filterForm" action="<?= site_url('barang') ?>" method="GET" class="flex flex-col gap-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="search" id="searchInput" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari nama barang..." autofocus class="w-full pl-4 pr-12 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <button type="button" onclick="openScanner('searchInput')" class="absolute right-1 top-1/2 -translate-y-1/2 p-1.5 bg-blue-500/10 text-blue-600 hover:bg-blue-500/20 rounded-md transition-colors" title="Scan QR/Barcode">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </button>
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 transition-colors shadow-lg shadow-blue-500/20">Cari</button>
                </div>

                <div class="min-w-[200px] md:w-56">
                    <input type="hidden" name="status_kondisi" id="status_kondisi" value="<?= esc($query['status_kondisi'] ?? '') ?>">
                    <input type="hidden" name="status_ketersediaan" id="status_ketersediaan" value="<?= esc($query['status_ketersediaan'] ?? '') ?>">
                    <?php
                    $filterValue = '';
                    if (($query['status_kondisi'] ?? '') !== '') {
                        $filterValue = 'kondisi-' . $query['status_kondisi'];
                    } elseif (($query['status_ketersediaan'] ?? '') !== '') {
                        $filterValue = 'status-' . $query['status_ketersediaan'];
                    }
                    ?>
                    <select id="unifiedFilter" onchange="handleFilterChange(this.value)" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer">
                        <option value="">Semua Filter</option>
                        <option value="kondisi-baik" <?= $filterValue === 'kondisi-baik' ? 'selected' : '' ?>>Baik</option>
                        <option value="kondisi-rusak" <?= $filterValue === 'kondisi-rusak' ? 'selected' : '' ?>>Rusak</option>
                        <option value="status-tersedia" <?= $filterValue === 'status-tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="status-dipinjam" <?= $filterValue === 'status-dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <?php if ($barangs): ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto max-h-[600px] relative">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-900/80 backdrop-blur-sm sticky top-0 z-20">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Barang</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No Seri</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kondisi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <?php if ($canEdit): ?><th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">QR</th><?php endif ?>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky right-0 bg-gray-50 dark:bg-gray-900/80 z-30">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($barangs as $barang): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 border border-gray-200 dark:border-gray-600">
                                            <?php if (! empty($barang['fotos'])): ?>
                                                <img src="<?= base_url(ltrim($barang['fotos'][0]['foto_path'], '/')) ?>" alt="<?= esc($barang['nama_barang']) ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                            <?php endif ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white truncate max-w-[150px] text-xs"><?= esc($barang['nama_barang']) ?></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($barang['kategori'] ?: '-') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300 font-mono"><?= esc($barang['nomor_seri']) ?></td>
                                <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-300"><?= esc($barang['lokasi_penyimpanan'] ?: '-') ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $barang['status_kondisi'] === 'baik' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400' ?>">
                                        <?= $barang['status_kondisi'] === 'baik' ? 'Baik' : 'Rusak' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $barang['status_ketersediaan'] === 'tersedia' ? 'bg-teal-100 text-teal-800 dark:bg-teal-500/10 dark:text-teal-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400' ?>">
                                        <?= $barang['status_ketersediaan'] === 'tersedia' ? 'Tersedia' : 'Dipinjam' ?>
                                    </span>
                                </td>
                                <?php if ($canEdit): ?>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer" <?= $barang['wajib_qr'] ? 'checked' : '' ?> onchange="toggleQR('<?= esc($barang['id']) ?>', this.checked)">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                    </td>
                                <?php endif ?>
                                <td class="px-6 py-4 sticky right-0 bg-white dark:bg-gray-800 z-10">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= site_url('barang/detail/' . $barang['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-600 hover:bg-purple-100 border border-purple-200 rounded-lg transition-colors text-xs dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30 dark:hover:bg-purple-500/20">Detail</a>
                                        <?php if ($canEdit): ?>
                                            <a href="<?= site_url('barang/edit/' . $barang['id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors text-xs dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30 dark:hover:bg-blue-500/20">Edit</a>
                                        <?php endif ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="flex items-center justify-center gap-2">
                <?php if ($pagination['hasPrev']): ?>
                    <a href="<?= barang_page_url($pagination['page'] - 1, $baseQuery) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Prev</a>
                <?php endif ?>
                <span class="px-4 py-2 text-gray-500 dark:text-gray-400">Halaman <?= esc($pagination['page']) ?> dari <?= esc($pagination['totalPages']) ?></span>
                <?php if ($pagination['hasNext']): ?>
                    <a href="<?= barang_page_url($pagination['page'] + 1, $baseQuery) ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Next</a>
                <?php endif ?>
            </div>
        <?php endif ?>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-12 text-center shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak ada barang ditemukan</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Coba ubah filter pencarian Anda</p>
            <?php if ($canEdit): ?>
                <a href="<?= site_url('barang/add') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">Tambah Barang Pertama</a>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>

<script>
function handleFilterChange(val) {
    document.getElementById('status_kondisi').value = '';
    document.getElementById('status_ketersediaan').value = '';
    if (val.startsWith('kondisi-')) document.getElementById('status_kondisi').value = val.replace('kondisi-', '');
    if (val.startsWith('status-')) document.getElementById('status_ketersediaan').value = val.replace('status-', '');
    document.getElementById('filterForm').submit();
}

let searchTimeout;
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => document.getElementById('filterForm').submit(), 800);
    });
    searchInput.focus();
    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
}

async function toggleQR(id, wajib_qr) {
    try {
        const response = await fetch(`/barang/toggle-qr/${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ wajib_qr })
        });
        const result = await response.json();
        if (!result.success) {
            alert(result.message || 'Gagal memperbarui status QR');
            location.reload();
        }
    } catch (error) {
        alert('Terjadi kesalahan koneksi.');
        location.reload();
    }
}
</script>
<?= $this->endSection() ?>
