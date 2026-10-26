<?php
/**
 * Login Page — PJBL Komak (Institutional Academic & Fintech Prestige)
 * Selaras dengan Desain Web KSPM
 * Proyek: PJBL_Komak
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Inisialisasi DB jika belum
$loginDb = Database::getConnection();
$loginBranches = $loginDb->query("SELECT id, code, name FROM branches WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'kasir') {
        header('Location: pos.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Masuk / Login - <?= APP_NAME ?></title>

  <!-- Immediate Theme Initializer (No Flash of Unstyled Theme) -->
  <script>
    (function() {
      try {
        var savedTheme = localStorage.getItem('kspm_theme') || localStorage.getItem('theme');
        if (savedTheme === 'dark') {
          document.documentElement.classList.add('dark');
          document.documentElement.classList.remove('light');
        } else {
          document.documentElement.classList.add('light');
          document.documentElement.classList.remove('dark');
        }
      } catch (e) {}
    })();
  </script>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              navy: '#000079',
              blue: '#0100b8',
              gold: '#f2a900',
              ice: '#f8f9fe'
            }
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            heading: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            mono: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'monospace'],
          }
        }
      }
    }
  </script>

  <!-- Google Fonts: Plus Jakarta Sans & Material Symbols Outlined -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

  <!-- FontAwesome 6 Free CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <!-- Stylesheet -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css?v=<?= file_exists(__DIR__ . '/../assets/css/styles.css') ? filemtime(__DIR__ . '/../assets/css/styles.css') : time() ?>" />

  <style>
    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
      text-rendering: optimizeLegibility;
      -webkit-font-smoothing: antialiased;
    }
    html.dark, html.dark body {
      background-color: #0b101e;
      color: #f1f5f9;
    }
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    ::selection {
      background-color: #e0e7ff;
      color: #0100b8;
    }
    html.dark ::selection {
      background-color: #1e3a8a;
      color: #93c5fd;
    }
    /* Grok Laundry Mascot (Komak-Bot) Animations */
    @keyframes mascotFloat {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-3px) rotate(1deg); }
    }
    .mascot-orb {
      animation: mascotFloat 4s ease-in-out infinite;
      transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
    }
    .mascot-orb:hover {
      transform: scale(1.08) translateY(-1px);
    }
    .mascot-eye {
      background-color: #ffffff !important;
      background: #ffffff !important;
      forced-color-adjust: none !important;
      transition: transform 0.12s ease-out, height 0.08s ease-in-out, border-radius 0.1s ease;
      box-shadow: none !important;
      filter: none !important;
    }
    /* Mode Gelap: Mata Putih Solid Bersih Tanpa Efek Bersinar */
    html.dark .mascot-eye,
    .dark .mascot-eye {
      background-color: #ffffff !important;
      background: #ffffff !important;
      forced-color-adjust: none !important;
      box-shadow: none !important;
      filter: none !important;
    }
    .mascot-eye.blink {
      transform: scaleY(0.08) !important;
    }
    .mascot-eye.happy {
      height: 4px !important;
      border-radius: 6px 6px 0 0 !important;
      border-top: 2.5px solid #ffffff !important;
      background: transparent !important;
      box-shadow: none !important;
    }
  </style>
