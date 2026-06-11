// Main JavaScript for ASTALA

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Preline
    if (typeof HSStaticMethods !== 'undefined') {
        HSStaticMethods.autoInit();
    }

    // Load notifications count and list
    loadNotificationCount();
    loadNotifications();

    // Poll for new notifications every 30 seconds
    setInterval(() => {
        loadNotificationCount();
    }, 30000);

    // Auto-hide flash messages
    setTimeout(() => {
        document.querySelectorAll('[role="alert"]').forEach(el => {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.5s';
            setTimeout(() => el.remove(), 500);
        });
    }, 5000);

    // Easter Egg Shortcut: Ctrl + A + P + D
    let keysPressed = [];
    document.addEventListener('keydown', (e) => {
        if (!window.location.pathname.includes('/dashboard')) return;

        if (e.ctrlKey) {
            const key = e.key.toLowerCase();
            if (['a', 'p', 'd'].includes(key)) {
                e.preventDefault(); // Stop Ctrl+P print dialog, Ctrl+D bookmark, etc.
                if (!keysPressed.includes(key)) {
                    keysPressed.push(key);
                }

                if (keysPressed.length === 3) {
                    const combo = keysPressed.sort().join('');
                    if (combo === 'adp') { // a, d, p
                        showDeveloperModal();
                        keysPressed = [];
                    }
                }
            }
        } else {
            keysPressed = [];
        }
    });

    // Reset keys on blur or keyup of non-ctrl
    window.addEventListener('keyup', (e) => {
        if (!e.ctrlKey) keysPressed = [];
    });
});

function showDeveloperModal() {
    // Prevent duplicate modals
    if (document.getElementById('developer-attribution-modal')) return;

    const modal = document.createElement('div');
    modal.id = 'developer-attribution-modal';
    modal.className = 'fixed inset-0 z-[200] flex items-center justify-center bg-black/80 backdrop-blur-sm transition-opacity duration-300';
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-sm w-full mx-4 shadow-2xl transform transition-all duration-300 scale-95 opacity-0 border border-gray-200 dark:border-gray-700">
            <div class="text-center">
                <div class="w-24 h-24 mx-auto mb-2 flex items-center justify-center">
                    <img src="/logo_astala.png" alt="ASTALA Logo" class="w-full h-full object-contain">
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">ASTALA</h3>
                <div class="space-y-1">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Website developed by</p>
                    <p class="text-lg font-bold text-blue-500">ANDRE PUTRA DEWANSYAH</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="mt-6 w-full py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors border border-gray-200 dark:border-gray-600">
                    Close
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    // Trigger animation
    requestAnimationFrame(() => {
        const inner = modal.querySelector('div');
        inner.classList.remove('scale-95', 'opacity-0');
        inner.classList.add('scale-100', 'opacity-100');
    });
}

// Notification Sound - Using custom WAV files
let lastNotificationCount = -1;
let lastNotificationId = null;

// Sound mapping based on notification type and title
function getSoundFile(type, title) {
    // Map notifications to sound files based on user requirements:
    // normal.wav: Pengingat (1,2), Stok Menipis (10), Role Diperbarui (12), Password Direset (13)
    // negative.wav: Terlambat (3,4), Ditolak (7)
    // positive.wav: Selesai (9), Selamat Datang (11)
    // correct.wav: Disetujui (6)
    // confirmation.wav: Request Baru (5), Menunggu Konfirmasi (8)

    if (title) {
        // Negative sounds (danger)
        if (title.includes('TERLAMBAT') || title.includes('Ditolak')) {
            return '/sounds/negative.wav';
        }
        // Correct sound (approval)
        if (title.includes('Disetujui')) {
            return '/sounds/correct.wav';
        }
        // Positive sounds (success)
        if (title.includes('Selesai') || title.includes('Selamat Datang')) {
            return '/sounds/positive.wav';
        }
        // Confirmation sounds (new requests, waiting)
        if (title.includes('Request Pengambilan Baru') || title.includes('Menunggu Konfirmasi')) {
            return '/sounds/confirmation.wav';
        }
        // Normal sounds (reminders, info)
        if (title.includes('Pengingat') || title.includes('Stok Menipis') ||
            title.includes('Role Diperbarui') || title.includes('Password Direset')) {
            return '/sounds/normal.wav';
        }
    }

    // Fallback based on type
    switch (type) {
        case 'danger':
            return '/sounds/negative.wav';
        case 'success':
            return '/sounds/positive.wav';
        case 'warning':
            return '/sounds/normal.wav';
        case 'info':
        default:
            return '/sounds/confirmation.wav';
    }
}

