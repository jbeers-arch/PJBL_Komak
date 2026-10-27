<?php
$pageTitle = 'Buku Besar (General Ledger)';
require_once __DIR__ . '/../includes/header.php';
requireAuth(['akuntan']);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Buku Besar (General Ledger)</h2>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-[#11182c] p-4 rounded-3xl border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col md:flex-row md:items-end justify-between gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:items-center gap-3 w-full lg:w-auto">
            <div class="w-full sm:col-span-2 lg:w-72">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pilih Akun Spesifik</label>
                <select id="ledger-account-select" onchange="loadLedger()" class="w-full p-2.5 text-xs rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-bold focus:ring-2 focus:ring-indigo-500">
                    <option value="">Memuat akun...</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:col-span-2 lg:flex lg:items-center">
                <div class="w-full">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" id="ledger-start-date" value="<?= date('Y-01-01') ?>" onchange="loadLedger()" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 font-medium">
                </div>

                <div class="w-full">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" id="ledger-end-date" value="<?= date('Y-m-d') ?>" onchange="loadLedger()" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 font-medium">
                </div>
            </div>

            <div class="w-full sm:col-span-2 lg:w-auto">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Entitas Cabang</label>
                <select id="ledger-branch-select" onchange="loadLedger()" class="w-full p-2 text-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Cabang (Konsolidasi)</option>
                    <?php foreach ($activeBranches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="w-full md:w-auto">
            <button onclick="loadLedger()" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs justify-center flex items-center">
                Segarkan Buku Besar
            </button>
        </div>
    </div>

    <!-- Quick Switcher Akun Aktif Bermutasi -->
    <div id="active-accounts-container" class="bg-gradient-to-r from-indigo-50/70 to-slate-50 p-4 rounded-3xl border border-indigo-100 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-indigo-900 flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Akun yang Memiliki Mutasi Transaksi pada Periode Ini (Klik Cepat):</span>
            </span>
            <span id="active-acc-count" class="text-[10px] font-bold text-indigo-700 bg-indigo-100/70 px-2 py-0.5 rounded-full font-mono">0 Akun</span>
        </div>
        <div id="active-accounts-chips" class="flex flex-wrap items-center gap-1.5 pt-1">
            <span class="text-xs text-slate-400 italic">Memuat aktivitas akun...</span>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Saldo Awal -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-slate-300"></div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Saldo Awal Periode</span>
            <h4 id="box-initial-balance" class="text-2xl font-black text-slate-800 mt-2 font-mono">Rp 0</h4>
            <span class="text-[11px] text-slate-400 mt-1 block">Posisi sebelum tanggal mulai</span>
        </div>

        <!-- Card 2: Mutasi Periode -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500"></div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Mutasi Periode</span>
            <div class="flex items-center space-x-3 mt-2">
                <span id="box-period-debit" class="text-sm font-bold text-emerald-700 font-mono bg-emerald-50 px-2 py-0.5 rounded-md">D: Rp 0</span>
                <span class="text-slate-300">|</span>
                <span id="box-period-credit" class="text-sm font-bold text-indigo-700 font-mono bg-indigo-50 px-2 py-0.5 rounded-md">K: Rp 0</span>
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Aktivitas debit dan kredit</span>
        </div>

        <!-- Card 3: Saldo Akhir -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 to-blue-600"></div>
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Saldo Akhir Berjalan</span>
            <h4 id="box-ending-balance" class="text-2xl font-black text-indigo-900 mt-2 font-mono">Rp 0</h4>
            <span class="text-[11px] text-slate-400 mt-1 block">Akumulasi riil posisi akun</span>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto no-scrollbar touch-scroll">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase font-bold tracking-wider">
                        <th class="py-3.5 px-4 w-32">Tanggal</th>
                        <th class="py-3.5 px-4 w-36">No Transaksi</th>
                        <th class="py-3.5 px-4">Deskripsi / Memo Transaksi</th>
                        <th class="py-3.5 px-4 w-32">Cabang</th>
                        <th class="py-3.5 px-4 text-right w-32">Debit (Rp)</th>
                        <th class="py-3.5 px-4 text-right w-32">Kredit (Rp)</th>
                        <th class="py-3.5 px-4 text-right w-40">Saldo Berjalan (Rp)</th>
                    </tr>
                </thead>
                <tbody id="ledger-table-body" class="divide-y divide-slate-100">
                    <tr><td colspan="7" class="py-12 text-center text-slate-400">Pilih akun untuk melihat mutasi buku besar.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let rawAccounts = [];
    let initialLoadDone = false;

    document.addEventListener('DOMContentLoaded', async () => {
        await loadAccountsForLedger();
        
        // Cek URL params
        const urlParams = new URLSearchParams(window.location.search);
        const urlAccId = urlParams.get('account_id');
        const urlStart = urlParams.get('start_date');
        const urlEnd = urlParams.get('end_date');
        const urlBranch = urlParams.get('branch_id');

        if (urlStart) document.getElementById('ledger-start-date').value = urlStart;
        if (urlEnd) document.getElementById('ledger-end-date').value = urlEnd;
        if (urlBranch) document.getElementById('ledger-branch-select').value = urlBranch;

        if (urlAccId) {
            document.getElementById('ledger-account-select').value = urlAccId;
        }

        await loadLedger();
    });

    async function loadAccountsForLedger() {
        try {
            const res = await fetch('../api/accounts.php');
            const result = await res.json();
            if (!result.success) return;

            rawAccounts = result.data;
            const select = document.getElementById('ledger-account-select');
            select.innerHTML = '';

            const groups = {
                'Aset': '🏢 Pos Aset / Harta (Masuk Neraca)',
                'Liabilitas': '⚖️ Pos Liabilitas / Kewajiban (Masuk Neraca)',
                'Ekuitas': '🏛️ Pos Ekuitas / Modal (Masuk Neraca)',
                'Pendapatan': '💰 Pos Pendapatan (Masuk Laba Rugi)',
                'Beban_Pokok': '📦 Beban Pokok Layanan / HPP (Masuk Laba Rugi)',
                'Beban_Operasional': '📋 Beban Operasional Usaha (Masuk Laba Rugi)'
            };

            const optgroups = {};
            for (const key in groups) {
                const og = document.createElement('optgroup');
                og.label = groups[key];
                optgroups[key] = og;
            }

            rawAccounts.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.innerText = `[${a.code}] ${a.name}`;
                if (optgroups[a.category]) {
                    optgroups[a.category].appendChild(opt);
                } else {
                    select.appendChild(opt);
                }
            });

            for (const key in optgroups) {
                if (optgroups[key].children.length > 0) {
                    select.appendChild(optgroups[key]);
                }
            }
        } catch (e) {}
    }

    function switchLedgerAccount(accId) {
        document.getElementById('ledger-account-select').value = accId;
        if (window.history.pushState) {
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('account_id', accId);
            window.history.pushState({}, '', newUrl);
        }
        loadLedger();
    }

    async function loadLedger() {
        const select = document.getElementById('ledger-account-select');
        let accId = select.value;
        const start = document.getElementById('ledger-start-date').value;
        const end = document.getElementById('ledger-end-date').value;
        const branch = document.getElementById('ledger-branch-select').value;

        try {
            const res = await fetch(`../api/ledger.php?account_id=${accId || 0}&start_date=${start}&end_date=${end}&branch_id=${branch}`);
            const result = await res.json();
            if (!result.success) return;

            // Render chips akun aktif yang bermutasi pada periode ini
            renderActiveAccountChips(result.active_accounts || [], result.account?.id);

            // Jika load awal dan akun terpilih tidak punya mutasi, namun ada akun lain yang aktif, alihkan otomatis
            if (!initialLoadDone) {
                initialLoadDone = true;
                const urlParams = new URLSearchParams(window.location.search);
                if (!urlParams.get('account_id') && (!result.mutations || result.mutations.length === 0) && result.active_accounts && result.active_accounts.length > 0) {
                    select.value = result.active_accounts[0].id;
                    return loadLedger();
                }
            }

            if (result.account) {
                select.value = result.account.id;
            }

            document.getElementById('box-initial-balance').innerText = 'Rp ' + Number(result.initial_balance).toLocaleString('id-ID');
            document.getElementById('box-period-debit').innerText = 'D: Rp ' + Number(result.total_debit).toLocaleString('id-ID');
            document.getElementById('box-period-credit').innerText = 'K: Rp ' + Number(result.total_credit).toLocaleString('id-ID');
            document.getElementById('box-ending-balance').innerText = 'Rp ' + Number(result.ending_balance).toLocaleString('id-ID');

            const tbody = document.getElementById('ledger-table-body');
            tbody.innerHTML = '';

            // Baris Saldo Awal
            const trInit = document.createElement('tr');
            trInit.className = 'bg-slate-50 font-bold';
            trInit.innerHTML = `
                <td class="py-3 px-4 font-mono text-slate-500">${result.period.start_date}</td>
                <td class="py-3 px-4 text-slate-400 font-mono">-</td>
                <td class="py-3 px-4 font-extrabold text-slate-800" colspan="2">Saldo Awal Periode</td>
                <td class="py-3 px-4 text-right text-slate-300 font-mono">-</td>
                <td class="py-3 px-4 text-right text-slate-300 font-mono">-</td>
                <td class="py-3 px-4 text-right font-black text-slate-900 font-mono">Rp ${Number(result.initial_balance).toLocaleString('id-ID')}</td>
            `;
            tbody.appendChild(trInit);

            if (result.mutations.length === 0) {
                const trEmpty = document.createElement('tr');
                trEmpty.innerHTML = '<td colspan="7" class="py-8 text-center text-slate-400 text-xs">Tidak ada mutasi transaksi pada periode ini untuk akun ini. Silakan pilih akun lain di atas.</td>';
                tbody.appendChild(trEmpty);
                return;
            }

            result.mutations.forEach(m => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/70 transition-colors';
                const isScan = m.ref_type === 'invoice_scan';
                if (isScan) {
                    tr.className += ' bg-indigo-50/30';
                }
                tr.innerHTML = `
                    <td class="py-2.5 px-4 text-slate-600 font-mono">${m.date}</td>
                    <td class="py-2.5 px-4 font-mono font-bold text-indigo-600 flex items-center space-x-1">
                        <a href="jurnal.php?search=${encodeURIComponent(m.transaction_no)}&start_date=2020-01-01" title="Lihat di Jurnal Umum" class="hover:underline">
                            ${m.transaction_no}
                        </a>
                        ${isScan ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-100 text-indigo-700">Scan AI</span>' : ''}
                    </td>
                    <td class="py-2.5 px-4 text-slate-800 font-medium">
                        ${m.description}
                        ${m.memo ? `<span class="block text-[11px] text-slate-400 italic">${m.memo}</span>` : ''}
                    </td>
                    <td class="py-2.5 px-4 text-slate-500 text-[11px]">${m.branch_name || 'Kantor Pusat'}</td>
                    <td class="py-2.5 px-4 text-right font-mono ${Number(m.debit) > 0 ? 'font-bold text-slate-900' : 'text-slate-300'}">
                        ${Number(m.debit) > 0 ? 'Rp ' + Number(m.debit).toLocaleString('id-ID') : '-'}
                    </td>
                    <td class="py-2.5 px-4 text-right font-mono ${Number(m.credit) > 0 ? 'font-bold text-slate-900' : 'text-slate-300'}">
                        ${Number(m.credit) > 0 ? 'Rp ' + Number(m.credit).toLocaleString('id-ID') : '-'}
                    </td>
                    <td class="py-2.5 px-4 text-right font-mono font-black text-slate-900">
                        Rp ${Number(m.running_balance).toLocaleString('id-ID')}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (e) {
            console.error('Error load ledger:', e);
        }
    }

    function renderActiveAccountChips(activeAccounts, currentAccId) {
        const container = document.getElementById('active-accounts-chips');
        const badgeCount = document.getElementById('active-acc-count');
        container.innerHTML = '';

        if (!activeAccounts || activeAccounts.length === 0) {
            container.innerHTML = '<span class="text-xs text-slate-400 italic">Belum ada transaksi pada rentang tanggal ini.</span>';
            badgeCount.innerText = '0 Akun';
            return;
        }

        badgeCount.innerText = `${activeAccounts.length} Akun Aktif`;

        activeAccounts.forEach(acc => {
            const isSelected = Number(acc.id) === Number(currentAccId);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.onclick = () => switchLedgerAccount(acc.id);
            btn.className = isSelected
                ? 'inline-flex items-center space-x-1.5 px-3 py-1 rounded-xl bg-indigo-600 text-white font-black text-[11px] shadow-xs'
                : 'inline-flex items-center space-x-1.5 px-3 py-1 rounded-xl bg-white hover:bg-indigo-50 text-slate-700 hover:text-indigo-900 font-bold text-[11px] border border-slate-200/80 transition-colors shadow-xs';
            
            btn.innerHTML = `
                <span>[${acc.code}] ${acc.name}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-mono ${isSelected ? 'bg-indigo-800 text-indigo-100' : 'bg-indigo-100 text-indigo-700'}">${acc.mutation_count}</span>
            `;
            container.appendChild(btn);
        });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