</head>
<body class="bg-[#fafbfc] dark:bg-[#0b101e] text-[#1e293b] dark:text-slate-100 min-h-screen flex flex-col justify-between antialiased selection:bg-[#e0e7ff] dark:selection:bg-blue-900/60 selection:text-[#0100b8] dark:selection:text-blue-300 font-sans transition-colors duration-200">

  <!-- TOP HEADER NAVIGATION -->
  <header class="w-full bg-white/95 dark:bg-[#0d1322]/95 backdrop-blur-md border-b border-slate-200/80 dark:border-white/10 sticky top-0 z-30 transition-colors">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
      <!-- Brand Logo & Title -->
      <div class="flex items-center gap-2.5">
        <!-- Animated Grok Mascot (Header Small) -->
        <div class="grok-mascot relative w-9 h-9 select-none shrink-0" title="Hai! Saya Komak-Bot (Klik saya!)">
          <div class="mascot-orb w-full h-full rounded-full bg-gradient-to-tr from-[#000079] via-[#0100b8] to-[#2563eb] relative flex items-center justify-center shadow-md shadow-[#0100b8]/35">
            <div class="absolute top-1 left-1.5 w-3 h-1.5 bg-white/40 rounded-full blur-[0.4px] -rotate-30 pointer-events-none"></div>
            <div class="mascot-face flex items-center justify-center space-x-1 transition-transform duration-100 ease-out">
              <div class="mascot-eye eye-left w-1.5 h-3 bg-white rounded-full"></div>
              <div class="mascot-eye eye-right w-1.5 h-3 bg-white rounded-full"></div>
            </div>
            <div class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-cyan-400/90 border border-white/80 shadow-xs animate-pulse"></div>
          </div>
          <div class="mascot-speech absolute -bottom-7 left-1/2 -translate-x-1/2 whitespace-nowrap bg-slate-900/95 dark:bg-white text-white dark:text-slate-900 text-[10px] font-extrabold py-0.5 px-2 rounded-lg shadow-lg opacity-0 pointer-events-none transition-all duration-200 z-50">
            Hai! ✨
          </div>
        </div>
        <div>
          <span class="text-xs sm:text-sm font-extrabold text-[#000079] dark:text-white tracking-tight">PJBL Komak</span>
          <span class="hidden sm:inline text-[11px] text-slate-500 dark:text-slate-400 font-medium ml-1.5">&bull; SIA &amp; POS Laundry Multi-Cabang</span>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="flex items-center gap-2 sm:gap-2.5">
        <!-- Theme Toggle Button (Dark / Light Mode) -->
        <button type="button" onclick="toggleTheme()" id="theme-toggle-login" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/90 dark:border-slate-700 text-slate-700 dark:text-amber-400 transition-all flex items-center justify-center flex-shrink-0 shadow-xs" title="Ganti Mode (Dark / Light)">
          <i id="theme-icon-login" class="fa-solid fa-moon text-slate-700 dark:text-amber-400 text-sm"></i>
        </button>

        <span class="px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40 rounded-full flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Sistem Aktif</span>
        </span>
      </div>
    </div>
  </header>

  <!-- MAIN LOGIN CONTAINER -->
  <main class="flex-1 flex items-center justify-center p-4 sm:p-6 my-4 sm:my-8">
    <div class="w-full max-w-md bg-white dark:bg-[#11182c] border border-slate-200/90 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-xl transition-all relative overflow-hidden">
      
      <!-- Brand Header with Main Grok Mascot -->
      <div class="text-center mb-6">
        <div class="grok-mascot relative w-16 h-16 mx-auto mb-3 cursor-pointer select-none" title="Klik saya! (Komak-Bot)">
          <div class="mascot-orb w-full h-full rounded-full bg-gradient-to-tr from-[#000079] via-[#0100b8] to-[#2563eb] p-1 relative flex items-center justify-center shadow-xl shadow-blue-800/40 hover:shadow-blue-600/50">
            <!-- Specular shine -->
            <div class="absolute top-2 left-2.5 w-5 h-2.5 bg-white/40 rounded-full blur-[0.6px] -rotate-30 pointer-events-none"></div>
            <!-- Face with Grok Eyes -->
            <div class="mascot-face flex items-center justify-center space-x-2.5 transition-transform duration-100 ease-out">
              <div class="mascot-eye eye-left w-2.5 h-5 bg-white rounded-full"></div>
              <div class="mascot-eye eye-right w-2.5 h-5 bg-white rounded-full"></div>
            </div>
            <!-- Soap Bubble Glow Accent -->
            <div class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-cyan-400/90 border border-white/80 shadow-md animate-pulse"></div>
          </div>
          <!-- Speech Bubble on click -->
          <div class="mascot-speech absolute -bottom-7 left-1/2 -translate-x-1/2 whitespace-nowrap bg-slate-900/95 dark:bg-white text-white dark:text-slate-900 text-xs font-extrabold py-1 px-3 rounded-xl shadow-xl opacity-0 pointer-events-none transition-all duration-200 z-50">
            Halo! Siap masuk portal? ✨
          </div>
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-[#000079] dark:text-white tracking-tight">
          Masuk ke Portal
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          Sistem Informasi Akuntansi &amp; POS Laundry Multi-Cabang
        </p>
      </div>

      <!-- Login Form -->
      <form id="login-form" onsubmit="submitLogin(event)" class="space-y-4">
        <div id="login-alert" class="hidden p-3.5 rounded-xl text-xs font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40 flex items-start space-x-2">
          <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span id="login-alert-text">Login gagal.</span>
        </div>

        <div>
          <label for="username" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username</label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-xs">
              <i class="fa-solid fa-user"></i>
            </span>
            <input class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-[#0100b8]/20 focus:border-[#0100b8] dark:focus:ring-blue-500/20 dark:focus:border-blue-500 transition-all font-medium"
              type="text" id="username" name="username" autocomplete="username" required placeholder="Masukkan username akun" />
          </div>
        </div>

        <div>
          <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kata Sandi / Password</label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-xs">
              <i class="fa-solid fa-lock"></i>
            </span>
            <input class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-[#0100b8]/20 focus:border-[#0100b8] dark:focus:ring-blue-500/20 dark:focus:border-blue-500 transition-all font-medium"
              type="password" id="password" name="password" autocomplete="current-password" required placeholder="Masukkan kata sandi" />
            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors p-1" title="Lihat / Sembunyikan Password">
              <i id="pw-eye-icon" class="fa-solid fa-eye text-xs"></i>
            </button>
          </div>
        </div>

        <div>
          <label for="branch_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cabang Operasional</label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-xs">
              <i class="fa-solid fa-store"></i>
            </span>
            <select id="branch_id" name="branch_id" class="w-full pl-9 pr-8 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#0100b8]/20 focus:border-[#0100b8] dark:focus:ring-blue-500/20 dark:focus:border-blue-500 transition-all font-semibold">
              <?php foreach ($loginBranches as $b): ?>
                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <button type="submit" id="btn-submit" class="w-full py-2.5 px-4 rounded-xl bg-[#0100b8] hover:bg-[#000079] text-white text-xs sm:text-sm font-bold shadow-xs hover:shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
          <span class="material-symbols-outlined text-[18px]">login</span>
          <span id="btn-submit-text">Masuk ke Portal</span>
        </button>
      </form>

      <!-- 1-Click Login Accounts Organized by Branch -->
      <div class="mt-6 pt-5 border-t border-slate-100 dark:border-white/10 space-y-4">
        
        <!-- Section: Akun Pusat & Superadmin -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-extrabold text-[#000079] dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
              <i class="fa-solid fa-building text-[10px]"></i>
              Kantor Pusat (Superadmin)
            </span>
            <span class="text-[10px] text-slate-400">Akses Penuh</span>
          </div>
          <div class="grid grid-cols-3 gap-1.5">
            <button type="button" onclick="fillAndSubmit('Faiq', 'faiq123', 1)" class="p-2 rounded-xl border border-slate-200/80 dark:border-white/10 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 transition-all flex items-center justify-between group text-left cursor-pointer">
              <span class="text-xs font-bold text-slate-800 dark:text-white group-hover:text-[#0100b8]">Faiq</span>
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </button>
            <button type="button" onclick="fillAndSubmit('Ivan', 'ivan123', 1)" class="p-2 rounded-xl border border-slate-200/80 dark:border-white/10 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 transition-all flex items-center justify-between group text-left cursor-pointer">
              <span class="text-xs font-bold text-slate-800 dark:text-white group-hover:text-[#0100b8]">Ivan</span>
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </button>
            <button type="button" onclick="fillAndSubmit('Raina', 'raina123', 1)" class="p-2 rounded-xl border border-slate-200/80 dark:border-white/10 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 transition-all flex items-center justify-between group text-left cursor-pointer">
              <span class="text-xs font-bold text-slate-800 dark:text-white group-hover:text-[#0100b8]">Raina</span>
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </button>
            <button type="button" onclick="fillAndSubmit('Lilis', 'lilis123', 1)" class="p-2 rounded-xl border border-slate-200/80 dark:border-white/10 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 transition-all flex items-center justify-between group text-left cursor-pointer">
              <span class="text-xs font-bold text-slate-800 dark:text-white group-hover:text-[#0100b8]">Lilis</span>
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </button>
            <button type="button" onclick="fillAndSubmit('Zaskia', 'zaskia123', 1)" class="p-2 rounded-xl border border-slate-200/80 dark:border-white/10 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 transition-all flex items-center justify-between group text-left cursor-pointer">
              <span class="text-xs font-bold text-slate-800 dark:text-white group-hover:text-[#0100b8]">Zaskia</span>
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </button>
            <button type="button" onclick="fillAndSubmit('Devina', 'devina123', 1)" class="p-2 rounded-xl border border-slate-200/80 dark:border-white/10 bg-slate-50/70 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 transition-all flex items-center justify-between group text-left cursor-pointer">
              <span class="text-xs font-bold text-slate-800 dark:text-white group-hover:text-[#0100b8]">Devina</span>
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </button>
          </div>
        </div>

        <!-- Section: Akun Cabang 1 & Cabang 2 -->
        <div class="pt-2 border-t border-slate-100 dark:border-white/10">
          <div class="mb-2">
            <span class="text-[11px] font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
              <i class="fa-solid fa-store text-[10px] text-blue-600"></i>
              Akun Khusus Cabang Outlet
            </span>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <!-- Cabang 1 -->
            <button type="button" onclick="fillAndSubmit('kasir_cbg1', 'kasir123', 2)" class="p-2.5 rounded-xl border border-blue-200/80 dark:border-blue-900/40 bg-blue-50/40 hover:bg-blue-100/60 dark:bg-blue-950/20 dark:hover:bg-blue-900/40 transition-all text-left cursor-pointer group">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400">Cabang 1 (Barat)</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Kasir</span>
              </div>
              <div class="text-xs font-bold text-slate-800 dark:text-white mt-1 group-hover:text-blue-600">Siti Rahma</div>
            </button>

            <!-- Cabang 2 -->
            <button type="button" onclick="fillAndSubmit('kasir_cbg2', 'kasir123', 3)" class="p-2.5 rounded-xl border border-blue-200/80 dark:border-blue-900/40 bg-blue-50/40 hover:bg-blue-100/60 dark:bg-blue-950/20 dark:hover:bg-blue-900/40 transition-all text-left cursor-pointer group">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400">Cabang 2 (Timur)</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Kasir</span>
              </div>
              <div class="text-xs font-bold text-slate-800 dark:text-white mt-1 group-hover:text-blue-600">Budi Santoso</div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- System Footnote -->
  <footer class="w-full text-center py-4 text-xs text-slate-500 dark:text-slate-400 font-medium">
    &copy; 2026 PJBL Komak &bull; Powered by Offline-First &amp; AI OCR
  </footer>

  <script>
    function updateTerminalThemeUI(theme) {
      const icon = document.getElementById('theme-icon-login');
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
      } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
        localStorage.setItem('kspm_theme', 'light');
        updateTerminalThemeUI('light');
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const isDark = document.documentElement.classList.contains('dark');
      updateTerminalThemeUI(isDark ? 'dark' : 'light');
    });

    function togglePasswordVisibility() {
      const passInput = document.getElementById('password');
      const eyeIcon = document.getElementById('pw-eye-icon');
      if (passInput.type === 'password') {
        passInput.type = 'text';
        if (eyeIcon) eyeIcon.className = 'fa-solid fa-eye-slash text-xs';
      } else {
        passInput.type = 'password';
        if (eyeIcon) eyeIcon.className = 'fa-solid fa-eye text-xs';
      }
    }

    function fillAndSubmit(user, pass, branchId = 1) {
      document.getElementById('username').value = user;
      document.getElementById('password').value = pass;
      const bSelect = document.getElementById('branch_id');
      if (bSelect && branchId) {
        bSelect.value = branchId;
      }
      document.getElementById('login-form').dispatchEvent(new Event('submit'));
    }

    async function submitLogin(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-submit');
      const btnText = document.getElementById('btn-submit-text');
      const alert = document.getElementById('login-alert');
      const alertText = document.getElementById('login-alert-text');
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
      const branchId = document.getElementById('branch_id') ? document.getElementById('branch_id').value : '';

      btn.disabled = true;
      btnText.innerHTML = '<span class="inline-block animate-spin mr-2">&#9696;</span> Memverifikasi...';
      alert.classList.add('hidden');

      try {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        if (branchId) {
          formData.append('branch_id', branchId);
        }

        const res = await fetch('../api/auth.php?action=login', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          btnText.innerText = 'Login Berhasil!';
          window.location.href = data.redirect;
        } else {
          alertText.innerText = data.message || 'Login gagal. Periksa kembali username dan password.';
          alert.classList.remove('hidden');
          btn.disabled = false;
          btnText.innerText = 'Masuk ke Portal';
        }
      } catch (err) {
        alertText.innerText = 'Terjadi kesalahan komunikasi dengan server.';
        alert.classList.remove('hidden');
        btn.disabled = false;
        btnText.innerText = 'Masuk ke Portal';
      }
    }

    // ==========================================
    // GROK LAUNDRY MASCOT CONTROLLER (KOMAK-BOT)
    // ==========================================
    function initGrokMascots() {
      const mascots = document.querySelectorAll('.grok-mascot');
      if (!mascots.length) return;

      const greetings = [
        'Halo! Selamat datang di portal! ✨',
        'Yuk masuk dan mulai kelola cucian! 🫧',
        'Akuntansi rapi, laundry berkah! 🧺',
        'Semangat bekerja hari ini! 💫',
        'Butuh bantuan login? 😊'
      ];

      mascots.forEach(mascot => {
        const face = mascot.querySelector('.mascot-face');
        const eyes = mascot.querySelectorAll('.mascot-eye');
        const orb = mascot.querySelector('.mascot-orb');
        const speech = mascot.querySelector('.mascot-speech');

        if (!face || !eyes.length || !orb) return;

        let lastMove = Date.now();
        let isHappy = false;

        // 1. Kedip-kedip (Blink)
        function triggerBlink(doubleBlink = false) {
          if (isHappy) return;
          eyes.forEach(e => e.classList.add('blink'));
          setTimeout(() => {
            eyes.forEach(e => e.classList.remove('blink'));
            if (doubleBlink) {
              setTimeout(() => {
                eyes.forEach(e => e.classList.add('blink'));
                setTimeout(() => eyes.forEach(e => e.classList.remove('blink')), 110);
              }, 130);
            }
          }, 140);
        }

        function scheduleNextBlink() {
          const delay = Math.random() * 3200 + 2400;
          setTimeout(() => {
            const isDouble = Math.random() > 0.72;
            triggerBlink(isDouble);
            scheduleNextBlink();
          }, delay);
        }
        scheduleNextBlink();

        // 2. Nengok-nengok (Idle Roaming)
        function idleLookAround() {
          if (Date.now() - lastMove < 2200 || isHappy) {
            setTimeout(idleLookAround, 1600);
            return;
          }

          const directions = [
            { x: 0, y: 0, rot: 0 },
            { x: -3.5, y: 0, rot: -3 },
            { x: 3.5, y: 0, rot: 3 },
            { x: -2, y: -2, rot: -2 },
            { x: 2, y: -1, rot: 2 },
            { x: 0, y: 0, rot: 0 }
          ];
          const target = directions[Math.floor(Math.random() * directions.length)];
          face.style.transform = `translate(${target.x}px, ${target.y}px)`;
          orb.style.transform = `rotate(${target.rot}deg)`;

          const nextLook = Math.random() * 2600 + 1600;
          setTimeout(idleLookAround, nextLook);
        }
        setTimeout(idleLookAround, 2000);

        // 3. Ikuti Kursor (Eye Tracking)
        window.addEventListener('mousemove', (e) => {
          if (isHappy) return;
          lastMove = Date.now();
          const rect = mascot.getBoundingClientRect();
          const mX = rect.left + rect.width / 2;
          const mY = rect.top + rect.height / 2;

          const diffX = e.clientX - mX;
          const diffY = e.clientY - mY;
          const dist = Math.sqrt(diffX * diffX + diffY * diffY);

          const maxDist = 500;
          const clampedDist = Math.min(dist, maxDist) / maxDist;
          const angle = Math.atan2(diffY, diffX);

          const maxOffset = mascot.classList.contains('w-16') ? 5 : 3.5;
          const offsetX = Math.cos(angle) * (clampedDist * maxOffset);
          const offsetY = Math.sin(angle) * (clampedDist * (maxOffset * 0.75));

          face.style.transform = `translate(${offsetX.toFixed(1)}px, ${offsetY.toFixed(1)}px)`;
          const tilt = (offsetX * 0.8).toFixed(1);
          orb.style.transform = `rotate(${tilt}deg)`;
        });

        // 4. Interaksi Klik: Mata Senang (^ ^) & Balon Sapaan
        mascot.addEventListener('click', (ev) => {
          ev.stopPropagation();
          isHappy = true;
          eyes.forEach(e => e.classList.add('happy'));
          face.style.transform = 'scale(1.15) translateY(-1px)';

          if (speech) {
            const randomGreet = greetings[Math.floor(Math.random() * greetings.length)];
            speech.innerText = randomGreet;
            speech.classList.remove('opacity-0');
            speech.classList.add('opacity-100');
          }

          setTimeout(() => {
            eyes.forEach(e => e.classList.remove('happy'));
            face.style.transform = 'scale(1) translate(0px, 0px)';
            isHappy = false;
            if (speech) {
              speech.classList.remove('opacity-100');
              speech.classList.add('opacity-0');
            }
          }, 2400);
        });
      });
    }

    document.addEventListener('DOMContentLoaded', initGrokMascots);
  </script>
</body>
</html>
