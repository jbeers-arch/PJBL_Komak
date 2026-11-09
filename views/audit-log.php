<?php
$pageTitle = 'Jejak Audit & Log Aktivitas';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-white/[0.08]">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Jejak Audit &amp; Aktivitas</h2>
            </div>
        </div>
        <div class="flex items-center space-x-2.5">
            <button onclick="exportAuditCsv()" class="inline-flex items-center px-4 py-2.5 rounded-2xl bg-slate-900 dark:bg-white dark:text-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition-all duration-200">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh CSV
            </button>
            <button onclick="loadAuditLogs(1)" class="inline-flex items-center px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/[0.08] text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-semibold shadow-2xs transition-colors">
                <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Segarkan
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Stat 1: Total Log -->
        <div class="bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200/80 dark:border-white/[0.08] p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Aktivitas</span>
                <div class="w-9 h-9 rounded-2xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div id="stat-total-logs" class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">0</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Keseluruhan jejak tercatat</p>
            </div>
        </div>

        <!-- Stat 2: Login Hari Ini -->
        <div class="bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200/80 dark:border-white/[0.08] p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Login Hari Ini</span>
                <div class="w-9 h-9 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div id="stat-logins-today" class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">0</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Sesi login berhasil hari ini</p>
            </div>
        </div>

        <!-- Stat 3: Mutasi Perubahan Data -->
        <div class="bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200/80 dark:border-white/[0.08] p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Mutasi Data Hari Ini</span>
                <div class="w-9 h-9 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div id="stat-mutations-today" class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">0</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Tambah / edit / hapus / penyesuaian</p>
            </div>
        </div>

        <!-- Stat 4: Login Gagal / Alert -->
        <div class="bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200/80 dark:border-white/[0.08] p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Login Gagal Hari Ini</span>
                <div class="w-9 h-9 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div id="stat-failed-today" class="text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight">0</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Upaya login ditolak</p>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200/80 dark:border-white/[0.08] p-4 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Search Text -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Cari Kata Kunci</label>
                <div class="relative">
                    <input type="text" id="filter-search" placeholder="Cari nama, aksi, modul, IP..." onkeyup="handleSearchKey(event)" class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.03] text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Modul -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Modul</label>
                <select id="filter-module" onchange="loadAuditLogs(1)" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.03] text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Modul</option>
                </select>
            </div>

            <!-- Aksi -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Jenis Aksi</label>
                <select id="filter-action" onchange="loadAuditLogs(1)" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.03] text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Aksi</option>
                </select>
            </div>

            <!-- Pengguna -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Pengguna</label>
                <select id="filter-user" onchange="loadAuditLogs(1)" class="w-full py-2 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.03] text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Pengguna</option>
                </select>
            </div>

            <!-- Reset Filter Button -->
            <div class="flex items-end">
                <button onclick="resetFilters()" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 text-xs font-semibold transition-colors flex items-center justify-center space-x-1">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>

        <!-- Date range subrow -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-3 pt-3 border-t border-slate-100 dark:border-white/[0.05]">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Dari Tanggal</label>
                <input type="date" id="filter-start-date" onchange="loadAuditLogs(1)" class="w-full py-1.5 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.03] text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Sampai Tanggal</label>
                <input type="date" id="filter-end-date" onchange="loadAuditLogs(1)" class="w-full py-1.5 px-2.5 text-xs rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.03] text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <!-- Audit Logs Table Card -->
    <div class="bg-white dark:bg-slate-900/80 rounded-3xl border border-slate-200/80 dark:border-white/[0.08] shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/[0.08] bg-slate-50/75 dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4">Waktu (WIB)</th>
                        <th class="py-3.5 px-4">Pengguna</th>
                        <th class="py-3.5 px-4">Modul</th>
                        <th class="py-3.5 px-4">Aksi</th>
                        <th class="py-3.5 px-4">Deskripsi Aktivitas</th>
                        <th class="py-3.5 px-4">Alamat IP</th>
                        <th class="py-3.5 px-4 text-center">Rincian Data</th>
                    </tr>
                </thead>
                <tbody id="audit-table-body" class="divide-y divide-slate-100 dark:divide-white/[0.05] text-slate-700 dark:text-slate-200">
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">Memuat log audit...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 border-t border-slate-100 dark:border-white/[0.05] bg-slate-50/50 dark:bg-white/[0.01]">
            <div id="pagination-info" class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan 0 dari 0 data
            </div>
            <div class="flex items-center space-x-2">
                <button id="btn-prev-page" onclick="changePage(-1)" disabled class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition-colors">
                    Sebelumnya
                </button>
                <span id="pagination-current-page" class="text-xs font-bold px-2 py-1 text-slate-700 dark:text-white">
                    Hal 1 / 1
                </span>
                <button id="btn-next-page" onclick="changePage(1)" disabled class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition-colors">
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rincian JSON Perubahan (Emil Kowalski Centered Dialog) -->
<div id="details-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-180 ease-out">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200/80 dark:border-white/[0.1] space-y-4 max-h-[90vh] flex flex-col transform scale-[0.96] opacity-0 transition-all duration-200" style="transform-origin: center; transition-timing-function: cubic-bezier(0.23, 1, 0.32, 1);">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/[0.08]">
            <div>
                <h4 class="font-extrabold text-sm text-slate-900 dark:text-white" id="modal-title">Rincian Perubahan Data</h4>
                <p class="text-[11px] text-slate-400" id="modal-subtitle">Snapshot parameter dan payload mutasi</p>
            </div>
            <button onclick="closeDetailsModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl font-bold p-1 rounded-lg transition-transform active:scale-[0.9]">&times;</button>
        </div>

        <div class="space-y-3 overflow-y-auto pr-1 flex-1 text-xs">
            <!-- Meta row -->
            <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 dark:bg-white/[0.03] rounded-2xl border border-slate-200/60 dark:border-white/[0.05]">
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Waktu &amp; IP</span>
                    <span id="modal-meta-time" class="font-mono text-slate-700 dark:text-slate-200 text-[11px]">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Pelaksana</span>
                    <span id="modal-meta-user" class="font-bold text-slate-800 dark:text-white">-</span>
                </div>
                <div class="col-span-2">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Deskripsi</span>
                    <span id="modal-meta-desc" class="text-slate-700 dark:text-slate-300">-</span>
                </div>
            </div>

            <!-- JSON Payload viewer -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Payload JSON:</label>
                <pre id="modal-json-content" class="p-3.5 bg-slate-950 text-emerald-400 text-[11px] font-mono rounded-2xl overflow-x-auto max-h-80 border border-slate-800 leading-relaxed"></pre>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-white/[0.08] flex justify-end">
            <button onclick="closeDetailsModal()" class="py-2.5 px-5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-slate-700 dark:text-slate-200 text-xs font-bold transition-all active:scale-[0.97]">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let totalPages = 1;
    let cachedLogs = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadStats();
        loadAuditLogs(1);

        // Auto-refresh log audit secara realtime setiap 10 detik
        setInterval(() => {
            const modal = document.getElementById('details-modal');
            const isModalOpen = modal && !modal.classList.contains('hidden');
            const searchVal = document.getElementById('filter-search')?.value.trim() || '';
            if (currentPage === 1 && !isModalOpen && searchVal.length === 0) {
                loadAuditLogs(1, true);
                loadStats();
            }
        }, 10000);
    });

    async function loadStats() {
        try {
            const res = await fetch('../api/audit_logs.php?action=stats');
            const data = await res.json();
            if (!data.success) return;

            document.getElementById('stat-total-logs').textContent = Number(data.stats.total_logs || 0).toLocaleString('id-ID');
            document.getElementById('stat-logins-today').textContent = Number(data.stats.logins_today || 0).toLocaleString('id-ID');
            document.getElementById('stat-mutations-today').textContent = Number(data.stats.mutations_today || 0).toLocaleString('id-ID');
            document.getElementById('stat-failed-today').textContent = Number(data.stats.failed_logins_today || 0).toLocaleString('id-ID');

            // Populate Modul dropdown
            const moduleSelect = document.getElementById('filter-module');
            const currentMod = moduleSelect.value;
            moduleSelect.innerHTML = '<option value="">Semua Modul</option>';
            data.modules.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = m.toUpperCase();
                if (m === currentMod) opt.selected = true;
                moduleSelect.appendChild(opt);
            });

            // Populate Action dropdown
            const actionSelect = document.getElementById('filter-action');
            const currentAct = actionSelect.value;
            actionSelect.innerHTML = '<option value="">Semua Aksi</option>';
            data.actions.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a;
                opt.textContent = a.replace(/_/g, ' ');
                if (a === currentAct) opt.selected = true;
                actionSelect.appendChild(opt);
            });

            // Populate User dropdown
            const userSelect = document.getElementById('filter-user');
            const currentUsr = userSelect.value;
            userSelect.innerHTML = '<option value="">Semua Pengguna</option>';
            data.users.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.user_id || '';
                opt.textContent = `${u.user_name} (${u.role})`;
                if (String(u.user_id) === currentUsr) opt.selected = true;
                userSelect.appendChild(opt);
            });
        } catch (err) {
            console.error('Gagal memuat statistik audit log:', err);
        }
    }

    async function loadAuditLogs(page = 1, isSilent = false) {
        currentPage = page;
        const tbody = document.getElementById('audit-table-body');
        if (!isSilent) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-12 text-center text-slate-400">Memuat log audit...</td></tr>`;
        }

        const search = document.getElementById('filter-search').value.trim();
        const module = document.getElementById('filter-module').value;
        const actionType = document.getElementById('filter-action').value;
        const userId = document.getElementById('filter-user').value;
        const startDate = document.getElementById('filter-start-date').value;
        const endDate = document.getElementById('filter-end-date').value;

        const params = new URLSearchParams({
            action: 'list',
            page: currentPage,
            limit: 50,
            search,
            module,
            action_type: actionType,
            user_id: userId,
            start_date: startDate,
            end_date: endDate
        });

        try {
            const res = await fetch(`../api/audit_logs.php?${params.toString()}`);
            const result = await res.json();

            if (!result.success || !result.data || result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="py-12 text-center text-slate-400">Tidak ada log aktivitas yang cocok dengan kriteria pencarian.</td></tr>`;
                document.getElementById('pagination-info').textContent = 'Menampilkan 0 dari 0 data';
                document.getElementById('btn-prev-page').disabled = true;
                document.getElementById('btn-next-page').disabled = true;
                document.getElementById('pagination-current-page').textContent = 'Hal 1 / 1';
                return;
            }

            cachedLogs = result.data;
            totalPages = result.pagination.total_pages || 1;

            tbody.innerHTML = '';
            result.data.forEach(log => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/70 dark:hover:bg-white/[0.02] transition-colors';

                // Action badge styling
                let actionBadge = getActionBadge(log.action);
                let roleBadge = getRoleBadge(log.role);

                tr.innerHTML = `
                    <td class="py-3 px-4 font-mono text-[11px] text-slate-600 dark:text-slate-400 whitespace-nowrap">
                        <div>${formatDateTime(log.created_at)}</div>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-white/[0.1] text-slate-700 dark:text-white flex items-center justify-center font-bold text-[10px]">
                                ${(log.user_name || '?').charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white">${log.user_name || 'Sistem'}</span>
                                <div class="mt-0.5">${roleBadge}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                        <span class="font-mono text-[11px] text-slate-600 dark:text-slate-300 font-semibold uppercase">${log.module || '-'}</span>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                        ${actionBadge}
                    </td>
                    <td class="py-3 px-4">
                        <div class="text-slate-800 dark:text-slate-200 line-clamp-2" title="${escapeHtml(log.description)}">${escapeHtml(log.description)}</div>
                    </td>
                    <td class="py-3 px-4 font-mono text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        ${log.ip_address || '-'}
                    </td>
                    <td class="py-3 px-4 text-center whitespace-nowrap">
                        ${log.details ? `
                            <button onclick="viewDetails(${log.id})" class="px-2.5 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 font-bold text-[10px] transition-colors">
                                Lihat Data
                            </button>
                        ` : `<span class="text-slate-300 dark:text-slate-600">-</span>`}
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update Pagination UI
            const startIdx = (currentPage - 1) * result.pagination.limit + 1;
            const endIdx = Math.min(result.pagination.total, currentPage * result.pagination.limit);
            document.getElementById('pagination-info').textContent = `Menampilkan ${startIdx}-${endIdx} dari ${result.pagination.total} data`;
            document.getElementById('pagination-current-page').textContent = `Hal ${currentPage} / ${totalPages}`;
            document.getElementById('btn-prev-page').disabled = currentPage <= 1;
            document.getElementById('btn-next-page').disabled = currentPage >= totalPages;

        } catch (err) {
            console.error('Gagal mengambil data audit log:', err);
            tbody.innerHTML = `<tr><td colspan="7" class="py-12 text-center text-rose-500 font-semibold">Terjadi kesalahan koneksi saat memuat log audit.</td></tr>`;
        }
    }

    function changePage(delta) {
        const target = currentPage + delta;
        if (target >= 1 && target <= totalPages) {
            loadAuditLogs(target);
        }
    }

    let searchTimer = null;
    function handleSearchKey(e) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            loadAuditLogs(1);
        }, 350);
    }

    function resetFilters() {
        document.getElementById('filter-search').value = '';
        document.getElementById('filter-module').value = '';
        document.getElementById('filter-action').value = '';
        document.getElementById('filter-user').value = '';
        document.getElementById('filter-start-date').value = '';
        document.getElementById('filter-end-date').value = '';
        loadAuditLogs(1);
    }

    function exportAuditCsv() {
        const module = document.getElementById('filter-module').value;
        const actionType = document.getElementById('filter-action').value;
        const startDate = document.getElementById('filter-start-date').value;
        const endDate = document.getElementById('filter-end-date').value;

        const params = new URLSearchParams({
            action: 'export',
            module,
            action_type: actionType,
            start_date: startDate,
            end_date: endDate
        });

        window.open(`../api/audit_logs.php?${params.toString()}`, '_blank');
    }

    function viewDetails(logId) {
        const log = cachedLogs.find(l => Number(l.id) === Number(logId));
        if (!log) return;

        document.getElementById('modal-meta-time').textContent = `${log.created_at} (IP: ${log.ip_address || '-'})`;
        document.getElementById('modal-meta-user').textContent = `${log.user_name || 'Sistem'} [${log.role || '-'}]`;
        document.getElementById('modal-meta-desc').textContent = log.description;

        let formattedJson = '';
        try {
            const parsed = typeof log.details_parsed === 'object' ? log.details_parsed : JSON.parse(log.details);
            formattedJson = JSON.stringify(parsed, null, 2);
        } catch (e) {
            formattedJson = log.details;
        }

        document.getElementById('modal-json-content').textContent = formattedJson;
        const modal = document.getElementById('details-modal');
        const card = modal.querySelector('div');
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-[0.96]', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }
        });
    }

    function closeDetailsModal() {
        const modal = document.getElementById('details-modal');
        const card = modal.querySelector('div');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        if (card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-[0.96]', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 140);
    }

    function getActionBadge(action) {
        action = (action || '').toUpperCase();
        const base = "inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border tracking-wide";
        if (action === 'LOGIN') {
            return `<span class="${base} bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40">LOGIN</span>`;
        } else if (action === 'LOGIN_FAILED') {
            return `<span class="${base} bg-rose-50 text-rose-700 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/40">GAGAL LOGIN</span>`;
        } else if (action === 'LOGOUT') {
            return `<span class="${base} bg-slate-100 text-slate-700 border-slate-200/80 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">LOGOUT</span>`;
        } else if (action === 'CREATE') {
            return `<span class="${base} bg-sky-50 text-sky-700 border-sky-200/80 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800/40">TAMBAH</span>`;
        } else if (action === 'UPDATE') {
            return `<span class="${base} bg-amber-50 text-amber-700 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40">UBAH</span>`;
        } else if (action === 'DELETE') {
            return `<span class="${base} bg-rose-50 text-rose-700 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/40">HAPUS</span>`;
        } else if (action === 'CLOSING_PERIOD') {
            return `<span class="${base} bg-purple-50 text-purple-700 border-purple-200/80 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/40">TUTUP BUKU</span>`;
        } else if (action === 'REVERSE') {
            return `<span class="${base} bg-orange-50 text-orange-700 border-orange-200/80 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800/40">PEMBALIK</span>`;
        } else if (action === 'STOCK_ADJUST') {
            return `<span class="${base} bg-teal-50 text-teal-700 border-teal-200/80 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800/40">ADJUST STOK</span>`;
        } else if (action === 'POS_SYNC') {
            return `<span class="${base} bg-indigo-50 text-indigo-700 border-indigo-200/80 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800/40">POS SYNC</span>`;
        } else if (action === 'OPEN_SHIFT') {
            return `<span class="${base} bg-cyan-50 text-cyan-700 border-cyan-200/80 dark:bg-cyan-950/40 dark:text-cyan-300 dark:border-cyan-800/40">BUKA SHIFT</span>`;
        } else if (action === 'CLOSE_SHIFT') {
            return `<span class="${base} bg-violet-50 text-violet-700 border-violet-200/80 dark:bg-violet-950/40 dark:text-violet-300 dark:border-violet-800/40">TUTUP SHIFT</span>`;
        } else if (action === 'APPROVE') {
            return `<span class="${base} bg-blue-50 text-blue-700 border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/40">APPROVE</span>`;
        } else if (action === 'SWITCH_BRANCH') {
            return `<span class="${base} bg-slate-100 text-slate-800 border-slate-200/90 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">SWITCH_BRANCH</span>`;
        }
        return `<span class="${base} bg-slate-100 text-slate-700 border-slate-200/80 dark:bg-white/[0.08] dark:text-slate-300">${action}</span>`;
    }

    function getRoleBadge(role) {
        role = (role || '').toLowerCase();
        if (role === 'superadmin') {
            return `<span class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400">superadmin</span>`;
        } else if (role === 'akuntan') {
            return `<span class="text-[9px] font-bold text-sky-600 dark:text-sky-400">akuntan</span>`;
        } else if (role === 'kasir') {
            return `<span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400">kasir</span>`;
        }
        return `<span class="text-[9px] text-slate-400">${role || '-'}</span>`;
    }

    function formatDateTime(str) {
        if (!str) return '-';
        const cleanStr = str.replace('T', ' ');
        const parts = cleanStr.split(/[- :]/);
        if (parts.length >= 6) {
            const logDate = new Date(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]);
            const now = new Date();
            const diffSec = Math.floor((now - logDate) / 1000);
            let rel = '';
            if (diffSec >= 0 && diffSec < 60) {
                rel = 'Baru saja';
            } else if (diffSec >= 60 && diffSec < 3600) {
                rel = Math.floor(diffSec / 60) + ' mnt lalu';
            } else if (diffSec >= 3600 && diffSec < 86400) {
                rel = Math.floor(diffSec / 3600) + ' jam lalu';
            }
            if (rel) {
                return `<div class="font-bold text-slate-800 dark:text-slate-100">${cleanStr}</div><div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">${rel}</div>`;
            }
        }
        return `<div>${cleanStr}</div>`;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
