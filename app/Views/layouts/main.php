<?php
$user = $user ?? session('user');
$role = $user['role'] ?? 'guest';
$currentPath = '/' . ltrim(current_url(true)->getPath(), '/');
$canEdit = $role === 'admin' || (in_array($role, ['manager', 'karyawan'], true) && ($user['sub_user'] ?? '') === 'editor');
$simpellUrl = rtrim((string) env('SIMPELL_URL', ''), '/');
$profilePhotoUrl = static function (?string $path): ?string {
    if (! $path) {
        return null;
    }

    return str_starts_with($path, '/')
        ? base_url(ltrim($path, '/'))
        : base_url('uploads/profiles/' . $path);
};

$navSections = [
    [
        'label' => null,
        'items' => [
            ['label' => 'Dashboard', 'url' => '/dashboard', 'show' => true, 'icon' => 'home'],
        ],
    ],
    [
        'label' => 'Peminjaman',
        'items' => [
            ['label' => 'Daftar Barang', 'url' => '/barang', 'show' => $role !== 'mitra', 'icon' => 'box'],
            ['label' => 'Pinjam Barang', 'url' => '/peminjaman/items', 'show' => $role !== 'mitra', 'icon' => 'plus'],
            ['label' => 'Sedang Dipinjam', 'url' => '/peminjaman/current', 'show' => $role !== 'mitra', 'icon' => 'clipboard'],
            ['label' => 'Riwayat Peminjaman', 'url' => '/peminjaman/history', 'show' => $role !== 'mitra', 'icon' => 'clock'],
        ],
    ],
    [
        'label' => null,
        'items' => [
            ['label' => 'Pengambilan Aset', 'url' => '/pengambilan', 'show' => $role === 'mitra', 'icon' => 'archive'],
        ],
    ],
    [
        'label' => 'Keluar Masuk Aset',
        'items' => [
            ['label' => 'Gudang', 'url' => '/admin/gudang', 'show' => $canEdit, 'icon' => 'building'],
            ['label' => 'Aset Material', 'url' => '/admin/aset-material', 'show' => in_array($role, ['admin', 'manager', 'karyawan'], true), 'icon' => 'box'],
            ['label' => 'Pengambilan Aset', 'url' => '/pengambilan/admin', 'show' => in_array($role, ['admin', 'manager', 'karyawan'], true), 'icon' => 'check'],
        ],
    ],
    [
        'label' => $role === 'manager' ? 'Manager' : 'Admin',
        'items' => [
            ['label' => 'Semua Peminjaman', 'url' => '/admin/loans', 'show' => in_array($role, ['admin', 'manager'], true), 'icon' => 'chart'],
            ['label' => 'Log Aktivitas', 'url' => '/admin/logs', 'show' => $role === 'admin', 'icon' => 'document'],
        ],
    ],
];

