<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('pengambilan/detail/' . $pengambilan['id']) ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pickup Aset #<?= esc($pengambilan['id']) ?></h1>
    </div>

    <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Item yang Diambil</h2>
        <div class="space-y-3">
            <?php foreach ($pengambilan['items'] as $item): ?>
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white"><?= esc($item['aset']['nama']) ?></p>
                        <p class="text-sm text-gray-500"><?= esc(strtoupper($item['aset']['tipe'])) ?> - <?= esc($item['aset']['core']) ?> Core</p>
                    </div>
                    <p class="font-semibold text-gray-900 dark:text-white"><?= $item['aset']['tipe'] === 'kabel' ? esc($item['jumlah_meter']) . ' meter' : esc($item['jumlah_pcs']) . ' pcs' ?></p>
                </div>
            <?php endforeach ?>
        </div>
    </section>

    <form action="<?= site_url('pengambilan/pickup/' . $pengambilan['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-8" id="pickup-form">
        <div class="space-y-6">
            <?php foreach ($pengambilan['items'] as $item): ?>
                <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
                    <div class="mb-6 pb-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?= esc($item['aset']['nama']) ?></h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 mt-2"><?= esc(strtoupper($item['aset']['tipe'])) ?></span>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= $item['aset']['tipe'] === 'kabel' ? esc($item['jumlah_meter']) : esc($item['jumlah_pcs']) ?></p>
                            <p class="text-sm text-gray-500 uppercase font-medium"><?= $item['aset']['tipe'] === 'kabel' ? 'Meter' : 'Unit' ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php if ($item['aset']['tipe'] === 'kabel'): ?>
                            <?php foreach (['kabel_ujung1' => 'Ujung Kiri', 'kabel_ujung2' => 'Ujung Kanan', 'kabel_roll' => 'Gulungan'] as $suffix => $label): ?>
                                <div><label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"><?= esc($label) ?> *</label><input type="file" name="item_<?= esc($item['id']) ?>_<?= esc($suffix) ?>" accept="image/*" class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100"></div>
                            <?php endforeach ?>
                        <?php else: ?>
                            <div><label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Foto Bukti 1 *</label><input type="file" name="item_<?= esc($item['id']) ?>_general_1" accept="image/*" class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100"></div>
                            <div><label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Foto Bukti 2</label><input type="file" name="item_<?= esc($item['id']) ?>_general_2" accept="image/*" class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100"></div>
                        <?php endif ?>
                    </div>
                </section>
            <?php endforeach ?>
        </div>

        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-8 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Tanda Tangan Petugas</h2>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden mx-auto max-w-lg">
                <canvas id="signature-pad" class="w-full h-48 cursor-crosshair"></canvas>
            </div>
            <div class="flex justify-center mt-4"><button type="button" onclick="clearSignature()" class="px-4 py-2 text-sm text-red-600 bg-red-50 hover:bg-red-100 rounded-lg font-medium">Hapus Tanda Tangan</button></div>
            <input type="hidden" name="ttd_petugas" id="ttd-input">
        </section>

        <div class="flex gap-4 pt-4">
            <button type="submit" class="flex-1 px-8 py-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold">Submit Pickup</button>
            <a href="<?= site_url('pengambilan/detail/' . $pengambilan['id']) ?>" class="px-8 py-4 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 font-semibold">Batal</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
const canvas = document.getElementById('signature-pad');
const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgba(0,0,0,0)', penColor: 'rgb(0, 0, 0)' });
function resizeCanvas(){const ratio=Math.max(window.devicePixelRatio||1,1);canvas.width=canvas.offsetWidth*ratio;canvas.height=canvas.offsetHeight*ratio;canvas.getContext('2d').scale(ratio,ratio);}
window.addEventListener('resize', resizeCanvas); setTimeout(resizeCanvas, 100);
function clearSignature(){signaturePad.clear();}
document.getElementById('pickup-form').addEventListener('submit', function(e){if(signaturePad.isEmpty()){e.preventDefault();alert('Harap tanda tangan terlebih dahulu.');return false;}document.getElementById('ttd-input').value=signaturePad.toDataURL();});
</script>

<?= $this->endSection() ?>