function playNotificationSound(type = 'info', title = '') {
    try {
        const soundFile = getSoundFile(type, title);
        const audio = new Audio(soundFile);
        audio.volume = 0.5; // 50% volume
        audio.play().catch(e => console.log('Audio autoplay blocked:', e));
    } catch (e) {
        console.log('Audio not supported:', e);
    }
}

async function loadNotificationCount() {
    try {
        const response = await fetch('/notifications/api/unread-count');
        const data = await response.json();
        const badge = document.getElementById('notif-badge');
        if (badge) {
            if (data.count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            // Check for new notifications by comparing with last known count
            if (lastNotificationCount !== -1 && data.count > lastNotificationCount) {
                // New notifications detected! Get latest to determine sound type
                loadNotifications();
                showLatestNotificationToast();
            }

            lastNotificationCount = data.count;
        }
    } catch (error) {
        console.error('Failed to load notification count:', error);
    }
}

async function showLatestNotificationToast() {
    try {
        const response = await fetch('/notifications/api/recent');
        if (!response.ok) return;
        const notifications = await response.json();

        if (notifications.length > 0) {
            const latest = notifications[0];
            // Play sound with correct type based on notification
            playNotificationSound(latest.tipe, latest.judul);
            showNotificationToast(latest.judul, latest.pesan, latest.tipe, latest.link);
        }
    } catch (error) {
        console.error('Failed to show notification toast:', error);
    }
}

function showNotificationToast(title, message, type = 'info', link = null) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const typeStyles = {
        info: 'border-blue-500 bg-blue-50 dark:bg-blue-500/10',
        success: 'border-green-500 bg-green-50 dark:bg-green-500/10',
        warning: 'border-yellow-500 bg-yellow-50 dark:bg-yellow-500/10',
        danger: 'border-red-500 bg-red-50 dark:bg-red-500/10'
    };

    const iconStyles = {
        info: 'text-blue-500',
        success: 'text-green-500',
        warning: 'text-yellow-500',
        danger: 'text-red-500'
    };

    const icons = {
        info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        danger: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
    };

    const toast = document.createElement('div');
    toast.className = `transform transition-all duration-300 ease-out translate-x-full opacity-0 p-4 rounded-lg border-l-4 shadow-lg bg-white dark:bg-gray-800 ${typeStyles[type] || typeStyles.info}`;
    toast.innerHTML = `
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 ${iconStyles[type] || iconStyles.info}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icons[type] || icons.info}
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">${title}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">${message}</p>
                ${link ? `<a href="${link}" class="text-xs text-violet-600 dark:text-violet-400 hover:underline mt-2 inline-block">Lihat Detail →</a>` : ''}
            </div>
            <button onclick="this.closest('.transform').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
        toast.classList.add('translate-x-0', 'opacity-100');
    });

    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

async function loadNotifications() {
    try {
        const response = await fetch('/notifications/api/recent');
        if (!response.ok) return;
        const notifications = await response.json();
        const list = document.getElementById('notification-list');

        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = '<p class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center">Tidak ada notifikasi</p>';
            return;
        }

        list.innerHTML = notifications.map(n => `
            <a href="${n.link || '/notifications'}" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 ${!n.is_read ? 'bg-violet-50 dark:bg-violet-500/10' : ''}" onclick="markAsRead(${n.id})">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full ${n.tipe === 'danger' ? 'bg-red-500' : n.tipe === 'warning' ? 'bg-yellow-500' : n.tipe === 'success' ? 'bg-green-500' : 'bg-blue-500'}"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${n.judul}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">${n.pesan}</p>
                    </div>
                </div>
            </a>
        `).join('');
    } catch (error) {
        console.error('Failed to load notifications:', error);
    }
}

async function markAsRead(id) {
    try {
        await fetch(`/notifications/mark-read/${id}`, { method: 'POST' });
    } catch (error) {
        console.error('Failed to mark as read:', error);
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('/notifications/mark-all-read', {
            method: 'POST'
        });
        if (response.ok) {
            window.location.reload();
        }
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
}

// Toast Functions
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Loading Functions
function showLoading(element) {
    const originalContent = element.innerHTML;
    element.dataset.originalContent = originalContent;
    element.innerHTML = '<span class="spinner"></span>';
    element.disabled = true;
}

function hideLoading(element) {
    element.innerHTML = element.dataset.originalContent;
    element.disabled = false;
}

// Form Validation
function validateForm(form) {
    let isValid = true;
    const requiredInputs = form.querySelectorAll('[required]');

    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
            input.classList.remove('border-gray-700');
        } else {
            input.classList.remove('border-red-500');
            input.classList.add('border-gray-700');
        }
    });

    return isValid;
}

// Confirm Delete
function confirmDelete(message = 'Yakin ingin menghapus?') {
    return confirm(message);
}

// Format Date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Debounce Function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Copy to Clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Berhasil disalin!', 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showToast('Gagal menyalin', 'error');
    });
}

// Image Preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Mobile Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');

    if (sidebar) {
        const isOpen = !sidebar.classList.contains('-translate-x-full');

        if (isOpen) {
            // Close sidebar
            sidebar.classList.add('-translate-x-full');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            // Open sidebar
            sidebar.classList.remove('-translate-x-full');
            if (backdrop) backdrop.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }
}

// Alias for backward compatibility
function toggleMobileMenu() {
    toggleSidebar();
}

// Desktop Sidebar Toggle
function toggleDesktopSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleIcon = document.getElementById('sidebar-toggle-icon');
    const sidebarHeader = document.getElementById('sidebar-header');
    const texts = document.querySelectorAll('.sidebar-text');

    if (!sidebar || !mainContent) return;

    // Check current state
    const isValuesCollapsed = sidebar.classList.contains('w-24');

    if (isValuesCollapsed) {
        // EXPAND
        sidebar.classList.remove('w-24');
        sidebar.classList.add('w-64');

        mainContent.classList.remove('lg:pl-24');
        mainContent.classList.add('lg:pl-64');

        // Reset Header Layout (Wide)
        if (sidebarHeader) {
            sidebarHeader.classList.remove('px-2', 'justify-center', 'gap-1');
            sidebarHeader.classList.add('px-6', 'justify-between');
        }

        texts.forEach(el => {
            el.classList.remove('opacity-0', 'pointer-events-none', 'hidden');
            // Small delay for fade in
            setTimeout(() => el.classList.remove('opacity-0'), 50);
        });

        if (toggleIcon) toggleIcon.style.transform = 'rotate(0deg)';
        localStorage.setItem('sidebarState', 'expanded');
    } else {
        // COLLAPSE
        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-24');

        mainContent.classList.remove('lg:pl-64');
        mainContent.classList.add('lg:pl-24');

        // Adjust Header Layout (Compact)
        if (sidebarHeader) {
            sidebarHeader.classList.remove('px-6', 'justify-between');
            // Use gap-1 to ensure fit if w-24 is still tight, but w-24 (96px) should fit w-10+w-8
            sidebarHeader.classList.add('px-2', 'justify-center', 'gap-1');
        }

        texts.forEach(el => {
            el.classList.add('opacity-0', 'pointer-events-none');
            // Hide after transition
            setTimeout(() => el.classList.add('hidden'), 300);
        });

        if (toggleIcon) toggleIcon.style.transform = 'rotate(180deg)';
        localStorage.setItem('sidebarState', 'collapsed');
    }
}

// Initialize Sidebar State
document.addEventListener('DOMContentLoaded', () => {
    const state = localStorage.getItem('sidebarState');
    if (state === 'collapsed') {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleIcon = document.getElementById('sidebar-toggle-icon');
        const sidebarHeader = document.getElementById('sidebar-header');
        const texts = document.querySelectorAll('.sidebar-text');

        if (sidebar && mainContent) {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-24');
            mainContent.classList.remove('lg:pl-64');
            mainContent.classList.add('lg:pl-24');

            // Apply Collapsed Header Layout
            if (sidebarHeader) {
                sidebarHeader.classList.remove('px-6', 'justify-between');
                sidebarHeader.classList.add('px-2', 'justify-center', 'gap-1');
            }

            texts.forEach(el => {
                el.classList.add('opacity-0', 'pointer-events-none', 'hidden');
            });

            if (toggleIcon) toggleIcon.style.transform = 'rotate(180deg)';
        }
    }
});

console.log('Main.js loaded');
