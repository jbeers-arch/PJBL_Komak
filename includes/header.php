<?php
/**
 * Header Template
 * Proyek: PJBL_Komak (Institutional Academic & Fintech Styling)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();

$currentUser = $_SESSION['user'];
$pageTitle = $pageTitle ?? 'Dashboard';

$appDb = Database::getConnection();
$activeBranches = $appDb->query("SELECT id, code, name FROM branches WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= APP_NAME ?></title>
    
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
          }
        } catch (e) {}
      })();
      window.APP_BRANCHES = <?= json_encode($activeBranches) ?>;
    </script>

    <!-- Google Fonts: Plus Jakarta Sans & Material Symbols Outlined -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    
    <!-- FontAwesome 6 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
      if (typeof tailwind !== 'undefined') {
        tailwind.config = {
          darkMode: 'class',
          theme: {
            extend: {
              colors: {
                brand: {
                  navy: '#000079',
                  blue: '#0100b8',
                  gold: '#f2a900',
                  ice: '#f8f9fe',
                  50: '#eef2ff',
                  100: '#e0e7ff',
                  200: '#c7d2fe',
                  300: '#a5b4fc',
                  400: '#818cf8',
                  500: '#6366f1',
                  600: '#4f46e5',
                  700: '#4338ca',
                  800: '#3730a3',
                  900: '#312e81',
                }
              },
              fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                heading: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                mono: ['"Plus Jakarta Sans"', 'monospace'],
              },
              boxShadow: {
                'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                'card': '0 0 0 1px rgba(226, 232, 240, 0.8), 0 2px 8px -2px rgba(15, 23, 42, 0.06)',
                'glow': '0 0 25px -5px rgba(1, 0, 184, 0.3)',
              }
            }
          }
        };
      }
    </script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Project Stylesheet (KSPM Academic & Fintech Design System) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css?v=<?= file_exists(__DIR__ . '/../assets/css/styles.css') ? filemtime(__DIR__ . '/../assets/css/styles.css') : time() ?>">

    <style>
        html, body {
            height: 100%;
            height: 100dvh;
            max-width: 100vw;
            overflow: hidden;
            width: 100%;
        }
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        @media print {
            body * { visibility: hidden; }
            #receipt-print-area, #receipt-print-area * { visibility: visible; }
            #receipt-print-area { position: absolute; left: 0; top: 0; width: 80mm; }
        }
        /* Grok Laundry Mascot (Komak-Bot) Animations */
        @keyframes mascotFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-2.5px) rotate(1deg); }
        }
        .mascot-orb {
            animation: mascotFloat 4s ease-in-out infinite;
            transition: transform 0.2s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.2s ease;
        }
        @media (hover: hover) and (pointer: fine) {
            .mascot-orb:hover {
                transform: scale(1.06) translateY(-1px);
            }
        }
        .mascot-orb:active {
            transform: scale(0.94) translateY(0);
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
<body class="bg-[#f8f9fe] dark:bg-[#0b101e] text-[#1e293b] dark:text-[#f8fafc] antialiased flex flex-col h-screen h-[100dvh] overflow-hidden selection:bg-indigo-500 selection:text-white transition-colors duration-200 w-full max-w-full">
    <!-- Top Navbar -->
    <header class="bg-white/95 dark:bg-[#0d1322]/95 backdrop-blur-md border-b border-slate-200/80 dark:border-white/10 shrink-0 z-30 shadow-xs transition-colors w-full min-w-0 max-w-full h-14 sm:h-16">
        <div class="px-3 sm:px-6 lg:px-8 flex items-center justify-between h-full w-full max-w-full min-w-0">
            <!-- Brand / Logo & Mobile Menu Toggle -->
            <div class="flex items-center space-x-2 sm:space-x-3 shrink-0 min-w-0">
                <!-- Mobile Menu Hamburger Button -->
                <button type="button" onclick="toggleMobileSidebar()" class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition-all active:scale-[0.92] shrink-0" title="Buka Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="flex items-center space-x-2.5 sm:space-x-3 group cursor-pointer shrink-0" onclick="window.location.href='<?= $currentUser['role'] === 'kasir' ? 'pos.php' : 'dashboard.php' ?>'">
                    <!-- Animated Grok Mascot (Komak-Bot) -->
                    <div class="grok-mascot relative w-8 h-8 sm:w-10 sm:h-10 select-none shrink-0 cursor-pointer" title="Klik saya untuk mulai mencuci! (Komak-Bot 🫧)">
                        <div class="mascot-orb w-full h-full rounded-full bg-gradient-to-tr from-[#000079] via-[#0100b8] to-[#2563eb] relative flex items-center justify-center shadow-md shadow-[#0100b8]/35 group-hover:shadow-lg group-hover:shadow-blue-600/40">
                            <!-- Gloss Specular Highlight -->
                            <div class="absolute top-0.5 sm:top-1 left-1.5 sm:left-2 w-2.5 sm:w-3.5 h-1 sm:h-1.5 bg-white/40 rounded-full blur-[0.4px] -rotate-30 pointer-events-none"></div>
                            <!-- Mascot Face & Animated Eyes -->
                            <div class="mascot-face flex items-center justify-center space-x-1 sm:space-x-1.5 transition-transform duration-100 ease-out">
                                <div class="mascot-eye eye-left w-1.5 sm:w-2 h-3 sm:h-4 bg-white rounded-full"></div>
                                <div class="mascot-eye eye-right w-1.5 sm:w-2 h-3 sm:h-4 bg-white rounded-full"></div>
                            </div>
                            <!-- Tiny Soap Bubble / Clean Sparkle Accent -->
                            <div class="absolute -top-0.5 -right-0.5 w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full bg-cyan-400/90 border border-white/80 blur-[0.2px] shadow-xs animate-pulse"></div>
                        </div>
                        <!-- Mini Speech Bubble on Click -->
                        <div class="mascot-speech absolute -bottom-7 left-1/2 -translate-x-1/2 whitespace-nowrap bg-slate-900/95 dark:bg-white text-white dark:text-slate-900 text-[10px] font-extrabold py-0.5 px-2 rounded-lg shadow-lg opacity-0 pointer-events-none transition-all duration-200 z-50">
                            Hai! ✨
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center space-x-1.5">
                            <h1 class="text-sm sm:text-base font-extrabold text-[#000079] dark:text-white tracking-tight leading-none whitespace-nowrap">PJBL Komak</h1>
                        </div>
                        <p class="hidden sm:block text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5 whitespace-nowrap truncate">SIA &amp; POS Laundry Multi-Cabang</p>
                    </div>
                </div>
            </div>

            <!-- Badges, Theme Toggle & User Status -->
            <div class="flex items-center space-x-1.5 sm:space-x-3 shrink-0">
                <!-- Theme Toggle Button (Dark / Light Mode) -->
                <button type="button" onclick="toggleTheme()" id="theme-toggle" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/90 dark:border-slate-700 text-slate-700 dark:text-amber-400 transition-all active:scale-[0.92] flex items-center justify-center flex-shrink-0 shadow-xs" title="Ganti Mode (Dark / Light)">
                    <i id="theme-icon" class="fa-solid fa-moon text-slate-700 dark:text-amber-400 text-xs sm:text-sm"></i>
                </button>

                <!-- Status Koneksi (Online / Offline) -->
                <div id="connection-status-badge" class="inline-flex items-center px-2 sm:px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40 shadow-xs transition-all shrink-0" title="Online (Terhubung Pusat)">
                    <span class="relative flex h-2 w-2 sm:mr-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span id="connection-status-text" class="hidden sm:inline tracking-wide">ONLINE</span>
                </div>

                <!-- Cabang Selector (Superadmin & Akuntan) / Badge (Kasir) -->
                <?php if (in_array($currentUser['role'] ?? '', ['superadmin', 'akuntan'])): ?>
                    <div class="hidden sm:inline-flex items-center">
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#0100b8] dark:text-blue-400 pointer-events-none">
                                <i class="fa-solid fa-store text-xs"></i>
                            </span>
                            <select id="header-branch-select" onchange="switchOperatingBranch(this.value)" title="Ganti Cabang Operasional" class="pl-7 pr-7 py-1 rounded-xl text-xs font-bold bg-slate-100/90 hover:bg-slate-200/80 dark:bg-white/[0.08] dark:hover:bg-white/[0.12] text-slate-800 dark:text-slate-200 border border-slate-200/80 dark:border-white/10 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer transition-all">
                                <?php foreach ($activeBranches as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= ($currentUser['branch_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hidden sm:inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100/90 dark:bg-white/[0.06] text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-white/10">
                        <i class="fa-solid fa-store mr-1.5 text-emerald-600 dark:text-emerald-400 text-xs"></i>
                        <?= htmlspecialchars($currentUser['branch_name'] ?? 'Pusat') ?>
                    </div>
                <?php endif; ?>

                <!-- Role Badge -->
                <?php
                $roleBadgeClass = 'bg-blue-50 dark:bg-blue-950/60 text-[#0100b8] dark:text-blue-400 border-blue-200/80 dark:border-blue-800/40';
                if (($currentUser['role'] ?? '') === 'superadmin') {
                    $roleBadgeClass = 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/40';
                } elseif (($currentUser['role'] ?? '') === 'akuntan') {
                    $roleBadgeClass = 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/40';
                } elseif (($currentUser['role'] ?? '') === 'kasir') {
                    $roleBadgeClass = 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/40';
                }
                ?>
                <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg text-[10px] sm:text-[11px] font-bold uppercase tracking-wider <?= $roleBadgeClass ?> border shrink-0">
                    <?= htmlspecialchars($currentUser['role']) ?>
                </span>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-1 sm:space-x-2 pl-1.5 sm:pl-2 border-l border-slate-200 dark:border-white/10 shrink-0">
                    <div class="hidden md:flex items-center space-x-2 bg-slate-50 dark:bg-white/[0.04] px-2.5 py-1 rounded-xl border border-slate-200/60 dark:border-white/10">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-[#000079] to-[#0100b8] text-white font-bold text-xs flex items-center justify-center">
                            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($currentUser['name']) ?></span>
                    </div>

                    <button onclick="handleLogout()" title="Keluar Akun" class="p-1.5 sm:p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-all duration-150 active:scale-[0.92] shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 min-h-0 relative w-full max-w-full overflow-hidden">
        <?php include_once __DIR__ . '/sidebar.php'; ?>
        <main class="flex-1 min-w-0 h-full p-4 sm:p-6 lg:p-8 pb-14 sm:pb-20 overflow-y-auto max-w-7xl mx-auto w-full">
