<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$fmtQty = static function (array $item): string {
    return ($item['aset']['tipe'] ?? '') === 'kabel'
        ? number_format((int) $item['jumlah_meter']) . ' m'
        : number_format((int) $item['jumlah_pcs']) . ' pcs';
};
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('pengambilan/admin') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Konfirmasi Pengambilan #<?= esc($pengambilan['id']) ?></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex flex-col">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ringkasan Pengambilan</h2>
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 mb-1">Mitra</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?= esc($pengambilan['nama_mitra']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Petugas</p>
                        <p class="font-medium text-gray-900 dark:text-white"><?= esc($pengambilan['nama_petugas']) ?></p>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Item yang Diambil</h3>
                    <div class="space-y-2">
                        <?php foreach ($pengambilan['items'] as $item): ?>
                            <div class="flex justify-between py-2.5 text-xs border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                                <span class="text-gray-700 dark:text-gray-300"><?= esc($item['aset']['nama'] ?? 'N/A') ?></span>
                                <span class="font-semibold text-gray-900 dark:text-white"><?= esc($fmtQty($item)) ?></span>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <?php if (! empty($pengambilan['ttd_petugas'])): ?>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-3">Tanda Tangan Petugas</label>
                        <div class="relative rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 overflow-hidden py-3">
                            <img src="<?= esc($pengambilan['ttd_petugas']) ?>" class="h-48 mx-auto object-contain relative z-10" alt="Tanda tangan petugas">
                        </div>
                    </div>
                <?php endif ?>
            </div>
        </section>

        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm overflow-hidden flex flex-col">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Foto Bukti dari Petugas</h2>
            <?php if (! empty($pengambilan['fotos'])): ?>
                <div class="grid grid-cols-3 gap-2 overflow-y-auto pr-1" style="max-height:350px">
                    <?php foreach ($pengambilan['fotos'] as $foto): ?>
                        <a href="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" target="_blank" class="group relative aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 shadow-sm hover:shadow-md transition-all">
                            <img src="<?= base_url(ltrim($foto['foto_path'], '/')) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="Bukti pengambilan">
                            <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded bg-black/60 text-white text-[10px]"><?= esc($foto['tipe']) ?></span>
                        </a>
                    <?php endforeach ?>
                </div>
            <?php else: ?>
                <div class="flex-1 flex flex-col items-center justify-center py-12 text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                    <svg class="w-12 h-12 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p class="text-sm italic">Tidak ada foto bukti</p>
                </div>
            <?php endif ?>
        </section>
    </div>

    <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg shadow-black/5 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/50">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Konfirmasi Admin</h2>
            <button type="button" onclick="clearSignature()" class="text-xs font-semibold text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 flex items-center gap-1.5 transition-colors">Bersihkan Area</button>
        </div>

        <form id="confirm-form" action="<?= site_url('pengambilan/admin/confirm/' . $pengambilan['id']) ?>" method="POST" class="p-6 pt-4">
            <div class="relative mb-6">
                <div class="relative rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 overflow-hidden group hover:border-blue-300 dark:hover:border-blue-900 transition-colors">
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03] dark:opacity-[0.05] select-none">
                        <span class="text-8xl font-black uppercase tracking-tighter">OFFICIAL USE</span>
                    </div>
                    <canvas id="signature-pad" class="w-full h-48 cursor-crosshair active:cursor-grabbing relative z-10"></canvas>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-4">
                <input type="hidden" name="ttd_admin" id="ttd-input">
                <button type="submit" class="w-full md:flex-1 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-lg transition-all shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 active:scale-[0.98]">Konfirmasi</button>
                <a href="<?= site_url('pengambilan/admin/detail/' . $pengambilan['id']) ?>" class="w-full md:w-auto px-10 py-4 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all font-semibold text-center">Batalkan</a>
            </div>
        </form>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
const canvas = document.getElementById('signature-pad');
const signaturePad = new SignaturePad(canvas, {
    minWidth: 1.5,
    maxWidth: 4.5,
    penColor: 'rgb(0, 0, 0)'
});

function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    signaturePad.clear();
}

function clearSignature() {
    signaturePad.clear();
}

window.addEventListener('resize', resizeCanvas);
resizeCanvas();

document.getElementById('confirm-form').addEventListener('submit', function (event) {
    if (signaturePad.isEmpty()) {
        event.preventDefault();
        alert('Silakan tanda tangan terlebih dahulu');
        return;
    }
    document.getElementById('ttd-input').value = signaturePad.toDataURL();
});
</script>

<?= $this->endSection() ?>
