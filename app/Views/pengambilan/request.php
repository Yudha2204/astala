<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= site_url('pengambilan') ?>" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Request Pengambilan Aset</h1>
    </div>

    <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
        <form action="<?= site_url('pengambilan/request') ?>" method="POST" id="request-form" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Gudang *</label>
                <select name="gudang_id" id="gudang-select" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                    <option value="">-- Pilih Gudang --</option>
                    <?php foreach ($gudangs as $gudang): ?>
                        <option value="<?= esc($gudang['id']) ?>" data-asets='<?= esc(json_encode($gudang['asetMaterials']), 'attr') ?>'><?= esc($gudang['nama']) ?> (<?= esc($gudang['tipe']) ?>)</option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Mitra</label>
                    <input type="text" value="<?= esc($user['nama'] ?? '') ?>" disabled class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Petugas Pengambil *</label>
                    <input type="text" name="nama_petugas" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="Nama petugas yang akan mengambil barang">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi Keperluan</label>
                <textarea name="deskripsi_keperluan" rows="3" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white" placeholder="Jelaskan keperluan pengambilan aset..."></textarea>
            </div>

            <div id="items-section" class="hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Aset *</label>
                <div id="aset-list" class="space-y-3"></div>
                <button type="button" onclick="addItem()" class="mt-3 px-4 py-2 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 text-sm">+ Tambah Item</button>
            </div>

            <input type="hidden" name="items" id="items-data" value="[]">

            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Submit Request</button>
                <a href="<?= site_url('pengambilan') ?>" class="px-6 py-3 bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Batal</a>
            </div>
        </form>
    </section>
</div>

<script>
let currentAsets = [];
let itemCount = 0;

document.getElementById('gudang-select').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const asetsData = selectedOption.getAttribute('data-asets');
    if (asetsData) {
        currentAsets = JSON.parse(asetsData);
        document.getElementById('items-section').classList.remove('hidden');
        document.getElementById('aset-list').innerHTML = '';
        itemCount = 0;
        addItem();
    } else {
        document.getElementById('items-section').classList.add('hidden');
    }
});

function addItem() {
    itemCount++;
    const container = document.getElementById('aset-list');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'flex flex-wrap gap-3 items-end p-4 bg-gray-50 dark:bg-gray-900 rounded-lg';
    itemDiv.id = 'item-' + itemCount;
    let options = '<option value="">Pilih Aset</option>';
    currentAsets.forEach(a => {
        options += `<option value="${a.id}" data-tipe="${a.tipe}" data-stok-meter="${a.stok_meter}" data-stok-pcs="${a.stok_pcs}" data-meter-per-roll="${a.meter_per_roll}">${a.nama}</option>`;
    });
    itemDiv.innerHTML = `
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-gray-500 mb-1">Aset</label>
            <select onchange="checkAvailability(${itemCount})" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-sm" id="aset-select-${itemCount}">${options}</select>
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs text-gray-500 mb-1">Jumlah</label>
            <input type="number" min="1" value="1" class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-sm" id="qty-input-${itemCount}" onchange="checkAvailability(${itemCount})" oninput="checkAvailability(${itemCount})">
        </div>
        <div class="min-w-[120px]">
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <div class="px-3 py-2 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-400" id="indicator-display-${itemCount}">-</div>
        </div>
        <button type="button" onclick="removeItem(${itemCount})" class="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg">Hapus</button>`;
    container.appendChild(itemDiv);
}

function checkAvailability(idx) {
    const select = document.getElementById('aset-select-' + idx);
    const qty = parseInt(document.getElementById('qty-input-' + idx).value) || 0;
    const indicator = document.getElementById('indicator-display-' + idx);
    if (!select.value) {
        indicator.textContent = '-';
        updateItems();
        return;
    }
    const option = select.options[select.selectedIndex];
    const tipe = option.getAttribute('data-tipe');
    const currentStock = tipe === 'kabel' ? parseInt(option.getAttribute('data-stok-meter')) || 0 : parseInt(option.getAttribute('data-stok-pcs')) || 0;
    const available = currentStock >= qty;
    indicator.innerHTML = available ? '<span class="text-green-600 font-medium">Tersedia</span>' : '<span class="text-red-600 font-medium">Tidak Cukup</span>';
    updateItems();
}

function removeItem(idx) {
    document.getElementById('item-' + idx)?.remove();
    updateItems();
}

function updateItems() {
    const items = [];
    document.querySelectorAll('[id^="aset-select-"]').forEach(select => {
        if (!select.value) return;
        const idx = select.id.replace('aset-select-', '');
        const option = select.options[select.selectedIndex];
        const tipe = option.getAttribute('data-tipe');
        const qty = parseInt(document.getElementById('qty-input-' + idx).value) || 0;
        const item = { aset_id: parseInt(select.value) };
        if (tipe === 'kabel') {
            const meterPerRoll = parseInt(option.getAttribute('data-meter-per-roll')) || 4000;
            item.jumlah_meter = qty;
            item.jumlah_roll = qty / meterPerRoll;
        } else {
            item.jumlah_pcs = qty;
        }
        items.push(item);
    });
    document.getElementById('items-data').value = JSON.stringify(items);
}

document.getElementById('request-form').addEventListener('submit', function (event) {
    updateItems();
    if (JSON.parse(document.getElementById('items-data').value).length === 0) {
        event.preventDefault();
        alert('Silakan pilih minimal satu aset');
    }
    if ([...document.querySelectorAll('[id^="indicator-display-"]')].some(el => el.textContent.includes('Tidak Cukup'))) {
        event.preventDefault();
        alert('Ada item dengan stok tidak mencukupi.');
    }
});
</script>

<?= $this->endSection() ?>