function astala_icon(string $name): string
{
    $paths = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>',
        'box' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>',
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>',
        'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'archive' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
        'document' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
    ];

    return '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . ($paths[$name] ?? $paths['box']) . '</svg>';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'ASTALA') ?></title>
    <script>
        (function () {
            const serverTheme = '<?= esc($user['theme'] ?? '', 'js') ?>';
            const localTheme = localStorage.getItem('theme');
            const theme = serverTheme || localTheme || 'system';
            if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } }
        };
    </script>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased">
    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
        <div class="flex items-center justify-between">
            <a href="<?= site_url('dashboard') ?>" class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
                    <img src="<?= base_url('img/logo_astala.png') ?>" alt="Logo" class="w-full h-full object-contain">
                </span>
                <span class="text-gray-900 dark:text-white font-semibold">ASTALA</span>
            </a>
            <button type="button" onclick="toggleSidebar()" class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="hidden fixed inset-0 bg-gray-900/50 dark:bg-black/50 z-[55] lg:hidden"></div>

    <aside id="sidebar" class="fixed top-0 left-0 bottom-0 z-[60] w-64 bg-white dark:bg-gray-800 border-e border-gray-200 dark:border-gray-700 transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div id="sidebar-header" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between transition-all duration-300">
                <a href="<?= site_url('dashboard') ?>" class="flex items-center gap-3 overflow-hidden">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 overflow-hidden">
                        <img src="<?= base_url('img/logo_astala.png') ?>" alt="Logo" class="w-full h-full object-contain">
                    </span>
                    <span class="sidebar-text transition-opacity duration-300 whitespace-nowrap">
                        <span class="block text-gray-900 dark:text-white font-bold text-lg">ASTALA</span>
                        <span class="block text-gray-500 dark:text-gray-400 text-xs">PT Lintasarta</span>
                    </span>
                </a>
                <button onclick="toggleDesktopSidebar()" class="hidden lg:flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg id="sidebar-toggle-icon" class="w-5 h-5 transition-transform duration-300 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 overflow-y-auto overflow-x-hidden">
                <ul class="space-y-1.5">
                    <?php foreach ($navSections as $section): ?>
                        <?php
                        $visibleItems = array_filter($section['items'], static fn ($item) => $item['show']);
                        if (! $visibleItems) {
                            continue;
                        }
                        ?>
                        <?php if ($section['label']): ?>
                            <li class="pt-4 pb-2 sidebar-text whitespace-nowrap">
                                <span class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider"><?= esc($section['label']) ?></span>
                            </li>
                        <?php endif ?>
                        <?php foreach ($visibleItems as $item): ?>
                            <?php
                            $isActive = str_starts_with($currentPath, $item['url']);
                            $classes = $isActive
                                ? 'bg-blue-50 text-blue-600 dark:text-blue-400'
                                : 'text-gray-700 hover:bg-gray-100/50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700/50 dark:hover:text-white';
                            ?>
                            <li>
                                <a href="<?= site_url(ltrim($item['url'], '/')) ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $classes ?>" style="<?= $isActive ? 'background-color: rgba(37, 99, 235, 0.1);' : '' ?>">
                                    <?= astala_icon($item['icon']) ?>
                                    <span class="sidebar-text whitespace-nowrap"><?= esc($item['label']) ?></span>
                                </a>
                            </li>
                        <?php endforeach ?>
                    <?php endforeach ?>
                </ul>
            </nav>

            <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3 px-3 py-2">
                    <?php if ($profilePhotoUrl($user['foto'] ?? null)): ?>
                        <img src="<?= esc($profilePhotoUrl($user['foto'] ?? null)) ?>" alt="<?= esc($user['nama'] ?? 'User') ?>" class="w-10 h-10 rounded-full object-cover border-2 border-blue-500">
                    <?php else: ?>
                        <span class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold"><?= esc(strtoupper(substr($user['nama'] ?? 'U', 0, 1))) ?></span>
                        </span>
                    <?php endif ?>
                    <span class="sidebar-text flex-1 min-w-0 transition-opacity duration-300">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white truncate"><?= esc($user['nama'] ?? 'User') ?></span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 truncate capitalize"><?= esc(str_replace('_', ' ', $role)) ?></span>
                    </span>
                </div>
            </div>
        </div>
    </aside>

    <div id="main-content" class="lg:pl-64 pt-14 lg:pt-0 transition-all duration-300">
        <header class="sticky lg:top-0 top-14 z-30 w-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-end px-3 sm:px-4 lg:px-6 h-14 w-full gap-2">
                <div class="hs-dropdown relative inline-flex">
                    <button type="button" class="relative p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span id="notif-badge" class="hidden absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <div class="hs-dropdown-menu hidden absolute right-0 top-full mt-2 transition-opacity duration-200 opacity-0 w-80 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Notifikasi</h3>
                            <button onclick="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Tandai semua dibaca</button>
                        </div>
                        <div id="notification-list" class="max-h-80 overflow-y-auto">
                            <p class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center">Tidak ada notifikasi</p>
                        </div>
                        <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                            <a href="<?= site_url('notifications') ?>" class="block text-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Lihat Semua</a>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 p-1.5 px-3 rounded-lg cursor-default select-none">
                    <span class="w-8 h-8 relative">
                        <?php if ($profilePhotoUrl($user['foto'] ?? null)): ?>
                            <img src="<?= esc($profilePhotoUrl($user['foto'] ?? null)) ?>" alt="Profile" class="w-full h-full rounded-full object-cover border border-gray-200 dark:border-gray-600">
                        <?php else: ?>
                            <span class="w-full h-full rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs ring-2 ring-white dark:ring-gray-800">
                                <?= esc(strtoupper(substr($user['nama'] ?? 'U', 0, 1))) ?>
                            </span>
                        <?php endif ?>
                    </span>
                    <span class="hidden sm:inline-block text-sm font-medium text-gray-700 dark:text-gray-200"><?= esc($user['nama'] ?? 'User') ?></span>
                </div>

                <div class="h-6 w-[1px] bg-gray-200 dark:bg-gray-700 mx-1"></div>
                <a href="<?= $simpellUrl ? esc($simpellUrl . '/portal') : site_url('auth/logout') ?>" title="<?= $simpellUrl ? 'Ke Portal' : 'Logout' ?>" class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </a>
            </div>
        </header>

        <main class="pt-0 pb-6 px-4 sm:px-6 lg:px-8">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 bg-teal-500/10 border border-teal-500 text-teal-500 rounded-lg p-4" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 bg-red-500/10 border border-red-500 text-red-500 rounded-lg p-4" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <script src="<?= base_url('js/html5-qrcode.min.js') ?>"></script>
    <script src="<?= base_url('js/main.js') ?>"></script>
    <script src="<?= base_url('js/scanner.js') ?>"></script>
    <div id="toast-container" class="fixed top-20 right-4 z-[100] flex flex-col gap-3 max-w-sm"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.hs-dropdown').forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const menu = dropdown.querySelector('.hs-dropdown-menu');
                if (!button || !menu) return;
                button.addEventListener('click', event => {
                    event.stopPropagation();
                    const hidden = menu.classList.contains('hidden');
                    document.querySelectorAll('.hs-dropdown-menu').forEach(item => {
                        item.classList.add('hidden');
                        item.classList.remove('opacity-100');
                    });
                    if (hidden) {
                        menu.classList.remove('hidden');
                        setTimeout(() => menu.classList.add('opacity-100'), 10);
                    }
                });
                document.addEventListener('click', event => {
                    if (!dropdown.contains(event.target)) {
                        menu.classList.add('hidden');
                        menu.classList.remove('opacity-100');
                    }
                });
            });
        });
    </script>
</body>
</html>
