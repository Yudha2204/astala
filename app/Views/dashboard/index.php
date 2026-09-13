<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
function astala_format_datetime(?string $date): string
{
    return $date ? date('d M Y H:i', strtotime($date)) : '-';
}

function astala_status_label(string $status): string
{
    return [
        'request' => 'Request',
        'waiting' => 'Menunggu',
        'pickup' => 'Siap Pickup',
        'confirmation' => 'Konfirmasi',
        'done' => 'Selesai',
        'rejected' => 'Ditolak',
    ][$status] ?? ucfirst($status);
}

function astala_stat_card(string $label, int|string|null $value, string $tone, string $iconPath): string
{
    $tones = [
        'blue' => ['border-blue-200 dark:border-blue-500/30', 'bg-blue-100 dark:bg-blue-500/10', 'text-blue-600 dark:text-blue-400'],
        'green' => ['border-green-200 dark:border-green-500/30', 'bg-green-100 dark:bg-green-500/10', 'text-green-600 dark:text-green-400'],
        'yellow' => ['border-yellow-200 dark:border-yellow-500/30', 'bg-yellow-100 dark:bg-yellow-500/10', 'text-yellow-600 dark:text-yellow-400'],
        'teal' => ['border-teal-200 dark:border-teal-500/30', 'bg-teal-100 dark:bg-teal-500/10', 'text-teal-600 dark:text-teal-400'],
        'red' => ['border-red-200 dark:border-red-500/30', 'bg-red-100 dark:bg-red-500/10', 'text-red-600 dark:text-red-400'],
        'cyan' => ['border-cyan-200 dark:border-cyan-500/30', 'bg-cyan-100 dark:bg-cyan-500/10', 'text-cyan-600 dark:text-cyan-400'],
        'orange' => ['border-orange-200 dark:border-orange-500/30', 'bg-orange-100 dark:bg-orange-500/10', 'text-orange-600 dark:text-orange-400'],
        'amber' => ['border-amber-200 dark:border-amber-500/30', 'bg-amber-100 dark:bg-amber-500/10', 'text-amber-600 dark:text-amber-400'],
        'emerald' => ['border-emerald-200 dark:border-emerald-500/30', 'bg-emerald-100 dark:bg-emerald-500/10', 'text-emerald-600 dark:text-emerald-400'],
    ];
    [$border, $bg, $text] = $tones[$tone] ?? $tones['blue'];

    return '<div class="bg-white dark:bg-gray-800 border ' . $border . ' rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">'
        . '<div class="flex items-center gap-4">'
        . '<div class="w-12 h-12 ' . $bg . ' rounded-xl flex items-center justify-center">'
        . '<svg class="w-6 h-6 ' . $text . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . $iconPath . '</svg>'
        . '</div><div><p class="text-2xl font-bold text-gray-900 dark:text-white">' . esc((string) ($value ?? 0)) . '</p>'
        . '<p class="text-sm text-gray-500 dark:text-gray-400">' . esc($label) . '</p></div></div></div>';
}

