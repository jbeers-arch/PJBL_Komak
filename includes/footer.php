        </main>
    </div>

    <!-- Toast Notification Container (Sonner-Style Bottom-Right Anchor) -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col-reverse space-y-reverse space-y-2.5 pointer-events-none max-w-sm w-full"></div>

    <!-- Global Custom Confirmation Modal (Emil Kowalski Centered Dialog) -->
    <div id="global-confirm-modal" class="hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 opacity-0 transition-opacity duration-180 ease-out">
        <div id="global-confirm-card" class="bg-white dark:bg-[#11182c] rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200/80 dark:border-white/10 text-center relative overflow-hidden transform scale-[0.96] opacity-0 transition-all duration-200" style="transform-origin: center; transition-timing-function: cubic-bezier(0.23, 1, 0.32, 1);">
            <!-- Top Gradient Bar -->
            <div id="global-confirm-bar" class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-600"></div>

            <!-- Icon Header -->
            <div id="global-confirm-icon-box" class="w-12 h-12 mx-auto mb-3.5 rounded-2xl flex items-center justify-center text-xl shadow-xs transition-colors">
                <!-- SVG Icon inserted dynamically -->
            </div>

            <!-- Title & Message -->
            <h4 id="global-confirm-title" class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight mb-1.5">Konfirmasi Tindakan</h4>
            <div id="global-confirm-message" class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-5">Apakah Anda yakin ingin melanjutkan tindakan ini?</div>

            <!-- Action Buttons with Tactile Touch Feedback -->
            <div class="space-y-2">
                <button type="button" id="global-confirm-btn-ok" class="w-full py-2.5 px-4 rounded-2xl text-white text-xs font-bold shadow-sm transition-all transform active:scale-[0.97]">
                    Lanjutkan
                </button>
                <button type="button" id="global-confirm-btn-cancel" class="w-full py-2 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition-all active:scale-[0.97]">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/pos-offline.js"></script>
    <script>
        // Global Modern Confirmation Modal Function
        function showConfirmDialog({
            title = 'Konfirmasi Tindakan',
            message = 'Apakah Anda yakin ingin melanjutkan?',
            confirmText = 'Lanjutkan',
            cancelText = 'Batal',
            type = 'danger', // 'danger' | 'warning' | 'info' | 'success'
            icon = null
        } = {}) {
            return new Promise((resolve) => {
                const modal = document.getElementById('global-confirm-modal');
                const card = document.getElementById('global-confirm-card');
                const bar = document.getElementById('global-confirm-bar');
                const iconBox = document.getElementById('global-confirm-icon-box');
                const titleEl = document.getElementById('global-confirm-title');
                const msgEl = document.getElementById('global-confirm-message');
                const btnOk = document.getElementById('global-confirm-btn-ok');
                const btnCancel = document.getElementById('global-confirm-btn-cancel');

                if (!modal) {
                    resolve(window.confirm(message));
                    return;
                }

                titleEl.innerText = title;
                msgEl.innerHTML = message.replace(/\n/g, '<br>');
                btnOk.innerText = confirmText;
                btnCancel.innerText = cancelText;

                if (type === 'danger') {
                    bar.className = 'absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-600';
                    iconBox.className = 'w-12 h-12 mx-auto mb-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/40 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shadow-xs';
                    iconBox.innerHTML = icon === 'logout'
                        ? `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>`
                        : `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`;
                    btnOk.className = 'w-full py-2.5 px-4 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/20 transition-all transform hover:-translate-y-0.5';
                } else if (type === 'warning') {
                    bar.className = 'absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500';
                    iconBox.className = 'w-12 h-12 mx-auto mb-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shadow-xs';
                    iconBox.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
                    btnOk.className = 'w-full py-2.5 px-4 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm shadow-amber-600/20 transition-all transform hover:-translate-y-0.5';
                } else {
                    bar.className = 'absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-600';
                    iconBox.className = 'w-12 h-12 mx-auto mb-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shadow-xs';
                    iconBox.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
                    btnOk.className = 'w-full py-2.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm shadow-indigo-600/20 transition-all transform active:scale-[0.97]';
                }

                modal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    modal.classList.remove('opacity-0');
                    modal.classList.add('opacity-100');
                    card.classList.remove('scale-[0.96]', 'opacity-0');
                    card.classList.add('scale-100', 'opacity-100');
                });

                function cleanup(value) {
                    modal.classList.remove('opacity-100');
                    modal.classList.add('opacity-0');
                    card.classList.remove('scale-100', 'opacity-100');
                    card.classList.add('scale-[0.96]', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        btnOk.onclick = null;
                        btnCancel.onclick = null;
                        modal.onclick = null;
                        document.removeEventListener('keydown', handleKey);
                        resolve(value);
                    }, 140);
                }

                function handleKey(e) {
                    if (e.key === 'Escape') {
                        cleanup(false);
                    } else if (e.key === 'Enter') {
                        cleanup(true);
                    }
                }

                btnOk.onclick = () => cleanup(true);
                btnCancel.onclick = () => cleanup(false);
                modal.onclick = (e) => {
                    if (e.target === modal) cleanup(false);
                };
                document.addEventListener('keydown', handleKey);
            });
        }
        window.showConfirmDialog = showConfirmDialog;

        function updateTerminalThemeUI(theme) {
            const icon = document.getElementById('theme-icon');
            if (icon) {
                if (theme === 'light') {
                    icon.className = 'fa-solid fa-moon text-slate-700 text-sm';
                } else {
                    icon.className = 'fa-solid fa-sun text-amber-400 text-sm';
                }
            }
        }

        function toggleTheme() {
            const isLight = document.documentElement.classList.contains('light');
            if (isLight) {
                document.documentElement.classList.remove('light');
                document.documentElement.classList.add('dark');
                localStorage.setItem('kspm_theme', 'dark');
                updateTerminalThemeUI('dark');
                showToast('Beralih ke Dark Mode (Navy Edition)', 'info');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem('kspm_theme', 'light');
                updateTerminalThemeUI('light');
                showToast('Beralih ke Light Mode (Academic Edition)', 'success');
            }
        }

        // Initialize Theme UI on load
        document.addEventListener('DOMContentLoaded', () => {
            const isDark = document.documentElement.classList.contains('dark');
            updateTerminalThemeUI(isDark ? 'dark' : 'light');
        });

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            
            // Icon & color styling based on type
            let iconSvg = '';
            let styleClass = '';
            
            if (type === 'success') {
                styleClass = 'bg-slate-900/95 dark:bg-[#11182c]/95 text-white border-emerald-500/40 shadow-emerald-500/10';
                iconSvg = `<span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </span>`;
            } else if (type === 'error') {
                styleClass = 'bg-slate-900/95 dark:bg-[#11182c]/95 text-white border-rose-500/40 shadow-rose-500/10';
                iconSvg = `<span class="w-6 h-6 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </span>`;
            } else if (type === 'warning' || type === 'warn') {
                styleClass = 'bg-slate-900/95 dark:bg-[#11182c]/95 text-white border-amber-500/40 shadow-amber-500/10';
                iconSvg = `<span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>`;
            } else {
                styleClass = 'bg-slate-900/95 dark:bg-[#11182c]/95 text-white border-blue-500/40 shadow-blue-500/10';
                iconSvg = `<span class="w-6 h-6 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>`;
            }

            toast.className = `pointer-events-auto px-4 py-3 rounded-2xl border shadow-xl backdrop-blur-md flex items-center space-x-3 text-xs font-semibold transform translate-y-4 scale-[0.96] opacity-0 ${styleClass}`;
            toast.style.transition = 'transform 200ms cubic-bezier(0.23, 1, 0.32, 1), opacity 180ms cubic-bezier(0.23, 1, 0.32, 1)';
            toast.innerHTML = `
                ${iconSvg}
                <div class="flex-1 text-slate-100 leading-snug">${message}</div>
                <button onclick="this.closest('.toast-item')?.dismiss?.() || this.parentElement.remove()" class="text-slate-400 hover:text-white p-1 rounded-lg transition-transform active:scale-[0.9]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            toast.classList.add('toast-item');

            let dismissTimer = null;
            const startDismissTimer = (ms = 3500) => {
                clearTimeout(dismissTimer);
                dismissTimer = setTimeout(dismiss, ms);
            };

            const dismiss = () => {
                clearTimeout(dismissTimer);
                toast.style.transition = 'transform 140ms ease-out, opacity 140ms ease-out';
                toast.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
                toast.classList.add('translate-y-2', 'scale-[0.97]', 'opacity-0');
                setTimeout(() => toast.remove(), 150);
            };
            toast.dismiss = dismiss;

            // Sonner Edge Case: Pause timer saat kursor pengguna berada di atas toast
            toast.addEventListener('mouseenter', () => clearTimeout(dismissTimer));
            toast.addEventListener('mouseleave', () => startDismissTimer(1800));

            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-4', 'scale-[0.96]', 'opacity-0');
                toast.classList.add('translate-y-0', 'scale-100', 'opacity-100');
            });

            startDismissTimer(3500);
        }

        async function handleLogout() {
            const confirmed = await showConfirmDialog({
                title: 'Keluar dari Portal?',
                message: 'Apakah Anda yakin ingin mengakhiri sesi saat ini? Anda dapat masuk kembali kapan saja.',
                confirmText: 'Ya, Keluar Sekarang',
                cancelText: 'Tetap di Sini',
                type: 'danger',
                icon: 'logout'
            });
            if (!confirmed) return;

            try {
                const res = await fetch('../api/auth.php?action=logout');
                const data = await res.json();
                window.location.href = data.redirect || 'login.php';
            } catch (err) {
                window.location.href = 'login.php';
            }
        }

        async function switchOperatingBranch(branchId) {
            try {
                const formData = new FormData();
                formData.append('branch_id', branchId);
                const res = await fetch('../api/auth.php?action=switch_branch', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    showToast(data.message || 'Gagal beralih cabang', 'error');
                }
            } catch (e) {
                showToast('Terjadi kesalahan komunikasi server', 'error');
            }
        }

    </script>
    <!-- Komak Mascot (Komak-Bot) Laundry Washing & Physics Animation Engine -->
    <script src="<?= defined('BASE_URL') ? BASE_URL . '/assets/js/komak-mascot.js' : '../assets/js/komak-mascot.js' ?>?v=<?= file_exists(__DIR__ . '/../assets/js/komak-mascot.js') ? filemtime(__DIR__ . '/../assets/js/komak-mascot.js') : time() ?>"></script>
    <!-- KomakFX Animation & Micro-Interactions Library -->
    <script src="<?= defined('BASE_URL') ? BASE_URL . '/assets/js/komak-fx.js' : '../assets/js/komak-fx.js' ?>?v=<?= file_exists(__DIR__ . '/../assets/js/komak-fx.js') ? filemtime(__DIR__ . '/../assets/js/komak-fx.js') : time() ?>"></script>
</body>
</html>
