<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$fmt = static fn ($date): string => $date ? date('d M Y H:i', strtotime($date)) : '-';
$image = ! empty($peminjaman['barang']['fotos'][0]['foto_path']) ? base_url(ltrim($peminjaman['barang']['fotos'][0]['foto_path'], '/')) : null;
$isLate = strtotime((string) $peminjaman['tanggal_kembali_rencana']) < time();
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('peminjaman/current') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengembalian Barang</h1>
            <p class="text-gray-500 dark:text-gray-400"><?= esc($peminjaman['barang']['nama_barang']) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <aside class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden sticky top-24 shadow-sm">
                <?php if ($image): ?>
                    <img src="<?= esc($image) ?>" alt="<?= esc($peminjaman['barang']['nama_barang']) ?>" class="w-full aspect-video object-cover">
                <?php else: ?>
                    <div class="w-full aspect-video bg-gray-100 dark:bg-gray-900 flex items-center justify-center text-gray-400">No image</div>
                <?php endif ?>
                <div class="p-4 space-y-3">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-lg"><?= esc($peminjaman['barang']['nama_barang']) ?></h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">SN: <?= esc($peminjaman['barang']['nomor_seri']) ?></p>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3"><span class="text-gray-500">Dipinjam:</span><span class="text-gray-700 dark:text-gray-300 text-right"><?= esc($fmt($peminjaman['tanggal_pinjam'])) ?></span></div>
                        <div class="flex justify-between gap-3"><span class="text-gray-500">Deadline:</span><span class="text-gray-700 dark:text-gray-300 text-right"><?= esc($fmt($peminjaman['tanggal_kembali_rencana'])) ?></span></div>
                        <div class="flex justify-between gap-3"><span class="text-gray-500">Lokasi:</span><span class="text-gray-700 dark:text-gray-300 text-right"><?= esc($peminjaman['lokasi_peminjaman']) ?></span></div>
                    </div>
                    <?php if ($isLate): ?>
                        <div class="bg-red-50 border border-red-200 dark:bg-red-500/10 dark:border-red-500/30 rounded-lg p-3 text-center">
                            <p class="text-red-600 dark:text-red-400 font-medium">Terlambat</p>
                            <p class="text-sm text-red-500 dark:text-red-300">Pengembalian melebihi batas waktu</p>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </aside>

        <div class="lg:col-span-2">
            <form action="<?= site_url('peminjaman/return/' . $peminjaman['id']) ?>" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 space-y-6 shadow-sm">
                <input type="hidden" name="barcode_detected" id="barcode_detected" value="">

                <section class="bg-blue-50 border border-blue-200 dark:bg-blue-500/5 dark:border-blue-500/30 rounded-lg p-4">
                    <h4 class="font-medium text-blue-600 dark:text-blue-400 mb-2">Verifikasi Identitas Barang</h4>
                    <?php if ($peminjaman['barang']['wajib_qr']): ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Scan barcode untuk memverifikasi barang yang dikembalikan</p>
                        <button type="button" onclick="openScannerForVerification('<?= esc($peminjaman['barang']['nomor_seri'], 'js') ?>')" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-blue-500/10 border border-blue-300 dark:border-blue-500 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/20">Scan Barcode</button>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Input nomor seri barang secara manual</p>
                        <input type="text" id="manual_sn_input" class="w-full px-4 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Input nomor seri manual..." oninput="verifyManualSN(this.value, '<?= esc($peminjaman['barang']['nomor_seri'], 'js') ?>')">
                    <?php endif ?>
                    <div id="scan-result" class="hidden mt-3 px-3 py-2 rounded-lg text-sm"></div>
                </section>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto Barang Saat Pengembalian</label>
                    <p class="text-xs text-gray-500 mb-3">Ambil foto kondisi barang saat dikembalikan</p>
                    <div class="flex gap-2 mb-3">
                        <button type="button" onclick="openCamera()" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-50 border border-teal-200 text-teal-600 dark:bg-teal-500/10 dark:border-teal-500 dark:text-teal-400 rounded-lg hover:bg-teal-100 dark:hover:bg-teal-500/20">Ambil Foto</button>
                        <label class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 text-blue-600 dark:bg-blue-500/10 dark:border-blue-500 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 cursor-pointer">
                            Upload
                            <input type="file" name="fotos[]" multiple accept="image/*" class="hidden" onchange="previewPhotos(this)">
                        </label>
                    </div>
                    <div id="photo_preview" class="grid grid-cols-3 gap-4 hidden"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="Catatan kondisi barang saat pengembalian"></textarea>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 shadow-lg shadow-blue-500/25">Konfirmasi Pengembalian</button>
                    <a href="<?= site_url('peminjaman/current') ?>" class="px-6 py-3 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openScannerForVerification(expectedCode) {
    window.expectedBarcode = expectedCode;
    openScanner('barcode_detected', true);
}
function verifyManualSN(value, expected) {
    const resultDiv = document.getElementById('scan-result');
    const barcodeInput = document.getElementById('barcode_detected');
    if (value === expected) {
        resultDiv.className = 'mt-3 px-3 py-2 rounded-lg text-sm bg-green-50 text-green-600';
        resultDiv.textContent = 'Nomor seri cocok';
        barcodeInput.value = value;
    } else {
        resultDiv.className = 'mt-3 px-3 py-2 rounded-lg text-sm bg-red-50 text-red-600';
        resultDiv.textContent = 'Nomor seri tidak cocok';
        barcodeInput.value = '';
    }
}
function previewPhotos(input) {
    const preview = document.getElementById('photo_preview');
    preview.innerHTML = '';
    preview.classList.remove('hidden');
    for (const file of input.files) {
        const reader = new FileReader();
        reader.onload = event => {
            const div = document.createElement('div');
            div.className = 'aspect-square bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden';
            div.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?= $this->endSection() ?>