$icons = [
    'box' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>',
    'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
    'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path>',
    'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>',
    'archive' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>',
    'x' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    'plus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>',
];
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                <?= esc($dashboardType === 'manager' ? 'Dashboard Manager' : ($dashboardType === 'mitra' ? 'Dashboard Mitra' : 'Dashboard')) ?>
            </h1>
            <p class="text-gray-500 dark:text-gray-400">Selamat datang kembali, <?= esc($user['nama'] ?? 'User') ?>!</p>
        </div>
    </div>

    <?php if (in_array($dashboardType, ['admin', 'manager'], true)): ?>
        <div class="pt-2">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $icons['clipboard'] ?></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Peminjaman Barang</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Statistik peminjaman barang oleh karyawan</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?= astala_stat_card('Total Barang', $stats['totalBarang'] ?? 0, 'blue', $icons['box']) ?>
            <?= astala_stat_card('Tersedia', $stats['barangTersedia'] ?? 0, 'green', $icons['check']) ?>
            <?= astala_stat_card('Kondisi Baik', $stats['barangBaik'] ?? 0, 'teal', $icons['shield']) ?>
            <?= astala_stat_card('Rusak', $stats['barangRusak'] ?? 0, 'red', $icons['warning']) ?>

            <?= astala_stat_card('Peminjaman Aktif', $stats['activePeminjaman'] ?? 0, 'blue', $icons['clipboard']) ?>
            <?php if ($dashboardType === 'admin'): ?>
                <?= astala_stat_card('Total User', $stats['totalUser'] ?? 0, 'cyan', $icons['users']) ?>
            <?php endif ?>
            <?= astala_stat_card('Terlambat', count($overdueLoans ?? []), 'orange', $icons['warning']) ?>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik Peminjaman</h2>
                <select id="loanPeriod" class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-white text-sm">
                    <option value="daily">Harian</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div class="p-5">
                <div class="relative w-full h-[250px]"><canvas id="loanChart"></canvas></div>
            </div>
        </div>



        <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-500/50 rounded-xl shadow-sm">
            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-red-600 dark:text-red-400">Barang Terlambat</h2>
                <a href="<?= site_url('admin/loans?is_late=true') ?>" class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Lihat Semua</a>
            </div>
            <div class="p-5">
                <?php if ($overdueLoans): ?>
                    <div class="space-y-4">
                        <?php foreach ($overdueLoans as $loan): ?>
                            <div class="flex items-center gap-4 p-4 bg-red-50 border border-red-100 dark:bg-red-500/5 dark:border-red-500/20 rounded-lg">
                                <div class="w-10 h-10 bg-red-100 dark:bg-red-500/10 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $icons['warning'] ?></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= esc($loan['nama_barang'] ?? '-') ?></p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">Dipinjam oleh: <?= esc($loan['user_nama'] ?? '-') ?></p>
                                    <p class="text-xs text-red-600 dark:text-red-400">Deadline: <?= esc(astala_format_datetime($loan['tanggal_kembali_rencana'] ?? null)) ?></p>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada barang terlambat.</p>
                <?php endif ?>
            </div>
        </div>
    <?php elseif ($dashboardType === 'mitra'): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?= astala_stat_card('Total Pengambilan', $stats['total'] ?? 0, 'blue', $icons['clipboard']) ?>
            <?= astala_stat_card('Menunggu Proses', $stats['pending'] ?? 0, 'yellow', $icons['clock']) ?>
            <?= astala_stat_card('Selesai', $stats['completed'] ?? 0, 'green', $icons['check']) ?>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $icons['archive'] ?></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold">Pengambilan Aset</h2>
                        <p class="text-white/80 text-sm">Request pengambilan kabel, ODP, atau Closure</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="<?= site_url('pengambilan/request') ?>" class="px-5 py-2.5 bg-white text-blue-600 rounded-lg font-medium hover:bg-gray-100 transition-colors">+ Request Baru</a>
                    <a href="<?= site_url('pengambilan') ?>" class="px-5 py-2.5 bg-white/20 text-white rounded-lg font-medium hover:bg-white/30 transition-colors">Lihat Semua</a>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pengambilan Terakhir</h2>
            </div>
            <?php if ($recentPickups): ?>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($recentPickups as $pickup): ?>
                        <a href="<?= site_url('pengambilan/detail/' . $pickup['id']) ?>" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-xs">#<?= esc($pickup['id']) ?> - <?= esc($pickup['gudang_nama'] ?? 'Unknown') ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?= esc($pickup['item_count'] ?? 0) ?> item - <?= esc(astala_format_datetime($pickup['tanggal_request'] ?? $pickup['created_at'] ?? null)) ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"><?= esc(astala_status_label($pickup['status'])) ?></span>
                        </a>
                    <?php endforeach ?>
                </div>
            <?php else: ?>
                <div class="p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Belum ada pengambilan. Mulai dengan membuat request baru.</p>
                    <a href="<?= site_url('pengambilan/request') ?>" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Request Pengambilan</a>
                </div>
            <?php endif ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?= astala_stat_card('Total Peminjaman', $stats['totalPeminjaman'] ?? 0, 'blue', $icons['clipboard']) ?>
            <?= astala_stat_card('Sudah Dikembalikan', $stats['selesaiPeminjaman'] ?? 0, 'green', $icons['check']) ?>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="<?= site_url('peminjaman/items') ?>" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 hover:border-blue-500 dark:hover:border-blue-500 transition-colors group shadow-sm">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/10 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 dark:group-hover:bg-blue-500/20 transition-colors">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $icons['plus'] ?></svg>
                </div>
                <p class="font-medium text-gray-900 dark:text-white">Pinjam Barang</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Mulai peminjaman baru</p>
            </a>

            <a href="<?= site_url('peminjaman/history') ?>" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 hover:border-blue-500 dark:hover:border-blue-500 transition-colors group shadow-sm">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/10 rounded-xl flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $icons['clock'] ?></svg>
                </div>
                <p class="font-medium text-gray-900 dark:text-white">Riwayat</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Lihat semua riwayat</p>
            </a>
            <a href="<?= site_url('barang') ?>" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 hover:border-blue-500 dark:hover:border-blue-500 transition-colors group shadow-sm">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-500/10 rounded-xl flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $icons['box'] ?></svg>
                </div>
                <p class="font-medium text-gray-900 dark:text-white">Inventaris</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Lihat daftar barang</p>
            </a>
        </div>


    <?php endif ?>
