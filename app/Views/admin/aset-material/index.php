<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$canEdit = ($user['role'] ?? '') === 'admin' || (in_array($user['role'] ?? '', ['manager', 'karyawan'], true) && ($user['sub_user'] ?? '') === 'editor');
$query = $query ?? [];
$badgeClass = static fn (string $tipe): string => match ($tipe) {
    'kabel' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    'odp' => 'bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400',
    default => 'bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400',
};
$isLow = static function (array $aset): bool {
    return $aset['tipe'] === 'kabel'
        ? (int) $aset['min_stok_roll'] > 0 && (int) $aset['stok_roll'] <= (int) $aset['min_stok_roll']
        : (int) $aset['min_stok_pcs'] > 0 && (int) $aset['stok_pcs'] <= (int) $aset['min_stok_pcs'];
};
$exportUrl = static function () use ($query): string {
    $params = array_filter([
        'search' => $query['search'] ?? '',
        'gudang_id' => $query['gudang_id'] ?? '',
        'tipe' => $query['tipe'] ?? '',
    ], static fn ($value) => $value !== '' && $value !== null);

    return site_url('admin/report/pdf/aset-material') . ($params ? '?' . http_build_query($params) : '');
};
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Aset Material</h1>
            <p class="text-gray-500 dark:text-gray-400">Kelola stok kabel, ODP, dan Closure</p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($canEdit): ?>
                <a href="<?= site_url('admin/aset-material/add') ?>" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 w-fit shadow-lg shadow-blue-500/20">Tambah Aset</a>
            <?php endif ?>
            <a href="<?= esc($exportUrl()) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-gray-700 font-medium rounded-lg hover:bg-gray-50 border border-gray-200 shadow-sm h-fit">Export PDF</a>
        </div>
    </div>

    <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
        <form action="<?= site_url('admin/aset-material') ?>" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 flex gap-2">
                <input type="text" name="search" value="<?= esc($query['search'] ?? '') ?>" placeholder="Cari aset..." class="w-full pl-4 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-500/20">Cari</button>
            </div>
            <select name="gudang_id" onchange="this.form.submit()" class="px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white min-w-[200px]">
                <option value="">Semua Gudang</option>
                <?php foreach ($gudangs as $gudang): ?>
                    <option value="<?= esc($gudang['id']) ?>" <?= (int) ($query['gudang_id'] ?? 0) === (int) $gudang['id'] ? 'selected' : '' ?>><?= esc($gudang['nama']) ?></option>
                <?php endforeach ?>
            </select>
            <select name="tipe" onchange="this.form.submit()" class="px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white min-w-[150px]">
                <option value="">Semua Tipe</option>
                <option value="kabel" <?= ($query['tipe'] ?? '') === 'kabel' ? 'selected' : '' ?>>Kabel ADSS</option>
                <option value="odp" <?= ($query['tipe'] ?? '') === 'odp' ? 'selected' : '' ?>>ODP</option>
                <option value="closure" <?= ($query['tipe'] ?? '') === 'closure' ? 'selected' : '' ?>>Closure</option>
            </select>
        </form>
    </section>

    <?php foreach ($asetsByGudang as $gudangNama => $data): ?>
        <section class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <?= esc($gudangNama) ?>
                    <?php if ($data['gudang']): ?>
                        <span class="px-2 py-0.5 text-xs font-medium rounded <?= ($data['gudang']['tipe'] ?? '') === 'indoor' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' ?>"><?= esc($data['gudang']['tipe'] ?? '-') ?></span>
                    <?php endif ?>
                </h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto max-h-[600px] relative">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="bg-gray-50 dark:bg-gray-900/80 sticky top-0 z-20">
                            <tr>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Nama</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Core</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Stok</th>
                                <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase sticky right-0 bg-gray-50 dark:bg-gray-900/80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ([...$data['kabel'], ...$data['odp'], ...$data['closure']] as $aset): ?>
                                <?php $low = $isLow($aset); ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="py-3 px-4"><span class="px-2 py-1 text-xs font-medium rounded <?= $badgeClass($aset['tipe']) ?>"><?= esc(strtoupper($aset['tipe'])) ?></span></td>
                                    <td class="py-3 px-4 text-gray-900 dark:text-white font-medium text-xs"><?= esc($aset['nama']) ?></td>
                                    <td class="py-3 px-4 text-gray-600 dark:text-gray-300 text-xs text-center"><?= esc($aset['core']) ?> Core</td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <?php if ($aset['tipe'] === 'kabel'): ?>
                                                <span class="font-semibold <?= $low ? 'text-red-500' : 'text-gray-900 dark:text-white' ?> text-xs"><?= esc($aset['stok_roll']) ?> Roll</span>
                                                <span class="text-gray-500 dark:text-gray-400 text-[10px]">(<?= number_format((int) $aset['stok_meter']) ?> m)</span>
                                            <?php else: ?>
                                                <span class="font-semibold <?= $low ? 'text-red-500' : 'text-gray-900 dark:text-white' ?> text-xs"><?= esc($aset['stok_pcs']) ?> Pcs</span>
                                            <?php endif ?>
                                            <?php if ($low): ?><span class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400 rounded">Low</span><?php endif ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap sticky right-0 bg-white dark:bg-gray-800">
                                        <div class="flex justify-center gap-2">
                                            <?php if ($canEdit): ?>
                                                <a href="<?= site_url('admin/aset-material/add-stock/' . $aset['id']) ?>" class="px-3 py-1.5 text-xs bg-green-50 text-green-600 hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400 rounded">+ Stok</a>
                                                <a href="<?= site_url('admin/aset-material/edit/' . $aset['id']) ?>" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 rounded">Edit</a>
                                            <?php endif ?>
                                            <a href="<?= site_url('admin/aset-material/detail/' . $aset['id']) ?>" class="px-3 py-1.5 text-xs bg-violet-50 text-violet-600 hover:bg-violet-100 dark:bg-violet-500/10 dark:text-violet-400 rounded">Detail</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endforeach ?>

    <?php if (! $asetsByGudang): ?>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center shadow-sm">
            <p class="text-gray-500 dark:text-gray-400">Belum ada aset material. Silakan tambah aset baru.</p>
        </div>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
