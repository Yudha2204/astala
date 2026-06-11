<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $currentTheme = $user['theme'] ?? 'system'; ?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atur preferensi tampilan aplikasi Anda.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Tampilan</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button type="button" onclick="setTheme('light')" class="group relative flex flex-col items-center p-4 border-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 focus:outline-none theme-btn" data-theme="light">
                    <div class="w-full aspect-video bg-gray-100 rounded-lg mb-4 overflow-hidden border border-gray-200"><div class="w-full h-full p-2"><div class="w-3/4 h-2 bg-white rounded shadow-sm mb-2"></div><div class="w-1/2 h-2 bg-white rounded shadow-sm"></div></div></div>
                    <span class="font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Terang</span>
                    <div class="absolute top-4 right-4 w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center check-icon"><div class="w-2.5 h-2.5 bg-blue-600 rounded-full hidden"></div></div>
                </button>

                <button type="button" onclick="setTheme('dark')" class="group relative flex flex-col items-center p-4 border-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 focus:outline-none theme-btn" data-theme="dark">
                    <div class="w-full aspect-video bg-gray-900 rounded-lg mb-4 overflow-hidden border border-gray-800"><div class="w-full h-full p-2"><div class="w-3/4 h-2 bg-gray-800 rounded mb-2"></div><div class="w-1/2 h-2 bg-gray-800 rounded"></div></div></div>
                    <span class="font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Gelap</span>
                    <div class="absolute top-4 right-4 w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center check-icon"><div class="w-2.5 h-2.5 bg-blue-600 rounded-full hidden"></div></div>
                </button>

                <button type="button" onclick="setTheme('system')" class="group relative flex flex-col items-center p-4 border-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 focus:outline-none theme-btn" data-theme="system">
                    <div class="w-full aspect-video bg-gradient-to-r from-gray-100 to-gray-900 rounded-lg mb-4 overflow-hidden border border-gray-200 dark:border-gray-700"><div class="w-full h-full p-2 flex"><div class="w-1/2 h-full border-r border-gray-300/20"></div></div></div>
                    <span class="font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Sistem</span>
                    <div class="absolute top-4 right-4 w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-full flex items-center justify-center check-icon"><div class="w-2.5 h-2.5 bg-blue-600 rounded-full hidden"></div></div>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function updateActiveButton(theme) {
    document.querySelectorAll('.theme-btn').forEach(btn => {
        btn.classList.remove('border-blue-600', 'bg-blue-50', 'dark:bg-blue-900/10');
        btn.classList.add('border-gray-200', 'dark:border-gray-700');
        btn.querySelector('.check-icon').classList.remove('border-blue-600');
        btn.querySelector('.check-icon').classList.add('border-gray-300', 'dark:border-gray-600');
        btn.querySelector('.check-icon div').classList.add('hidden');
    });

    const activeBtn = document.querySelector(`.theme-btn[data-theme="${theme}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('border-gray-200', 'dark:border-gray-700');
        activeBtn.classList.add('border-blue-600', 'bg-blue-50', 'dark:bg-blue-900/10');
        activeBtn.querySelector('.check-icon').classList.remove('border-gray-300', 'dark:border-gray-600');
        activeBtn.querySelector('.check-icon').classList.add('border-blue-600');
        activeBtn.querySelector('.check-icon div').classList.remove('hidden');
    }
}

function applyTheme(theme) {
    if (theme === 'system') {
        localStorage.removeItem('theme');
        document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
    } else {
        localStorage.theme = theme;
        document.documentElement.classList.toggle('dark', theme === 'dark');
    }
}

function setTheme(theme) {
    applyTheme(theme);
    updateActiveButton(theme);
    fetch('<?= site_url('profile/settings/theme') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({theme})
    }).catch(() => {});
}

const currentTheme = '<?= esc($currentTheme, 'js') ?>' || 'system';
applyTheme(currentTheme);
updateActiveButton(currentTheme);
</script>

<?= $this->endSection() ?>