</div>

<script>
function chartColor(status) {
    if (status === 'done') return ['rgba(16, 185, 129, 0.2)', 'rgba(16, 185, 129, 1)'];
    if (status === 'rejected') return ['rgba(239, 68, 68, 0.2)', 'rgba(239, 68, 68, 1)'];
    return ['rgba(59, 130, 246, 0.2)', 'rgba(59, 130, 246, 1)'];
}

async function loadChart(canvasId, url, status = 'all') {
    const canvas = document.getElementById(canvasId);
    if (!canvas || typeof Chart === 'undefined') return;
    const response = await fetch(url);
    const result = await response.json();
    if (!result.success) return;

    if (canvasId === 'pickupChart') {
        const badge = document.getElementById('pengambilanTotalBadge');
        if (badge) {
            const label = result.status === 'done' ? ' Selesai' : result.status === 'rejected' ? ' Ditolak' : '';
            badge.textContent = `Total${label}: ${result.total}`;
        }
    }

    const [backgroundColor, borderColor] = chartColor(status);
    const previous = Chart.getChart(canvas);
    if (previous) previous.destroy();

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: result.data.map(item => item.x),
            datasets: [{
                label: canvasId === 'pickupChart' ? 'Jumlah Pengambilan' : 'Jumlah Peminjaman',
                data: result.data.map(item => item.y),
                fill: true,
                backgroundColor,
                borderColor,
                borderWidth: 2,
                tension: 0.4,
                pointBackgroundColor: borderColor,
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'nearest', axis: 'x', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(75, 85, 99, 0.25)' }, ticks: { color: '#9CA3AF', maxTicksLimit: 8 } },
                y: { beginAtZero: true, grid: { color: 'rgba(75, 85, 99, 0.25)' }, ticks: { precision: 0, color: '#9CA3AF' } }
            }
        }
    });
}

const loanPeriod = document.getElementById('loanPeriod');
const pickupPeriod = document.getElementById('pickupPeriod');
const pickupStatus = document.getElementById('pickupStatus');

function refreshLoanChart() {
    if (loanPeriod) loadChart('loanChart', `/dashboard/api/chart/loans?period=${loanPeriod.value}`);
}

loanPeriod?.addEventListener('change', refreshLoanChart);
refreshLoanChart();
</script>
<?= $this->endSection() ?>
