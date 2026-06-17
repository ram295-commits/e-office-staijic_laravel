<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Android UI/UX Simulator - E-Office STAI JIC Surakarta">
    <title>E-Office Mobile Simulator — STAI JIC</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#1e5c45',
                        secondary: '#8AC249',
                        android: {
                            lightBg: '#f6f8f6',
                            darkBg: '#121412',
                            lightSurface: '#ffffff',
                            darkSurface: '#1e221f',
                            lightPrimary: '#1e5c45',
                            darkPrimary: '#8ed9b2',
                            lightText: '#1c1c1c',
                            darkText: '#e2e3e2'
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Custom Styling for Android Frame and Animations -->
    <style>
        /* Hide scrollbars but keep functionality */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Ambient glowing effects */
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .dark .glass-panel {
            background: rgba(25, 30, 26, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Custom Phone Shell elements */
        .phone-shell {
            box-shadow: 
                0px 0px 0px 4px #2b302c,
                0px 0px 0px 12px #151816,
                0 20px 50px rgba(0, 0, 0, 0.3);
        }

        /* Material Ripple effect animation classes */
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        .ripple {
            position: relative;
            overflow: hidden;
        }
        .ripple::after {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            pointer-events: none;
            opacity: 1;
        }

        /* Screen Transitions */
        .slide-up-enter {
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.1, 0.76, 0.55, 0.94);
        }
        .slide-up-enter-active {
            transform: translateY(0);
        }
        .slide-up-leave {
            transform: translateY(0);
            transition: transform 0.25s cubic-bezier(0.1, 0.76, 0.55, 0.94);
        }
        .slide-up-leave-active {
            transform: translateY(100%);
        }

        /* Custom notification toast styling */
        @keyframes toastIn {
            0% { transform: translateY(-40px) scale(0.9); opacity: 0; }
            100% { transform: translateY(0) scale(1); opacity: 1; }
        }
        .animate-toast {
            animation: toastIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen flex flex-col md:flex-row overflow-x-hidden selection:bg-secondary selection:text-white"
      x-data="androidAppSimulator()">

    <!-- LEFT SIDE: Control Panel & Overview (Desktop Only) -->
    <div class="w-full md:w-[32%] lg:w-[28%] bg-white border-r border-slate-200 p-6 flex flex-col justify-between shrink-0 glass-panel md:h-screen md:overflow-y-auto no-scrollbar">
        <div>
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <div class="h-10 w-10 rounded-xl bg-primary flex items-center justify-center text-white font-bold shadow-md shadow-emerald-900/10">
                    <i class="ph ph-device-mobile text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 leading-tight">E-Office Mobile</h1>
                    <span class="text-xs font-semibold text-secondary">Android UI/UX Simulator</span>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100 mb-6">
                <p class="text-xs text-emerald-800 font-medium leading-relaxed">
                    Prototipe aplikasi mobile E-Office STAI JIC dengan penyesuaian UX khusus untuk lingkungan mobile Android.
                </p>
            </div>

            <!-- Key Adjustments Section -->
            <div class="space-y-4 mb-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Penyesuaian Android UX:</h3>
                
                <div class="space-y-3">
                    <!-- Adjustment Item 1 -->
                    <div class="flex gap-3">
                        <div class="h-6 w-6 rounded bg-emerald-100/70 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="ph ph-hand-tap text-sm"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-700">Akses Satu Tangan (One-Handed UX)</h4>
                            <p class="text-[10px] text-slate-500 mt-0.5">Menu navigasi desktop digantikan Bottom Navigation Bar dan Floating Action Button (FAB) agar mudah dijangkau ibu jari.</p>
                        </div>
                    </div>

                    <!-- Adjustment Item 2 -->
                    <div class="flex gap-3">
                        <div class="h-6 w-6 rounded bg-emerald-100/70 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="ph ph-rows text-sm"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-700">Penyederhanaan Tata Letak</h4>
                            <p class="text-[10px] text-slate-500 mt-0.5">Tabel data padat dikonversi menjadi kartu (Cards) dengan target ketuk minimal 48dp.</p>
                        </div>
                    </div>

                    <!-- Adjustment Item 3 -->
                    <div class="flex gap-3">
                        <div class="h-6 w-6 rounded bg-emerald-100/70 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="ph ph-fingerprint text-sm"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-700">Integrasi Fitur Biometrik</h4>
                            <p class="text-[10px] text-slate-500 mt-0.5">Mendukung masuk cepat via Sidik Jari (Biometrics) untuk efisiensi akses kerja pegawai.</p>
                        </div>
                    </div>

                    <!-- Adjustment Item 4 -->
                    <div class="flex gap-3">
                        <div class="h-6 w-6 rounded bg-emerald-100/70 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="ph ph-sheet-holder text-sm"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-700">Bottom Sheets & Drawers</h4>
                            <p class="text-[10px] text-slate-500 mt-0.5">Semua aksi kritis seperti disposisi dan reservasi dimuat dalam laci bawah (Bottom Sheets) alih-alih modal pop-up desktop.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Selector Controls -->
            <div class="border-t border-slate-100 pt-5 mb-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Simulasikan Peran Pegawai:</label>
                <div class="grid grid-cols-3 gap-2">
                    <button @click="changeRole('admin')"
                            :class="activeRole === 'admin' ? 'bg-primary text-white border-primary shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200'"
                            class="px-2 py-2 rounded-lg text-xs font-bold border transition text-center">
                        Admin
                    </button>
                    <button @click="changeRole('manager')"
                            :class="activeRole === 'manager' ? 'bg-primary text-white border-primary shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200'"
                            class="px-2 py-2 rounded-lg text-xs font-bold border transition text-center">
                        Pimpinan
                    </button>
                    <button @click="changeRole('staff')"
                            :class="activeRole === 'staff' ? 'bg-primary text-white border-primary shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-slate-200'"
                            class="px-2 py-2 rounded-lg text-xs font-bold border transition text-center">
                        Staff
                    </button>
                </div>
                <div class="mt-2.5 text-[10px] text-slate-400 font-medium leading-relaxed">
                    Perubahan peran akan merubah daftar tugas disposisi, akses reservasi, dan hak akses tindakan pada aplikasi.
                </div>
            </div>

            <!-- Simulator Actions -->
            <div class="border-t border-slate-100 pt-5 space-y-2">
                <button @click="triggerNewMailNotification()" 
                        class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-xs transition duration-200 shadow-sm flex items-center justify-center gap-2">
                    <i class="ph ph-bell-ringing"></i> Kirim Notifikasi Baru
                </button>
                <button @click="resetSimulator()" 
                        class="w-full py-2.5 px-4 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold rounded-lg text-xs border border-slate-200 transition duration-200 flex items-center justify-center gap-2">
                    <i class="ph ph-arrows-counter-clockwise"></i> Reset Status Simulasi
                </button>
                <button @click="togglePhoneFrame()" 
                        class="w-full py-2.5 px-4 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold rounded-lg text-xs border border-slate-200 transition duration-200 flex items-center justify-center gap-2">
                    <i class="ph ph-monitor"></i> <span x-text="showFrame ? 'Sembunyikan Bingkai HP' : 'Tampilkan Bingkai HP'"></span>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-primary font-bold flex items-center gap-1">
                <i class="ph ph-arrow-left"></i> Kembali ke Web
            </a>
            <span>v1.0 (Material 3)</span>
        </div>
    </div>

    <!-- RIGHT SIDE: Emulator Display Frame -->
    <div class="flex-1 flex items-center justify-center p-3 sm:p-6 md:h-screen md:overflow-y-auto no-scrollbar bg-slate-200 relative">
        
        <!-- Background decorative patterns -->
        <div class="absolute inset-0 bg-[radial-gradient(#1e5c45_1px,transparent_1px)] [background-size:16px_16px] opacity-10 pointer-events-none"></div>
        
        <!-- Outer device shell wrapper -->
        <div :class="showFrame ? 'phone-shell w-[380px] h-[780px] rounded-[48px] border-[12px] border-slate-900 bg-black flex flex-col relative overflow-hidden shrink-0' : 'w-full max-w-md h-[95vh] rounded-3xl border border-slate-300 bg-white flex flex-col relative overflow-hidden shadow-2xl'"
             class="transition-all duration-300">
            
            <!-- Phone Speaker & Camera Notch (Only if Frame Shown) -->
            <div x-show="showFrame" class="absolute top-0 inset-x-0 h-7 bg-black z-[100] flex justify-center items-center pointer-events-none">
                <!-- Camera Punch Hole -->
                <div class="w-4 h-4 rounded-full bg-slate-900 border border-slate-800 ml-4"></div>
                <!-- Speaker Slit -->
                <div class="w-16 h-1 bg-slate-800 rounded-full ml-auto mr-auto absolute top-1.5"></div>
            </div>

            <!-- In-App Simulated Push Notification Banner (Material 3 pill style) -->
            <template x-if="activeInAppNotification">
                <div class="absolute top-10 inset-x-4 bg-white/95 dark:bg-zinc-900/95 shadow-xl border border-slate-100 dark:border-zinc-800 rounded-2xl p-3 z-[110] flex gap-3 items-center animate-toast cursor-pointer"
                     @click="openNotification(activeInAppNotification)">
                    <div class="h-9 w-9 rounded-full bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-primary dark:text-emerald-400 shrink-0">
                        <i class="ph ph-envelope-simple text-lg"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-extrabold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">E-Office JIC</span>
                            <span class="text-[9px] text-slate-400">Baru saja</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-100 truncate" x-text="activeInAppNotification.title"></h4>
                        <p class="text-[10px] text-slate-500 dark:text-zinc-400 line-clamp-1" x-text="activeInAppNotification.body"></p>
                    </div>
                </div>
            </template>

            <!-- Device Screen Content Area -->
            <div class="flex-1 flex flex-col h-full w-full relative overflow-hidden bg-android-lightBg text-android-lightText dark:bg-android-darkBg dark:text-android-darkText transition-colors duration-300"
                 :class="themeMode === 'dark' ? 'dark' : ''">

                <!-- STATUS BAR (Top indicator) -->
                <div class="h-8 shrink-0 flex justify-between items-center px-6 text-[11px] font-bold z-[80] select-none text-slate-700 dark:text-zinc-300 transition-colors"
                     :class="showFrame ? 'mt-4' : ''">
                    <!-- Time -->
                    <span x-text="simulatedTime"></span>
                    
                    <!-- Network & Battery Icons -->
                    <div class="flex items-center gap-1.5 text-xs">
                        <i class="ph ph-cellular-signal"></i>
                        <span class="text-[9px] font-bold">5G</span>
                        <i class="ph ph-wifi-high"></i>
                        <span class="text-[9px] font-bold mr-0.5">88%</span>
                        <i class="ph ph-battery-charging-vertical"></i>
                    </div>
                </div>

                <!-- MAIN WORKSPACE CONTAINER -->
                <div class="flex-1 overflow-hidden flex flex-col relative w-full h-full">

                    <!-- ========================================== -->
                    <!-- 1. SCREEN: LOGIN PAGE                      -->
                    <!-- ========================================== -->
                    <div x-show="currentScreen === 'login'"
                         class="absolute inset-0 bg-gradient-to-b from-emerald-950 via-emerald-900 to-zinc-950 text-white flex flex-col justify-between p-6 z-40 transition-all duration-300"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="scale-105 opacity-0"
                         x-transition:enter-end="scale-100 opacity-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="scale-100 opacity-100"
                         x-transition:leave-end="scale-95 opacity-0">
                        
                        <!-- Top Header & Logo -->
                        <div class="flex flex-col items-center mt-12 text-center">
                            <div class="h-16 w-16 bg-white rounded-3xl flex items-center justify-center shadow-lg border border-white/20 mb-4 animate-pulse">
                                <span class="text-primary font-black text-2xl tracking-tighter">JIC</span>
                            </div>
                            <h2 class="text-xl font-bold tracking-tight">E-Office Mobile</h2>
                            <p class="text-[10px] text-emerald-200/80 font-medium uppercase tracking-wider mt-1">STAI JIC Surakarta</p>
                        </div>

                        <!-- Login Fields & Biometrics -->
                        <div class="space-y-6 mb-8">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-emerald-200 uppercase tracking-widest mb-1.5 pl-1">Email / NIDN</label>
                                    <input type="text" :value="getMockUserEmail()" disabled class="w-full px-4 py-3 bg-white/10 border border-white/10 focus:border-secondary focus:ring-1 focus:ring-secondary rounded-2xl text-xs font-semibold text-white placeholder-emerald-300/40 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-emerald-200 uppercase tracking-widest mb-1.5 pl-1">Kata Sandi</label>
                                    <input type="password" value="********" disabled class="w-full px-4 py-3 bg-white/10 border border-white/10 focus:border-secondary focus:ring-1 focus:ring-secondary rounded-2xl text-xs font-semibold text-white placeholder-emerald-300/40 outline-none transition-all">
                                </div>
                            </div>

                            <!-- Login Action Trigger -->
                            <button @click="login()" 
                                    class="w-full py-3.5 bg-secondary hover:bg-lime-600 text-white font-bold rounded-2xl text-xs shadow-lg shadow-lime-900/20 transition duration-300 flex items-center justify-center gap-2">
                                Masuk Aplikasi <i class="ph ph-arrow-right font-bold"></i>
                            </button>

                            <!-- Biometric Touch Prompt -->
                            <div class="flex flex-col items-center mt-6">
                                <button @click="loginWithBiometrics()" 
                                        class="h-16 w-16 rounded-full bg-white/10 border border-white/10 hover:bg-white/15 flex items-center justify-center text-secondary text-3xl transition-all duration-300 transform hover:scale-105 cursor-pointer relative ripple">
                                    <i class="ph ph-fingerprint text-white"></i>
                                </button>
                                <span class="text-[10px] text-emerald-200/60 font-semibold mt-2.5">Ketuk untuk masuk dengan sidik jari</span>
                            </div>
                        </div>

                        <!-- Device Footer Copyright -->
                        <div class="text-[9px] text-center text-emerald-300/40 font-medium">
                            &copy; 2026 STAI JIC Surakarta. Hak Cipta Dilindungi.
                        </div>
                    </div>


                    <!-- ========================================== -->
                    <!-- 2. SCREEN: HOME DASHBOARD                  -->
                    <!-- ========================================== -->
                    <div x-show="currentScreen === 'dashboard'"
                         class="flex-1 flex flex-col h-full overflow-y-auto no-scrollbar px-5 pb-20 pt-2 transition-all duration-300"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <!-- Header Welcome Panel -->
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Selamat datang,</span>
                                <h3 class="text-base font-bold text-slate-800 dark:text-zinc-100 flex items-center gap-1.5" x-text="getMockUserName() + ' 👋'"></h3>
                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold mt-1"
                                     :class="activeRole === 'admin' ? 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400' : (activeRole === 'manager' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400')">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span x-text="getMockUserRoleLabel()"></span>
                                </div>
                            </div>
                            
                            <!-- Bell Alert -->
                            <button @click="openNotificationsPage()" class="relative h-10 w-10 bg-white dark:bg-zinc-900 rounded-full flex items-center justify-center border border-slate-100 dark:border-zinc-800 shadow-sm text-slate-600 dark:text-zinc-300 hover:scale-105 active:scale-95 transition-all">
                                <i class="ph ph-bell text-lg"></i>
                                <span x-show="notifications.filter(n => !n.read).length > 0"
                                      class="absolute -top-0.5 -right-0.5 bg-red-500 text-white font-black text-[9px] px-1.5 py-0.5 rounded-full border border-white dark:border-zinc-900"
                                      x-text="notifications.filter(n => !n.read).length"></span>
                            </button>
                        </div>

                        <!-- Mini Search Bar -->
                        <div class="relative mb-6">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-zinc-500">
                                <i class="ph ph-magnifying-glass"></i>
                            </span>
                            <input type="text" @click="currentScreen = 'mails'" placeholder="Cari surat, perihal, pengirim..." class="w-full pl-10 pr-12 py-3 bg-white dark:bg-zinc-900 border border-slate-150 dark:border-zinc-800 rounded-2xl text-xs font-medium text-slate-700 dark:text-zinc-200 shadow-sm cursor-pointer outline-none focus:ring-1 focus:ring-primary">
                            <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 dark:text-zinc-500 hover:text-primary">
                                <i class="ph ph-qr-code text-base"></i>
                            </span>
                        </div>

                        <!-- Stats Slider / Grid -->
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <!-- Stat 1: Incoming -->
                            <div @click="openMailsTab('incoming')" class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-100 dark:border-zinc-850 shadow-sm flex items-center gap-3.5 cursor-pointer hover:shadow-md transition">
                                <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-primary dark:text-emerald-400 flex items-center justify-center text-lg shrink-0">
                                    <i class="ph ph-arrow-down-left-bold"></i>
                                </div>
                                <div>
                                    <div class="text-base font-bold text-slate-800 dark:text-zinc-200" x-text="getMailCount('incoming')"></div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Surat Masuk</div>
                                </div>
                            </div>
                            <!-- Stat 2: Outgoing -->
                            <div @click="openMailsTab('outgoing')" class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-100 dark:border-zinc-850 shadow-sm flex items-center gap-3.5 cursor-pointer hover:shadow-md transition">
                                <div class="h-10 w-10 rounded-xl bg-lime-50 dark:bg-lime-950/40 text-secondary dark:text-lime-400 flex items-center justify-center text-lg shrink-0">
                                    <i class="ph ph-arrow-up-right-bold"></i>
                                </div>
                                <div>
                                    <div class="text-base font-bold text-slate-800 dark:text-zinc-200" x-text="getMailCount('outgoing')"></div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Surat Keluar</div>
                                </div>
                            </div>
                            <!-- Stat 3: Internal -->
                            <div @click="openMailsTab('internal')" class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-100 dark:border-zinc-850 shadow-sm flex items-center gap-3.5 cursor-pointer hover:shadow-md transition">
                                <div class="h-10 w-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-450 flex items-center justify-center text-lg shrink-0">
                                    <i class="ph ph-arrows-left-right-bold"></i>
                                </div>
                                <div>
                                    <div class="text-base font-bold text-slate-800 dark:text-zinc-200" x-text="getMailCount('internal')"></div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Surat Internal</div>
                                </div>
                            </div>
                            <!-- Stat 4: Pending Tasks -->
                            <div @click="openMailsWithFilter('pending')" class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-100 dark:border-zinc-850 shadow-sm flex items-center gap-3.5 cursor-pointer hover:shadow-md transition">
                                <div class="h-10 w-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-450 flex items-center justify-center text-lg shrink-0">
                                    <i class="ph ph-clock"></i>
                                </div>
                                <div>
                                    <div class="text-base font-bold text-slate-800 dark:text-zinc-200" x-text="getPendingCount()"></div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tindak Lanjut</div>
                                </div>
                            </div>
                        </div>

                        <!-- Graphical Summary Widget -->
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-100 dark:border-zinc-850 p-4 shadow-sm mb-6 flex items-center gap-4">
                            <div class="relative h-20 w-20 flex items-center justify-center shrink-0">
                                <!-- Simulated SVG Circle Progress -->
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                    <path class="text-slate-100 dark:text-zinc-850" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <!-- Green progress segment -->
                                    <path class="text-primary" stroke-dasharray="75, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                </svg>
                                <div class="absolute text-center flex flex-col items-center">
                                    <span class="text-sm font-extrabold text-slate-800 dark:text-zinc-100">82%</span>
                                    <span class="text-[7px] font-bold text-slate-400 uppercase tracking-wide">Selesai</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-150">Aktivitas Surat Bulan Ini</h4>
                                <p class="text-[10px] text-slate-400 leading-normal mt-0.5">Dari total 28 surat masuk & keluar, sebanyak 23 telah diselesaikan dan diarsipkan.</p>
                            </div>
                        </div>

                        <!-- Pending Dispositions / Role Tasks -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="ph ph-git-branch text-primary text-sm"></i> Tugas Disposisi Anda
                                </h4>
                                <span class="text-[10px] font-bold text-primary hover:underline cursor-pointer" @click="currentScreen = 'mails'">Lihat Semua</span>
                            </div>

                            <div class="space-y-3">
                                <template x-for="mail in getPendingDispositionsForRole()" :key="mail.id">
                                    <div @click="openMailDetails(mail)" class="bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-850 hover:border-primary dark:hover:border-emerald-700 p-3.5 rounded-2xl shadow-sm transition duration-200 cursor-pointer flex justify-between items-start gap-4">
                                        <div class="min-w-0 flex-1 space-y-1.5">
                                            <div class="flex justify-between items-center gap-2">
                                                <span class="text-[9px] font-extrabold text-primary dark:text-emerald-400 truncate max-w-[150px]" x-text="mail.reference_number"></span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold shrink-0 uppercase tracking-wide bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400" x-show="mail.priority === 'urgent'">Mendesak</span>
                                            </div>
                                            <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200 line-clamp-2 leading-relaxed" x-text="mail.subject"></h4>
                                            <div class="flex items-center gap-3 text-[10px] text-slate-500">
                                                <span class="flex items-center gap-1"><i class="ph ph-user text-[11px]"></i> Dari: <b class="font-semibold text-slate-700 dark:text-zinc-300" x-text="getDispositionFromUser(mail)"></b></span>
                                            </div>
                                        </div>
                                        <div class="h-6 w-6 rounded-full bg-slate-50 dark:bg-zinc-800 text-slate-400 hover:text-primary flex items-center justify-center shrink-0">
                                            <i class="ph ph-caret-right"></i>
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="getPendingDispositionsForRole().length === 0">
                                    <div class="text-center py-8 bg-white dark:bg-zinc-900 rounded-2xl border border-dashed border-slate-200 dark:border-zinc-800 p-6">
                                        <i class="ph ph-check-circle text-3xl text-emerald-500 mb-2"></i>
                                        <h5 class="text-xs font-bold text-slate-700 dark:text-zinc-300">Semua Tugas Selesai</h5>
                                        <p class="text-[9px] text-slate-400 mt-0.5">Tidak ada tugas disposisi tertunda untuk peran Anda.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>


                    <!-- ========================================== -->
                    <!-- 3. SCREEN: MAILS LIST                      -->
                    <!-- ========================================== -->
                    <div x-show="currentScreen === 'mails'"
                         class="flex-1 flex flex-col h-full overflow-hidden transition-all duration-300"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <!-- Top Tabs: Mail Types -->
                        <div class="bg-white dark:bg-zinc-900 border-b border-slate-100 dark:border-zinc-850 px-4 pt-1 shrink-0 flex gap-2">
                            <button @click="activeMailTypeTab = 'incoming'" 
                                    :class="activeMailTypeTab === 'incoming' ? 'border-primary text-primary dark:text-emerald-400 font-extrabold border-b-2' : 'border-transparent text-slate-500 font-semibold'"
                                    class="flex-1 py-3 text-xs text-center border-b-2 transition duration-200">
                                Masuk
                            </button>
                            <button @click="activeMailTypeTab = 'outgoing'" 
                                    :class="activeMailTypeTab === 'outgoing' ? 'border-primary text-primary dark:text-emerald-400 font-extrabold border-b-2' : 'border-transparent text-slate-500 font-semibold'"
                                    class="flex-1 py-3 text-xs text-center border-b-2 transition duration-200">
                                Keluar
                            </button>
                            <button @click="activeMailTypeTab = 'internal'" 
                                    :class="activeMailTypeTab === 'internal' ? 'border-primary text-primary dark:text-emerald-400 font-extrabold border-b-2' : 'border-transparent text-slate-500 font-semibold'"
                                    class="flex-1 py-3 text-xs text-center border-b-2 transition duration-200">
                                Internal
                            </button>
                        </div>

                        <!-- Filters & Search Inner -->
                        <div class="p-4 bg-white dark:bg-zinc-900 border-b border-slate-100 dark:border-zinc-850 shrink-0 space-y-2.5">
                            <!-- Search -->
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-zinc-500 text-xs">
                                    <i class="ph ph-magnifying-glass"></i>
                                </span>
                                <input type="text" x-model="searchQuery" placeholder="Cari perihal atau nomor ref..." class="w-full pl-8 pr-4 py-2 bg-slate-50 dark:bg-zinc-850 border border-slate-150 dark:border-zinc-800 rounded-xl text-xs font-semibold text-slate-700 dark:text-zinc-200 outline-none focus:ring-1 focus:ring-primary">
                            </div>

                            <!-- Chip Filters -->
                            <div class="flex gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                                <button @click="activeStatusFilter = 'all'"
                                        :class="activeStatusFilter === 'all' ? 'bg-primary text-white dark:bg-emerald-950 dark:text-emerald-400 font-bold' : 'bg-slate-50 text-slate-500 border border-slate-150 dark:bg-zinc-850 dark:border-zinc-800'"
                                        class="px-3 py-1 rounded-full text-[10px] shrink-0 transition">
                                    Semua
                                </button>
                                <button @click="activeStatusFilter = 'pending'"
                                        :class="activeStatusFilter === 'pending' ? 'bg-primary text-white dark:bg-emerald-950 dark:text-emerald-400 font-bold' : 'bg-slate-50 text-slate-500 border border-slate-150 dark:bg-zinc-850 dark:border-zinc-800'"
                                        class="px-3 py-1 rounded-full text-[10px] shrink-0 transition">
                                    Pending
                                </button>
                                <button @click="activeStatusFilter = 'in_progress'"
                                        :class="activeStatusFilter === 'in_progress' ? 'bg-primary text-white dark:bg-emerald-950 dark:text-emerald-400 font-bold' : 'bg-slate-50 text-slate-500 border border-slate-150 dark:bg-zinc-850 dark:border-zinc-800'"
                                        class="px-3 py-1 rounded-full text-[10px] shrink-0 transition">
                                    Proses
                                </button>
                                <button @click="activeStatusFilter = 'completed'"
                                        :class="activeStatusFilter === 'completed' ? 'bg-primary text-white dark:bg-emerald-950 dark:text-emerald-400 font-bold' : 'bg-slate-50 text-slate-500 border border-slate-150 dark:bg-zinc-850 dark:border-zinc-800'"
                                        class="px-3 py-1 rounded-full text-[10px] shrink-0 transition">
                                    Selesai
                                </button>
                            </div>
                        </div>

                        <!-- Scrollable Cards List -->
                        <div class="flex-1 overflow-y-auto no-scrollbar p-4 pb-20 space-y-3">
                            <template x-for="mail in getFilteredMails()" :key="mail.id">
                                <div @click="openMailDetails(mail)" class="bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-850 p-3.5 rounded-2xl shadow-sm transition hover:border-primary dark:hover:border-emerald-700 cursor-pointer flex justify-between items-start gap-4">
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <div class="flex justify-between items-center gap-2">
                                            <span class="text-[9px] font-extrabold text-primary dark:text-emerald-400 truncate max-w-[130px]" x-text="mail.reference_number"></span>
                                            <span :class="'inline-flex items-center px-1.5 py-0.5 rounded-full text-[8px] font-black border uppercase tracking-wider ' + getMailStatusStyle(mail.status)" x-text="getMailStatusLabel(mail.status)"></span>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-800 dark:text-zinc-200 line-clamp-2 leading-relaxed" x-text="mail.subject"></h4>
                                        
                                        <!-- Footer Meta -->
                                        <div class="flex justify-between items-center text-[9px] text-slate-400 font-semibold pt-1 border-t border-slate-50 dark:border-zinc-850">
                                            <span class="truncate max-w-[120px]"><i class="ph ph-user text-[10px] mr-0.5"></i> Dari: <b x-text="mail.sender_name"></b></span>
                                            <span><i class="ph ph-calendar text-[10px] mr-0.5"></i> <span x-text="mail.tanggal_surat"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="getFilteredMails().length === 0">
                                <div class="text-center py-12 text-slate-400 dark:text-zinc-650 bg-white dark:bg-zinc-900 rounded-2xl border border-dashed border-slate-200 dark:border-zinc-800 p-6">
                                    <i class="ph ph-envelope-open text-4xl text-primary mb-3"></i>
                                    <p class="text-xs font-bold text-slate-700 dark:text-zinc-300">Surat Tidak Ditemukan</p>
                                    <p class="text-[9px] text-slate-400 mt-1">Ganti filter status atau ubah pencarian Anda.</p>
                                </div>
                            </template>
                        </div>

                    </div>


                    <!-- ========================================== -->
                    <!-- 4. SCREEN: NUMBER RESERVATIONS             -->
                    <!-- ========================================== -->
                    <div x-show="currentScreen === 'reservations'"
                         class="flex-1 flex flex-col h-full overflow-hidden transition-all duration-300"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <!-- Top Header Screen Title -->
                        <div class="bg-white dark:bg-zinc-900 border-b border-slate-100 dark:border-zinc-850 p-4 flex justify-between items-center shrink-0">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-150">Reservasi Nomor Surat</h3>
                                <p class="text-[9px] text-slate-400 mt-0.5">Manajemen antrean nomor surat kantor</p>
                            </div>
                            
                            <!-- Add Slot Reservation -->
                            <button @click="createReservationFormOpen = true" class="h-8 w-8 bg-primary hover:bg-emerald-800 text-white rounded-lg flex items-center justify-center shadow-md">
                                <i class="ph ph-plus-bold text-sm"></i>
                            </button>
                        </div>

                        <!-- Content List -->
                        <div class="flex-1 overflow-y-auto no-scrollbar p-4 pb-20 space-y-3">
                            <template x-for="res in reservations" :key="res.id">
                                <div class="bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-850 p-3.5 rounded-2xl shadow-sm space-y-3">
                                    <div class="flex justify-between items-start gap-2">
                                        <div>
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Format Booking</span>
                                            <span class="text-xs font-extrabold text-primary dark:text-emerald-400" x-text="res.format"></span>
                                        </div>
                                        <span :class="'inline-flex items-center px-1.5 py-0.5 rounded-full text-[8px] font-extrabold border uppercase tracking-wider ' + getReservationStatusStyle(res.status)" x-text="getReservationStatusLabel(res.status)"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-semibold space-y-1 bg-slate-50 dark:bg-zinc-850/50 p-2 rounded-xl">
                                        <div>Tipe: <b class="text-slate-700 dark:text-zinc-300" x-text="res.type === 'incoming' ? 'Surat Masuk' : 'Surat Keluar'"></b></div>
                                        <div>Jumlah Slot: <b class="text-slate-700 dark:text-zinc-300" x-text="res.quantity + ' nomor'"></b></div>
                                        <div>Diajukan: <b class="text-slate-700 dark:text-zinc-300" x-text="res.created_by"></b></div>
                                    </div>

                                    <!-- Quick Admin Approve / Reject actions -->
                                    <div class="flex gap-2 pt-1.5 border-t border-slate-50 dark:border-zinc-850/50" x-show="res.status === 'pending' && activeRole === 'admin'">
                                        <button @click="approveReservation(res.id)" class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[9px] shadow-sm">
                                            Setujui
                                        </button>
                                        <button @click="rejectReservation(res.id)" class="flex-1 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 font-bold rounded-xl text-[9px]">
                                            Tolak
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>


                    <!-- ========================================== -->
                    <!-- 5. SCREEN: PROFILE & SETTINGS              -->
                    <!-- ========================================== -->
                    <div x-show="currentScreen === 'profile'"
                         class="flex-1 flex flex-col h-full overflow-y-auto no-scrollbar px-5 pb-20 pt-4 transition-all duration-300"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <!-- Profile Card -->
                        <div class="bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-850 rounded-3xl p-5 shadow-sm text-center mb-6 relative overflow-hidden">
                            <!-- Top design background decoration -->
                            <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-r from-emerald-800 to-primary"></div>
                            
                            <!-- Avatar image -->
                            <div class="relative mt-5 mb-3.5 inline-block">
                                <img :src="getMockUserAvatar()" class="h-16 w-16 rounded-full border-4 border-white dark:border-zinc-900 object-cover shadow-sm mx-auto">
                            </div>
                            
                            <h3 class="text-sm font-extrabold text-slate-800 dark:text-zinc-100" x-text="getMockUserName()"></h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5" x-text="getMockUserPosition()"></p>
                            
                            <div class="mt-4 pt-4 border-t border-slate-50 dark:border-zinc-850 flex justify-around text-center text-xs">
                                <div>
                                    <div class="font-extrabold text-slate-700 dark:text-zinc-300" x-text="getMailCount('all')"></div>
                                    <div class="text-[9px] text-slate-400 font-semibold">Total Surat</div>
                                </div>
                                <div class="border-l border-slate-100 dark:border-zinc-850"></div>
                                <div>
                                    <div class="font-extrabold text-slate-700 dark:text-zinc-300" x-text="getPendingCount()"></div>
                                    <div class="text-[9px] text-slate-400 font-semibold">Tindak Lanjut</div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Settings List -->
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest pl-1">Pengaturan Aplikasi</h4>
                            
                            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-slate-100 dark:border-zinc-850 overflow-hidden divide-y divide-slate-50 dark:divide-zinc-850">
                                
                                <!-- Toggle Dark Mode -->
                                <div class="p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-xl bg-slate-50 dark:bg-zinc-850 text-slate-600 dark:text-zinc-300 flex items-center justify-center">
                                            <i class="ph ph-moon text-base"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-800 dark:text-zinc-200">Mode Gelap (Dark Mode)</h5>
                                            <p class="text-[9px] text-slate-400">Sesuaikan tema visual sistem</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Toggle Switch -->
                                    <button @click="toggleTheme()" 
                                            :class="themeMode === 'dark' ? 'bg-secondary' : 'bg-slate-200'"
                                            class="w-10 h-6 rounded-full p-0.5 transition-colors focus:outline-none flex items-center shrink-0">
                                        <div :class="themeMode === 'dark' ? 'translate-x-4' : 'translate-x-0'"
                                             class="w-5 h-5 bg-white rounded-full shadow-sm transform transition-transform"></div>
                                    </button>
                                </div>

                                <!-- Toggle Biometrics -->
                                <div class="p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-xl bg-slate-50 dark:bg-zinc-850 text-slate-600 dark:text-zinc-300 flex items-center justify-center">
                                            <i class="ph ph-fingerprint-simple text-base"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-800 dark:text-zinc-200">Kunci Biometrik</h5>
                                            <p class="text-[9px] text-slate-400">Sidik jari untuk otentikasi login</p>
                                        </div>
                                    </div>
                                    
                                    <button @click="biometricsActive = !biometricsActive" 
                                            :class="biometricsActive ? 'bg-secondary' : 'bg-slate-200'"
                                            class="w-10 h-6 rounded-full p-0.5 transition-colors focus:outline-none flex items-center shrink-0">
                                        <div :class="biometricsActive ? 'translate-x-4' : 'translate-x-0'"
                                             class="w-5 h-5 bg-white rounded-full shadow-sm transform transition-transform"></div>
                                    </button>
                                </div>

                                <!-- Push Notification Toggle -->
                                <div class="p-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-xl bg-slate-50 dark:bg-zinc-850 text-slate-600 dark:text-zinc-300 flex items-center justify-center">
                                            <i class="ph ph-bell text-base"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-800 dark:text-zinc-200">Notifikasi Push</h5>
                                            <p class="text-[9px] text-slate-400">Terima pemberitahuan disposisi surat</p>
                                        </div>
                                    </div>
                                    
                                    <button @click="pushNotificationsActive = !pushNotificationsActive" 
                                            :class="pushNotificationsActive ? 'bg-secondary' : 'bg-slate-200'"
                                            class="w-10 h-6 rounded-full p-0.5 transition-colors focus:outline-none flex items-center shrink-0">
                                        <div :class="pushNotificationsActive ? 'translate-x-4' : 'translate-x-0'"
                                             class="w-5 h-5 bg-white rounded-full shadow-sm transform transition-transform"></div>
                                    </button>
                                </div>

                            </div>

                            <!-- Logout Button -->
                            <button @click="logout()" class="w-full py-3 bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-950/20 dark:text-red-400 font-extrabold text-xs rounded-2xl border border-red-100 dark:border-red-950/30 transition">
                                Keluar Akun
                            </button>
                        </div>

                    </div>


                    <!-- ========================================== -->
                    <!-- 6. SCREEN: NOTIFICATIONS DRAWER            -->
                    <!-- ========================================== -->
                    <div x-show="currentScreen === 'notifications'"
                         class="flex-1 flex flex-col h-full overflow-hidden transition-all duration-300"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <!-- Top Header Screen Title -->
                        <div class="bg-white dark:bg-zinc-900 border-b border-slate-100 dark:border-zinc-850 p-4 flex items-center gap-3 shrink-0">
                            <button @click="currentScreen = 'dashboard'" class="h-8 w-8 bg-slate-50 dark:bg-zinc-850 text-slate-600 dark:text-zinc-300 rounded-lg flex items-center justify-center">
                                <i class="ph ph-arrow-left text-base"></i>
                            </button>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-150">Pemberitahuan</h3>
                                <p class="text-[9px] text-slate-400 mt-0.5">Semua info dan disposisi masuk</p>
                            </div>
                        </div>

                        <!-- Content List -->
                        <div class="flex-1 overflow-y-auto no-scrollbar p-4 space-y-3">
                            <template x-for="notif in notifications" :key="notif.id">
                                <div @click="openNotification(notif)"
                                     :class="notif.read ? 'opacity-75 bg-white border-slate-100 dark:bg-zinc-900 dark:border-zinc-850' : 'bg-emerald-50/50 border-emerald-100 dark:bg-emerald-950/20 dark:border-emerald-900/40'"
                                     class="border p-4 rounded-2xl shadow-sm transition cursor-pointer flex gap-3">
                                    <div :class="notif.read ? 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400' : 'bg-primary text-white'"
                                         class="h-9 w-9 rounded-xl flex items-center justify-center shrink-0 text-base">
                                        <i class="ph ph-bell"></i>
                                    </div>
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-xs font-extrabold text-slate-800 dark:text-zinc-200" x-text="notif.title"></h4>
                                            <span class="text-[8px] font-bold text-slate-400" x-text="notif.time"></span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-zinc-400 leading-normal" x-text="notif.body"></p>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="notifications.length === 0">
                                <div class="text-center py-12 text-slate-400">
                                    <i class="ph ph-bell-slash text-4xl text-primary mb-3"></i>
                                    <p class="text-xs font-bold text-slate-700 dark:text-zinc-300">Belum ada pemberitahuan</p>
                                </div>
                            </template>
                        </div>

                    </div>


                    <!-- ========================================== -->
                    <!-- 7. SHEET ACTIVITY: MAIL DETAILS (Slide-up) -->
                    <!-- ========================================== -->
                    <div x-show="selectedMail"
                         class="absolute inset-x-0 bottom-0 top-12 bg-white dark:bg-zinc-900 z-50 flex flex-col rounded-t-[32px] shadow-2xl overflow-hidden"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-y-full"
                         x-transition:enter-end="translate-y-0"
                         x-transition:leave="transition ease-in duration-250 transform"
                         x-transition:leave-start="translate-y-0"
                         x-transition:leave-end="translate-y-full">
                        
                        <!-- Top drag line / Close handle -->
                        <div class="h-8 shrink-0 flex justify-center items-center cursor-pointer bg-slate-50/50 dark:bg-zinc-850/50" @click="selectedMail = null">
                            <div class="w-12 h-1.5 bg-slate-300 dark:bg-zinc-700 rounded-full"></div>
                        </div>

                        <!-- Header -->
                        <div class="px-6 pb-4 border-b border-slate-100 dark:border-zinc-850 flex justify-between items-start">
                            <div class="min-w-0 flex-1">
                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Dokumen Detail</span>
                                <h4 class="text-xs font-black text-primary dark:text-emerald-400 truncate" x-text="selectedMail ? selectedMail.reference_number : ''"></h4>
                                <div class="flex flex-wrap gap-1 mt-1.5" x-show="selectedMail">
                                    <!-- Status Badge -->
                                    <span :class="'inline-flex items-center px-2 py-0.5 rounded-full text-[8px] font-black border uppercase tracking-wider ' + (selectedMail ? getMailStatusStyle(selectedMail.status) : '')"
                                          x-text="selectedMail ? getMailStatusLabel(selectedMail.status) : ''"></span>
                                    <!-- Type Badge -->
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[8px] font-bold bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400 border border-slate-150 dark:border-zinc-750"
                                          x-text="selectedMail ? getMailTypeLabel(selectedMail.type) : ''"></span>
                                </div>
                            </div>
                            
                            <!-- Header Action Close Button -->
                            <button @click="selectedMail = null" class="h-8 w-8 bg-slate-50 dark:bg-zinc-850 rounded-full text-slate-400 flex items-center justify-center shrink-0">
                                <i class="ph ph-x"></i>
                            </button>
                        </div>

                        <!-- Scrollable Body Content -->
                        <div class="flex-1 overflow-y-auto no-scrollbar p-6 space-y-6">
                            
                            <!-- Subject -->
                            <div class="space-y-1">
                                <span class="text-[9px] font-extrabold text-slate-450 dark:text-zinc-500 uppercase tracking-wider block">Perihal / Subjek</span>
                                <p class="text-sm font-bold text-slate-800 dark:text-zinc-100 leading-relaxed" x-text="selectedMail ? selectedMail.subject : ''"></p>
                            </div>

                            <!-- Dates Grid -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <span class="text-[9px] font-extrabold text-slate-450 dark:text-zinc-500 uppercase tracking-wider block">Tanggal Surat</span>
                                    <p class="text-xs font-semibold text-slate-700 dark:text-zinc-300" x-text="selectedMail ? selectedMail.tanggal_surat : ''"></p>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[9px] font-extrabold text-slate-450 dark:text-zinc-500 uppercase tracking-wider block">Kategori</span>
                                    <p class="text-xs font-semibold text-slate-700 dark:text-zinc-300" x-text="selectedMail ? selectedMail.classification : '-'"></p>
                                </div>
                            </div>

                            <!-- Sender / Recipient -->
                            <div class="grid grid-cols-2 gap-4 border-t border-slate-50 dark:border-zinc-850/50 pt-4">
                                <div class="space-y-1">
                                    <span class="text-[9px] font-extrabold text-slate-450 dark:text-zinc-500 uppercase tracking-wider block">Pengirim</span>
                                    <p class="text-xs font-bold text-slate-800 dark:text-zinc-200" x-text="selectedMail ? selectedMail.sender_name : ''"></p>
                                    <p class="text-[9px] text-slate-400 truncate" x-text="selectedMail ? selectedMail.sender_organization || '-' : ''"></p>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[9px] font-extrabold text-slate-450 dark:text-zinc-500 uppercase tracking-wider block">Penerima</span>
                                    <p class="text-xs font-bold text-slate-800 dark:text-zinc-200" x-text="selectedMail ? selectedMail.recipient_name : ''"></p>
                                    <p class="text-[9px] text-slate-400 truncate" x-text="selectedMail ? selectedMail.recipient_department || '-' : ''"></p>
                                </div>
                            </div>

                            <!-- Summary Description -->
                            <div class="border-t border-slate-50 dark:border-zinc-850/50 pt-4 space-y-1.5">
                                <span class="text-[9px] font-extrabold text-slate-450 dark:text-zinc-500 uppercase tracking-wider block">Uraian / Ringkasan Isi</span>
                                <div class="p-3.5 bg-slate-50 dark:bg-zinc-850/30 rounded-2xl text-xs text-slate-600 dark:text-zinc-400 leading-relaxed whitespace-pre-line"
                                     x-text="selectedMail ? selectedMail.body : ''"></div>
                            </div>

                            <!-- Mock PDF Attachment Preview block -->
                            <div class="border-t border-slate-50 dark:border-zinc-850/50 pt-4 space-y-2" x-show="selectedMail && selectedMail.attachment_name">
                                <span class="text-[9px] font-extrabold text-slate-450 dark:text-zinc-500 uppercase tracking-wider block">Berkas Lampiran</span>
                                <div class="flex items-center justify-between p-3 bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i class="ph ph-file-pdf text-2xl text-primary shrink-0"></i>
                                        <span class="text-xs font-bold text-emerald-950 dark:text-emerald-300 truncate" x-text="selectedMail ? selectedMail.attachment_name : ''"></span>
                                    </div>
                                    <button @click="downloadMockAttachment()" class="px-3 py-1.5 bg-primary text-white font-bold rounded-xl text-[10px] shrink-0">
                                        Buka
                                    </button>
                                </div>
                            </div>

                            <!-- Vertical Disposition Timeline -->
                            <div class="border-t border-slate-50 dark:border-zinc-850/50 pt-4" x-show="selectedMail && selectedMail.dispositions && selectedMail.dispositions.length > 0">
                                <span class="text-[9px] font-extrabold text-slate-450 dark:text-zinc-500 uppercase tracking-wider block mb-3">Alur Riwayat Disposisi</span>
                                
                                <div class="space-y-4">
                                    <template x-for="disp in (selectedMail ? selectedMail.dispositions : [])" :key="disp.id">
                                        <div class="flex gap-3 relative">
                                            <!-- Vertical Timeline Connector Dot & Line -->
                                            <div class="flex flex-col items-center shrink-0">
                                                <div class="h-6 w-6 rounded-full bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-[10px] text-primary dark:text-emerald-400 font-extrabold">
                                                    <i class="ph ph-git-commit"></i>
                                                </div>
                                                <div class="w-0.5 flex-1 bg-slate-200 dark:bg-zinc-800 my-1"></div>
                                            </div>
                                            
                                            <!-- Card detail -->
                                            <div class="flex-1 bg-slate-50 dark:bg-zinc-850/40 p-3.5 rounded-2xl space-y-2">
                                                <div class="flex justify-between items-center">
                                                    <h5 class="text-xs font-bold text-slate-800 dark:text-zinc-200" x-text="'Ke: ' + disp.to_user_name"></h5>
                                                    <span :class="'inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold border uppercase tracking-wider ' + getDispositionStatusStyle(disp.status)" x-text="getDispositionStatusLabel(disp.status)"></span>
                                                </div>
                                                <p class="text-[9px] text-slate-450 font-bold" x-text="'Hal: ' + getDispositionActionLabel(disp.action_type) + ' | Dari: ' + disp.from_user_name"></p>
                                                <div class="text-[11px] text-slate-600 dark:text-zinc-400 bg-white dark:bg-zinc-900 p-2.5 rounded-xl border border-slate-100 dark:border-zinc-800/50 leading-relaxed whitespace-pre-line" x-text="disp.instruction"></div>
                                                
                                                <!-- Response fields (if staff replied) -->
                                                <div x-show="disp.response_notes" class="mt-2 p-2.5 bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-xl text-[11px] text-emerald-950 dark:text-emerald-400">
                                                    <b class="text-[9px] font-extrabold uppercase tracking-widest text-primary block mb-1">Balasan Pegawai:</b>
                                                    <span class="block leading-relaxed" x-text="disp.response_notes"></span>
                                                    <span class="block text-[8px] text-slate-400 mt-1" x-text="'Dibalas: ' + disp.responded_at"></span>
                                                </div>

                                                <!-- Staff Respond Input (Triggered only for active matching user) -->
                                                <div class="pt-1.5 border-t border-slate-100 dark:border-zinc-850 flex gap-2"
                                                     x-show="disp.status === 'pending' && isMyAssignedDisposition(disp)">
                                                    <input type="text" x-model="tempReplyNote" placeholder="Tulis catatan tanggapan..." class="flex-1 px-3 py-1.5 border border-slate-200 dark:border-zinc-850 bg-white dark:bg-zinc-900 rounded-xl text-[10px] outline-none">
                                                    <button @click="submitDispositionReply(disp)" class="px-3 py-1.5 bg-primary text-white font-bold rounded-xl text-[9px]">
                                                        Kirim
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                        </div>

                        <!-- Sticky Bottom Actions -->
                        <div class="h-20 border-t border-slate-100 dark:border-zinc-850 px-6 bg-slate-50/50 dark:bg-zinc-900/50 shrink-0 flex items-center gap-3" x-show="selectedMail">
                            <!-- Show Disposisi Sheet button -->
                            <button @click="openCreateDisposition()" class="flex-1 py-3 bg-secondary hover:bg-lime-600 text-white font-extrabold rounded-2xl text-xs shadow-md shadow-lime-900/10 flex items-center justify-center gap-1.5">
                                <i class="ph ph-git-branch text-base"></i> Disposisi
                            </button>
                            
                            <!-- Complete task action -->
                            <button @click="markMailAsCompleted()" 
                                    x-show="selectedMail && selectedMail.status !== 'completed'"
                                    class="flex-1 py-3 bg-primary hover:bg-emerald-800 text-white font-extrabold rounded-2xl text-xs shadow-md shadow-emerald-950/10 flex items-center justify-center gap-1.5">
                                <i class="ph ph-check-circle text-base"></i> Selesai
                            </button>
                        </div>
                    </div>


                    <!-- ========================================== -->
                    <!-- 8. SHEET: CREATE MAIL (FAB Form)           -->
                    <!-- ========================================== -->
                    <div x-show="createMailFormOpen"
                         class="absolute inset-x-0 bottom-0 top-12 bg-white dark:bg-zinc-900 z-50 flex flex-col rounded-t-[32px] shadow-2xl overflow-hidden"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-y-full"
                         x-transition:enter-end="translate-y-0"
                         x-transition:leave="transition ease-in duration-250 transform"
                         x-transition:leave-start="translate-y-0"
                         x-transition:leave-end="translate-y-full">
                        
                        <!-- Header Drag line -->
                        <div class="h-8 shrink-0 flex justify-center items-center cursor-pointer bg-slate-50/50 dark:bg-zinc-850/50" @click="createMailFormOpen = false">
                            <div class="w-12 h-1.5 bg-slate-300 dark:bg-zinc-700 rounded-full"></div>
                        </div>

                        <!-- Header Form Title -->
                        <div class="px-6 pb-4 border-b border-slate-100 dark:border-zinc-850 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-zinc-150">Tambah Surat Baru</h3>
                                <p class="text-[9px] text-slate-400 mt-0.5">Daftarkan surat resmi ke dalam log sistem</p>
                            </div>
                            <button @click="createMailFormOpen = false" class="h-8 w-8 bg-slate-50 dark:bg-zinc-850 rounded-full text-slate-400 flex items-center justify-center shrink-0">
                                <i class="ph ph-x"></i>
                            </button>
                        </div>

                        <!-- Form Scrollable body -->
                        <form @submit.prevent="submitNewMail()" class="flex-1 overflow-y-auto no-scrollbar p-6 space-y-4 text-xs">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Jenis Surat</label>
                                <select x-model="newMailForm.type" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                                    <option value="incoming">Surat Masuk</option>
                                    <option value="outgoing">Surat Keluar</option>
                                    <option value="internal">Surat Internal</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Perihal / Subjek</label>
                                <input type="text" x-model="newMailForm.subject" placeholder="Masukkan judul surat..." required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Pengirim</label>
                                    <input type="text" x-model="newMailForm.sender_name" placeholder="Nama pengirim..." required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Penerima</label>
                                    <input type="text" x-model="newMailForm.recipient_name" placeholder="Nama penerima..." required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Uraian / Ringkasan Isi</label>
                                <textarea x-model="newMailForm.body" rows="3" placeholder="Uraikan intisari dokumen..." required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Tanggal Surat</label>
                                    <input type="date" x-model="newMailForm.tanggal_surat" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Klasifikasi</label>
                                    <select x-model="newMailForm.classification" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                                        <option value="Dinas">Dinas</option>
                                        <option value="Pribadi">Pribadi</option>
                                        <option value="Penting">Penting</option>
                                        <option value="Biasa">Biasa</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Unggah Lampiran (Simulasi)</label>
                                <div class="border-2 border-dashed border-slate-200 dark:border-zinc-800 rounded-xl p-4 flex flex-col items-center justify-center bg-slate-50/50 dark:bg-zinc-850/20 text-center hover:bg-slate-100 transition cursor-pointer">
                                    <i class="ph ph-cloud-arrow-up text-xl text-primary mb-1"></i>
                                    <span class="font-bold text-[10px] text-slate-650 dark:text-zinc-400">Pilih berkas PDF atau Foto</span>
                                    <span class="text-[8px] text-slate-400">Maks. 5 MB</span>
                                </div>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="w-full py-3.5 bg-primary text-white font-extrabold rounded-2xl shadow-lg shadow-emerald-950/10 flex items-center justify-center gap-1.5 mt-4">
                                <i class="ph ph-plus-circle text-base"></i> Simpan Surat
                            </button>
                        </form>
                    </div>


                    <!-- ========================================== -->
                    <!-- 9. SHEET: CREATE DISPOSISi                 -->
                    <!-- ========================================== -->
                    <div x-show="createDispositionFormOpen"
                         class="absolute inset-x-0 bottom-0 top-12 bg-white dark:bg-zinc-900 z-50 flex flex-col rounded-t-[32px] shadow-2xl overflow-hidden"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-y-full"
                         x-transition:enter-end="translate-y-0"
                         x-transition:leave="transition ease-in duration-250 transform"
                         x-transition:leave-start="translate-y-0"
                         x-transition:leave-end="translate-y-full">
                        
                        <!-- Close Handle -->
                        <div class="h-8 shrink-0 flex justify-center items-center cursor-pointer bg-slate-50/50 dark:bg-zinc-850/50" @click="createDispositionFormOpen = false">
                            <div class="w-12 h-1.5 bg-slate-300 dark:bg-zinc-700 rounded-full"></div>
                        </div>

                        <!-- Header Title -->
                        <div class="px-6 pb-4 border-b border-slate-100 dark:border-zinc-850 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-zinc-150">Kirim Instruksi Disposisi</h3>
                                <p class="text-[9px] text-slate-400 mt-0.5" x-text="selectedMail ? selectedMail.reference_number : ''"></p>
                            </div>
                            <button @click="createDispositionFormOpen = false" class="h-8 w-8 bg-slate-50 dark:bg-zinc-850 rounded-full text-slate-400 flex items-center justify-center shrink-0">
                                <i class="ph ph-x"></i>
                            </button>
                        </div>

                        <!-- Form Scrollable body -->
                        <form @submit.prevent="submitNewDisposition()" class="flex-1 overflow-y-auto no-scrollbar p-6 space-y-4 text-xs">
                            
                            <!-- Select Recipient Multi -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1.5 pl-1">Pegawai Penerima Disposisi</label>
                                <div class="max-h-36 overflow-y-auto no-scrollbar border border-slate-200 dark:border-zinc-800 rounded-xl divide-y divide-slate-100 dark:divide-zinc-850/60 p-1">
                                    <template x-for="user in users" :key="user.id">
                                        <label class="flex items-center gap-2.5 px-3 py-2 hover:bg-slate-50 dark:hover:bg-zinc-850/50 rounded-lg cursor-pointer">
                                            <input type="checkbox" :value="user.id" :checked="newDispForm.to_user_ids.includes(user.id)" @change="toggleDispRecipient(user.id)" class="rounded text-primary focus:ring-primary h-4 w-4">
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-zinc-200" x-text="user.name"></div>
                                                <div class="text-[8px] text-slate-400 uppercase tracking-wide font-extrabold" x-text="user.position"></div>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <!-- Action Type & Due Date -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Hal Tindakan</label>
                                    <select x-model="newDispForm.action_type" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                                        <option value="for_action">Tindaklanjuti</option>
                                        <option value="for_review">Telaah</option>
                                        <option value="for_information">Ketahui</option>
                                        <option value="for_approval">Setujui</option>
                                        <option value="coordinate">Koordinasikan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Batas Waktu</label>
                                    <input type="date" x-model="newDispForm.due_date" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                                </div>
                            </div>

                            <!-- Instruction -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Instruksi Catatan</label>
                                <textarea x-model="newDispForm.instruction" rows="3" placeholder="Masukkan petunjuk penanganan..." required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200"></textarea>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="w-full py-3.5 bg-secondary text-white font-extrabold rounded-2xl shadow-lg shadow-lime-900/15 flex items-center justify-center gap-1.5 mt-4">
                                <i class="ph ph-paper-plane-right-bold text-base"></i> Kirim Disposisi
                            </button>
                        </form>
                    </div>


                    <!-- ========================================== -->
                    <!-- 10. SHEET: CREATE RESERVATION              -->
                    <!-- ========================================== -->
                    <div x-show="createReservationFormOpen"
                         class="absolute inset-x-0 bottom-0 top-12 bg-white dark:bg-zinc-900 z-50 flex flex-col rounded-t-[32px] shadow-2xl overflow-hidden"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="translate-y-full"
                         x-transition:enter-end="translate-y-0"
                         x-transition:leave="transition ease-in duration-250 transform"
                         x-transition:leave-start="translate-y-0"
                         x-transition:leave-end="translate-y-full">
                        
                        <!-- Close Handle -->
                        <div class="h-8 shrink-0 flex justify-center items-center cursor-pointer bg-slate-50/50 dark:bg-zinc-850/50" @click="createReservationFormOpen = false">
                            <div class="w-12 h-1.5 bg-slate-300 dark:bg-zinc-700 rounded-full"></div>
                        </div>

                        <!-- Header Form Title -->
                        <div class="px-6 pb-4 border-b border-slate-100 dark:border-zinc-850 flex justify-between items-center">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-zinc-150">Minta Reservasi Nomor</h3>
                                <p class="text-[9px] text-slate-400 mt-0.5">Ajukan slot nomor surat untuk keperluan dinas</p>
                            </div>
                            <button @click="createReservationFormOpen = false" class="h-8 w-8 bg-slate-50 dark:bg-zinc-850 rounded-full text-slate-400 flex items-center justify-center shrink-0">
                                <i class="ph ph-x"></i>
                            </button>
                        </div>

                        <!-- Form Scrollable body -->
                        <form @submit.prevent="submitNewReservation()" class="flex-1 overflow-y-auto no-scrollbar p-6 space-y-4 text-xs">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Jenis Antrean Surat</label>
                                <select x-model="newResForm.type" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                                    <option value="incoming">Surat Masuk (SM)</option>
                                    <option value="outgoing">Surat Keluar (SK)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Jumlah Slot Nomor</label>
                                <input type="number" x-model="newResForm.quantity" min="1" max="10" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mb-1 pl-1">Alasan Reservasi</label>
                                <textarea x-model="newResForm.reason" rows="3" placeholder="Masukkan alasan pemesanan slot nomor..." required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-xl outline-none text-slate-700 dark:text-zinc-200"></textarea>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="w-full py-3.5 bg-primary text-white font-extrabold rounded-2xl shadow-lg shadow-emerald-950/10 flex items-center justify-center gap-1.5 mt-4">
                                <i class="ph ph-hash text-base"></i> Ajukan Reservasi
                            </button>
                        </form>
                    </div>


                </div>

                <!-- BOTTOM NAVIGATION BAR (Android typical home indicators) -->
                <div class="h-16 shrink-0 border-t border-slate-100 bg-white/95 dark:border-zinc-850 dark:bg-zinc-900/95 flex justify-around items-center px-4 z-40 transition-colors duration-300"
                     x-show="isLoggedIn">
                    
                    <!-- Nav Tab: Home Dashboard -->
                    <button @click="currentScreen = 'dashboard'"
                            :class="currentScreen === 'dashboard' ? 'text-primary dark:text-emerald-400' : 'text-slate-400 hover:text-slate-655'"
                            class="flex flex-col items-center gap-1 focus:outline-none transition">
                        <i :class="currentScreen === 'dashboard' ? 'ph-fill' : ''" class="ph ph-squares-four text-xl"></i>
                        <span class="text-[9px] font-extrabold">Beranda</span>
                    </button>

                    <!-- Nav Tab: Mail Inbox -->
                    <button @click="currentScreen = 'mails'"
                            :class="currentScreen === 'mails' ? 'text-primary dark:text-emerald-400' : 'text-slate-400 hover:text-slate-655'"
                            class="flex flex-col items-center gap-1 focus:outline-none transition">
                        <i :class="currentScreen === 'mails' ? 'ph-fill' : ''" class="ph ph-envelope-simple text-xl"></i>
                        <span class="text-[9px] font-extrabold">Surat</span>
                    </button>

                    <!-- Center Floating Action Button (FAB) -->
                    <button @click="createMailFormOpen = true" 
                            class="h-12 w-12 rounded-full bg-secondary hover:bg-lime-600 text-white flex items-center justify-center -translate-y-4 shadow-lg shadow-lime-900/20 active:scale-95 transition-all outline-none">
                        <i class="ph ph-plus-bold text-xl"></i>
                    </button>

                    <!-- Nav Tab: Reservations -->
                    <button @click="currentScreen = 'reservations'"
                            :class="currentScreen === 'reservations' ? 'text-primary dark:text-emerald-400' : 'text-slate-400 hover:text-slate-655'"
                            class="flex flex-col items-center gap-1 focus:outline-none transition">
                        <i :class="currentScreen === 'reservations' ? 'ph-fill' : ''" class="ph ph-hash text-xl"></i>
                        <span class="text-[9px] font-extrabold">Reservasi</span>
                    </button>

                    <!-- Nav Tab: Profile settings -->
                    <button @click="currentScreen = 'profile'"
                            :class="currentScreen === 'profile' ? 'text-primary dark:text-emerald-400' : 'text-slate-400 hover:text-slate-655'"
                            class="flex flex-col items-center gap-1 focus:outline-none transition">
                        <i :class="currentScreen === 'profile' ? 'ph-fill' : ''" class="ph ph-user text-xl"></i>
                        <span class="text-[9px] font-extrabold">Profil</span>
                    </button>
                </div>

                <!-- Android Nav Gesture bar at bottom (Only if Phone Frame shown) -->
                <div x-show="showFrame" class="h-4 shrink-0 bg-white dark:bg-zinc-900 flex justify-center items-center pointer-events-none z-50">
                    <div class="w-32 h-1 bg-slate-350 dark:bg-zinc-750 rounded-full mb-1"></div>
                </div>

            </div>
        </div>

    </div>

    <!-- Interactive Simulated Notifications & Database State handler in Alpine.js -->
    <script>
        function androidAppSimulator() {
            return {
                // Device configurations
                showFrame: true,
                currentScreen: 'login',
                activeRole: 'admin', // default role
                themeMode: 'light',
                isLoggedIn: false,
                simulatedTime: '12:15',
                biometricsActive: true,
                pushNotificationsActive: true,

                // In-app Notification Banner state
                activeInAppNotification: null,

                // Active tab selections
                activeMailTypeTab: 'incoming',
                activeStatusFilter: 'all',
                searchQuery: '',
                selectedMail: null,
                tempReplyNote: '',

                // Form States
                createMailFormOpen: false,
                createDispositionFormOpen: false,
                createReservationFormOpen: false,

                // New Form Inputs
                newMailForm: {
                    type: 'incoming',
                    subject: '',
                    sender_name: '',
                    recipient_name: '',
                    body: '',
                    tanggal_surat: new Date().toISOString().split('T')[0],
                    classification: 'Dinas'
                },
                newDispForm: {
                    to_user_ids: [],
                    action_type: 'for_action',
                    due_date: new Date(Date.now() + 86400000 * 3).toISOString().split('T')[0], // +3 days
                    instruction: ''
                },
                newResForm: {
                    type: 'outgoing',
                    quantity: 1,
                    reason: ''
                },

                // Mock Database Tables
                users: [
                    { id: 1, name: 'Ahmad Fauzi, M.Pd', position: 'Administrator', role: 'admin', email: 'ahmad.fauzi@staijic.ac.id', avatar: 'https://ui-avatars.com/api/?name=Ahmad+Fauzi&background=1e5c45&color=fff' },
                    { id: 2, name: 'Dr. H. Solihin, M.Ag', position: 'Kepala Bagian Akademik', role: 'manager', email: 'solihin@staijic.ac.id', avatar: 'https://ui-avatars.com/api/?name=Dr+Solihin&background=8AC249&color=fff' },
                    { id: 3, name: 'Siti Nurhaliza, S.E', position: 'Staf Administrasi Umum', role: 'staff', email: 'siti.nurhaliza@staijic.ac.id', avatar: 'https://ui-avatars.com/api/?name=Siti+Nurhaliza&background=3b82f6&color=fff' }
                ],
                
                mails: [
                    {
                        id: 1,
                        reference_number: 'SM/0045/06/2026',
                        type: 'incoming',
                        subject: 'Undangan Rapat Koordinasi Kurikulum Baru STAI JIC',
                        body: 'Diharapkan kehadirannya dalam agenda koordinasi penyusunan silabus kurikulum Semester Ganjil 2026/2027 di Ruang Rapat Senat Utama Lantai 2.',
                        sender_name: 'Kementerian Agama Kota Surakarta',
                        sender_organization: 'Kemenag Surakarta',
                        recipient_name: 'Dr. H. Solihin, M.Ag',
                        recipient_department: 'Bagian Akademik',
                        tanggal_surat: '2026-06-12',
                        classification: 'Dinas',
                        priority: 'urgent',
                        status: 'pending',
                        attachment_name: 'Undangan_Rapat_Kurikulum_Kemenag.pdf',
                        dispositions: [
                            { id: 101, from_user_id: 1, from_user_name: 'Ahmad Fauzi, M.Pd', to_user_id: 2, to_user_name: 'Dr. H. Solihin, M.Ag', action_type: 'for_review', instruction: 'Mohon ditelaah agenda ini dan delegasikan perwakilan jika pimpinan berhalangan hadir.', status: 'pending', responded_at: null, response_notes: null }
                        ]
                    },
                    {
                        id: 2,
                        reference_number: 'SK/0098/06/2026',
                        type: 'outgoing',
                        subject: 'Surat Pengantar Pengajuan Akreditasi Program Studi Hukum Ekonomi Syariah',
                        body: 'Menindaklanjuti proses pemenuhan dokumen borang akreditasi Prodi HES, dikirimkan lampiran surat rekomendasi penjaminan mutu lembaga.',
                        sender_name: 'Dr. H. Solihin, M.Ag',
                        sender_organization: 'Bagian Akademik STAI JIC',
                        recipient_name: 'BAN-PT Jakarta',
                        recipient_department: 'Direktorat Akreditasi',
                        tanggal_surat: '2026-06-14',
                        classification: 'Penting',
                        priority: 'normal',
                        status: 'in_progress',
                        attachment_name: 'Pengantar_Akreditasi_HES.pdf',
                        dispositions: []
                    },
                    {
                        id: 3,
                        reference_number: 'SI/0012/06/2026',
                        type: 'internal',
                        subject: 'Permohonan Pengadaan Sarana Laptop Laboratorium Komputer',
                        body: 'Sehubungan dengan meningkatnya kuota penerimaan mahasiswa baru tahun akademik ini, kami mengajukan permohonan pengadaan 10 unit laptop tambahan untuk Lab Komputer Lantai 3.',
                        sender_name: 'Siti Nurhaliza, S.E',
                        sender_organization: 'Pusat Komputer JIC',
                        recipient_name: 'Ahmad Fauzi, M.Pd',
                        recipient_department: 'Administrasi Umum',
                        tanggal_surat: '2026-06-15',
                        classification: 'Dinas',
                        priority: 'normal',
                        status: 'completed',
                        attachment_name: 'Proposal_Lab_Komputer_2026.pdf',
                        dispositions: [
                            { id: 102, from_user_id: 3, from_user_name: 'Siti Nurhaliza, S.E', to_user_id: 1, to_user_name: 'Ahmad Fauzi, M.Pd', action_type: 'for_approval', instruction: 'Berikut proposal kebutuhan pengadaan komputer lab, mohon diajukan persetujuan anggaran pimpinan.', status: 'completed', responded_at: '2026-06-16 09:12', response_notes: 'Sudah disetujui pimpinan dan diteruskan ke bagian keuangan untuk pencairan dana.' }
                        ]
                    }
                ],

                reservations: [
                    { id: 1, format: 'SK/{seq}/BA-JIC/VI/2026', type: 'outgoing', quantity: 3, reason: 'Kebutuhan nomor surat keputusan kelulusan wisudawan ke-18', created_by: 'Siti Nurhaliza, S.E', status: 'pending' },
                    { id: 2, format: 'SM/{seq}/ADM-JIC/VI/2026', type: 'incoming', quantity: 1, reason: 'Booking slot nomor surat masuk resmi kemitraan Pemkot Surakarta', created_by: 'Dr. H. Solihin, M.Ag', status: 'approved' }
                ],

                notifications: [
                    { id: 1, title: 'Disposisi Baru', body: 'Anda menerima instruksi disposisi dari Ahmad Fauzi, M.Pd terkait dokumen SM/0045/06/2026', time: '1 jam yang lalu', mail_id: 1, read: false },
                    { id: 2, title: 'Reservasi Disetujui', body: 'Permohonan reservasi nomor surat masuk ADM-JIC disetujui oleh Administrator.', time: 'Kemarin', mail_id: null, read: true }
                ],

                init() {
                    // Start clock
                    this.updateTime();
                    setInterval(() => this.updateTime(), 15000);
                },

                updateTime() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const mins = String(now.getMinutes()).padStart(2, '0');
                    this.simulatedTime = `${hours}:${mins}`;
                },

                // Active User Helper functions
                getMockUser() {
                    return this.users.find(u => u.role === this.activeRole);
                },
                getMockUserName() {
                    return this.getMockUser() ? this.getMockUser().name : 'User';
                },
                getMockUserPosition() {
                    return this.getMockUser() ? this.getMockUser().position : 'Pegawai';
                },
                getMockUserEmail() {
                    return this.getMockUser() ? this.getMockUser().email : 'email@staijic.ac.id';
                },
                getMockUserAvatar() {
                    return this.getMockUser() ? this.getMockUser().avatar : 'https://ui-avatars.com/api/?name=User';
                },
                getMockUserRoleLabel() {
                    const role = this.activeRole;
                    if (role === 'admin') return 'Administrator';
                    if (role === 'manager') return 'Kepala Unit/Kajur';
                    return 'Staf/Pegawai';
                },

                changeRole(role) {
                    this.activeRole = role;
                    this.selectedMail = null;
                    this.createMailFormOpen = false;
                    this.createDispositionFormOpen = false;
                    this.createReservationFormOpen = false;
                    
                    // Trigger in-app UI refresh alert
                    this.showToast('Peran beralih ke: ' + this.getMockUserRoleLabel());
                },

                // Simulated Login Action
                login() {
                    this.isLoggedIn = true;
                    this.currentScreen = 'dashboard';
                    this.showToast('Login berhasil sebagai ' + this.getMockUserName());
                },

                loginWithBiometrics() {
                    if (this.biometricsActive) {
                        this.isLoggedIn = true;
                        this.currentScreen = 'dashboard';
                        this.showToast('Autentikasi sidik jari berhasil!');
                    } else {
                        this.showToast('Biometrik tidak aktif. Masukkan password.');
                    }
                },

                logout() {
                    this.isLoggedIn = false;
                    this.currentScreen = 'login';
                    this.showToast('Keluar akun berhasil');
                },

                toggleTheme() {
                    this.themeMode = this.themeMode === 'light' ? 'dark' : 'light';
                    this.showToast('Tema beralih ke Mode ' + (this.themeMode === 'dark' ? 'Gelap' : 'Terang'));
                },

                togglePhoneFrame() {
                    this.showFrame = !this.showFrame;
                },

                resetSimulator() {
                    // Reset to initial mock database
                    this.mails = [
                        {
                            id: 1,
                            reference_number: 'SM/0045/06/2026',
                            type: 'incoming',
                            subject: 'Undangan Rapat Koordinasi Kurikulum Baru STAI JIC',
                            body: 'Diharapkan kehadirannya dalam agenda koordinasi penyusunan silabus kurikulum Semester Ganjil 2026/2027 di Ruang Rapat Senat Utama Lantai 2.',
                            sender_name: 'Kementerian Agama Kota Surakarta',
                            sender_organization: 'Kemenag Surakarta',
                            recipient_name: 'Dr. H. Solihin, M.Ag',
                            recipient_department: 'Bagian Akademik',
                            tanggal_surat: '2026-06-12',
                            classification: 'Dinas',
                            priority: 'urgent',
                            status: 'pending',
                            attachment_name: 'Undangan_Rapat_Kurikulum_Kemenag.pdf',
                            dispositions: [
                                { id: 101, from_user_id: 1, from_user_name: 'Ahmad Fauzi, M.Pd', to_user_id: 2, to_user_name: 'Dr. H. Solihin, M.Ag', action_type: 'for_review', instruction: 'Mohon ditelaah agenda ini dan delegasikan perwakilan jika pimpinan berhalangan hadir.', status: 'pending', responded_at: null, response_notes: null }
                            ]
                        },
                        {
                            id: 2,
                            reference_number: 'SK/0098/06/2026',
                            type: 'outgoing',
                            subject: 'Surat Pengantar Pengajuan Akreditasi Program Studi Hukum Ekonomi Syariah',
                            body: 'Menindaklanjuti proses pemenuhan dokumen borang akreditasi Prodi HES, dikirimkan lampiran surat rekomendasi penjaminan mutu lembaga.',
                            sender_name: 'Dr. H. Solihin, M.Ag',
                            sender_organization: 'Bagian Akademik STAI JIC',
                            recipient_name: 'BAN-PT Jakarta',
                            recipient_department: 'Direktorat Akreditasi',
                            tanggal_surat: '2026-06-14',
                            classification: 'Penting',
                            priority: 'normal',
                            status: 'in_progress',
                            attachment_name: 'Pengantar_Akreditasi_HES.pdf',
                            dispositions: []
                        },
                        {
                            id: 3,
                            reference_number: 'SI/0012/06/2026',
                            type: 'internal',
                            subject: 'Permohonan Pengadaan Sarana Laptop Laboratorium Komputer',
                            body: 'Sehubungan dengan meningkatnya kuota penerimaan mahasiswa baru tahun akademik ini, kami mengajukan permohonan pengadaan 10 unit laptop tambahan untuk Lab Komputer Lantai 3.',
                            sender_name: 'Siti Nurhaliza, S.E',
                            sender_organization: 'Pusat Komputer JIC',
                            recipient_name: 'Ahmad Fauzi, M.Pd',
                            recipient_department: 'Administrasi Umum',
                            tanggal_surat: '2026-06-15',
                            classification: 'Dinas',
                            priority: 'normal',
                            status: 'completed',
                            attachment_name: 'Proposal_Lab_Komputer_2026.pdf',
                            dispositions: [
                                { id: 102, from_user_id: 3, from_user_name: 'Siti Nurhaliza, S.E', to_user_id: 1, to_user_name: 'Ahmad Fauzi, M.Pd', action_type: 'for_approval', instruction: 'Berikut proposal kebutuhan pengadaan komputer lab, mohon diajukan persetujuan anggaran pimpinan.', status: 'completed', responded_at: '2026-06-16 09:12', response_notes: 'Sudah disetujui pimpinan dan diteruskan ke bagian keuangan untuk pencairan dana.' }
                            ]
                        }
                    ];
                    this.reservations = [
                        { id: 1, format: 'SK/{seq}/BA-JIC/VI/2026', type: 'outgoing', quantity: 3, reason: 'Kebutuhan nomor surat keputusan kelulusan wisudawan ke-18', created_by: 'Siti Nurhaliza, S.E', status: 'pending' },
                        { id: 2, format: 'SM/{seq}/ADM-JIC/VI/2026', type: 'incoming', quantity: 1, reason: 'Booking slot nomor surat masuk resmi kemitraan Pemkot Surakarta', created_by: 'Dr. H. Solihin, M.Ag', status: 'approved' }
                    ];
                    this.notifications = [
                        { id: 1, title: 'Disposisi Baru', body: 'Anda menerima instruksi disposisi dari Ahmad Fauzi, M.Pd terkait dokumen SM/0045/06/2026', time: '1 jam yang lalu', mail_id: 1, read: false },
                        { id: 2, title: 'Reservasi Disetujui', body: 'Permohonan reservasi nomor surat masuk ADM-JIC disetujui oleh Administrator.', time: 'Kemarin', mail_id: null, read: true }
                    ];
                    this.isLoggedIn = false;
                    this.currentScreen = 'login';
                    this.selectedMail = null;
                    this.showToast('Data simulator diatur ulang.');
                },

                // Counts getters
                getMailCount(type) {
                    if (type === 'all') return this.mails.length;
                    return this.mails.filter(m => m.type === type).length;
                },
                getPendingCount() {
                    return this.mails.filter(m => m.status === 'pending').length;
                },

                // Get pending dispositions matching active user role
                getPendingDispositionsForRole() {
                    const currentUser = this.getMockUser();
                    if (!currentUser) return [];
                    return this.mails.filter(m => {
                        return m.dispositions.some(d => d.to_user_id === currentUser.id && d.status === 'pending');
                    });
                },

                getDispositionFromUser(mail) {
                    const currentUser = this.getMockUser();
                    const d = mail.dispositions.find(disp => disp.to_user_id === currentUser.id);
                    return d ? d.from_user_name : '-';
                },

                // Mail details opening
                openMailDetails(mail) {
                    this.selectedMail = mail;
                    this.tempReplyNote = '';
                },

                isMyAssignedDisposition(disp) {
                    const currentUser = this.getMockUser();
                    return currentUser && disp.to_user_id === currentUser.id;
                },

                // Submit Reply to disposition from details
                submitDispositionReply(disp) {
                    if (!this.tempReplyNote.trim()) {
                        this.showToast('Tulis catatan tanggapan terlebih dahulu.');
                        return;
                    }

                    // Update local mock db record
                    const mailRecord = this.mails.find(m => m.id === this.selectedMail.id);
                    if (mailRecord) {
                        const dispRecord = mailRecord.dispositions.find(d => d.id === disp.id);
                        if (dispRecord) {
                            dispRecord.status = 'completed';
                            dispRecord.response_notes = this.tempReplyNote;
                            dispRecord.responded_at = new Date().toISOString().replace('T', ' ').substring(0, 16);
                            
                            // Also update parent mail status if needed
                            mailRecord.status = 'completed';
                        }
                    }

                    this.tempReplyNote = '';
                    this.showToast('Tanggapan berhasil dikirim!');
                },

                markMailAsCompleted() {
                    if (!this.selectedMail) return;
                    
                    const mailRecord = this.mails.find(m => m.id === this.selectedMail.id);
                    if (mailRecord) {
                        mailRecord.status = 'completed';
                        this.selectedMail.status = 'completed';
                    }
                    this.showToast('Status surat ditandai Selesai.');
                },

                // Create Mail
                submitNewMail() {
                    const typeCode = this.newMailForm.type === 'incoming' ? 'SM' : (this.newMailForm.type === 'outgoing' ? 'SK' : 'SI');
                    const sequence = String(this.mails.length + 1).padStart(4, '0');
                    const refNo = `${typeCode}/${sequence}/06/2026`;

                    const newObj = {
                        id: this.mails.length + 1,
                        reference_number: refNo,
                        type: this.newMailForm.type,
                        subject: this.newMailForm.subject,
                        body: this.newMailForm.body,
                        sender_name: this.newMailForm.sender_name,
                        sender_organization: '',
                        recipient_name: this.newMailForm.recipient_name,
                        recipient_department: '',
                        tanggal_surat: this.newMailForm.tanggal_surat,
                        classification: this.newMailForm.classification,
                        priority: 'normal',
                        status: 'pending',
                        attachment_name: 'Lampiran_Dokumen_Baru.pdf',
                        dispositions: []
                    };

                    this.mails.unshift(newObj);
                    
                    // Reset Form
                    this.newMailForm = {
                        type: 'incoming',
                        subject: '',
                        sender_name: '',
                        recipient_name: '',
                        body: '',
                        tanggal_surat: new Date().toISOString().split('T')[0],
                        classification: 'Dinas'
                    };

                    this.createMailFormOpen = false;
                    this.showToast('Surat berhasil disimpan!');
                },

                // Create Disposition Sheet Actions
                openCreateDisposition() {
                    this.newDispForm.to_user_ids = [];
                    this.newDispForm.instruction = '';
                    this.createDispositionFormOpen = true;
                },

                toggleDispRecipient(id) {
                    if (this.newDispForm.to_user_ids.includes(id)) {
                        this.newDispForm.to_user_ids = this.newDispForm.to_user_ids.filter(uid => uid !== id);
                    } else {
                        this.newDispForm.to_user_ids.push(id);
                    }
                },

                submitNewDisposition() {
                    if (this.newDispForm.to_user_ids.length === 0) {
                        this.showToast('Pilih minimal satu pegawai penerima!');
                        return;
                    }

                    const mailRecord = this.mails.find(m => m.id === this.selectedMail.id);
                    if (mailRecord) {
                        this.newDispForm.to_user_ids.forEach(uid => {
                            const recipientUser = this.users.find(u => u.id === uid);
                            const currentUser = this.getMockUser();

                            const newDispObj = {
                                id: Date.now() + Math.random(),
                                from_user_id: currentUser.id,
                                from_user_name: currentUser.name,
                                to_user_id: uid,
                                to_user_name: recipientUser ? recipientUser.name : 'Pegawai',
                                action_type: this.newDispForm.action_type,
                                instruction: this.newDispForm.instruction,
                                status: 'pending',
                                responded_at: null,
                                response_notes: null
                            };
                            mailRecord.dispositions.push(newDispObj);

                            // Send push notification if it matches targeted role simulation
                            if (recipientUser && recipientUser.role !== currentUser.role) {
                                this.notifications.unshift({
                                    id: Date.now(),
                                    title: 'Disposisi Baru',
                                    body: `Anda menerima instruksi disposisi baru dari ${currentUser.name}`,
                                    time: 'Baru saja',
                                    mail_id: mailRecord.id,
                                    read: false
                                });
                            }
                        });

                        mailRecord.status = 'in_progress';
                        this.selectedMail.status = 'in_progress';
                    }

                    this.createDispositionFormOpen = false;
                    this.showToast('Disposisi berhasil didelegasikan!');
                },

                // Create Reservation
                submitNewReservation() {
                    const currentUser = this.getMockUser();
                    const typeCode = this.newResForm.type === 'incoming' ? 'SM' : 'SK';
                    const format = `${typeCode}/{seq}/ADM-JIC/VI/2026`;

                    const newRes = {
                        id: this.reservations.length + 1,
                        format: format,
                        type: this.newResForm.type,
                        quantity: this.newResForm.quantity,
                        reason: this.newResForm.reason,
                        created_by: currentUser.name,
                        status: 'pending'
                    };

                    this.reservations.unshift(newRes);
                    this.createReservationFormOpen = false;
                    
                    // Reset Form
                    this.newResForm = {
                        type: 'outgoing',
                        quantity: 1,
                        reason: ''
                    };

                    this.showToast('Reservasi nomor surat diajukan!');
                },

                // Approve / Reject Reservation (Admin only)
                approveReservation(id) {
                    const res = this.reservations.find(r => r.id === id);
                    if (res) {
                        res.status = 'approved';
                        this.showToast('Reservasi disetujui.');
                    }
                },
                rejectReservation(id) {
                    const res = this.reservations.find(r => r.id === id);
                    if (res) {
                        res.status = 'rejected';
                        this.showToast('Reservasi ditolak.');
                    }
                },

                // Filter logic for mail screen list
                getFilteredMails() {
                    return this.mails.filter(mail => {
                        // Type tab filter
                        if (mail.type !== this.activeMailTypeTab) return false;

                        // Status filter
                        if (this.activeStatusFilter !== 'all') {
                            if (mail.status !== this.activeStatusFilter) return false;
                        }

                        // Search Query
                        if (this.searchQuery.trim() !== '') {
                            const query = this.searchQuery.toLowerCase();
                            const sub = mail.subject.toLowerCase();
                            const ref = mail.reference_number.toLowerCase();
                            return sub.includes(query) || ref.includes(query);
                        }

                        return true;
                    });
                },

                // Utility Label helpers
                getMailStatusLabel(status) {
                    const labels = {
                        draft: 'Draft',
                        pending: 'Pending',
                        in_progress: 'Proses',
                        completed: 'Selesai',
                        archived: 'Arsip'
                    };
                    return labels[status] || status;
                },
                getMailStatusStyle(status) {
                    const styles = {
                        pending: 'bg-amber-50 text-amber-600 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/40',
                        in_progress: 'bg-blue-50 text-blue-600 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/40',
                        completed: 'bg-green-50 text-green-600 border-green-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40',
                        archived: 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-zinc-800 dark:text-zinc-450 dark:border-zinc-750'
                    };
                    return styles[status] || 'bg-slate-50 text-slate-500';
                },
                getMailTypeLabel(type) {
                    const labels = {
                        incoming: 'Surat Masuk',
                        outgoing: 'Surat Keluar',
                        internal: 'Surat Internal'
                    };
                    return labels[type] || type;
                },
                getDispositionStatusLabel(status) {
                    const labels = {
                        pending: 'Menunggu',
                        completed: 'Selesai'
                    };
                    return labels[status] || status;
                },
                getDispositionStatusStyle(status) {
                    if (status === 'completed') return 'bg-emerald-100/50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30';
                    return 'bg-amber-100/50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/30';
                },
                getDispositionActionLabel(action) {
                    const labels = {
                        for_review: 'Untuk Ditelaah',
                        for_action: 'Untuk Ditindaklanjuti',
                        for_information: 'Untuk Diketahui',
                        for_approval: 'Untuk Disetujui',
                        coordinate: 'Koordinasikan'
                    };
                    return labels[action] || 'Lainnya';
                },
                getReservationStatusLabel(status) {
                    const labels = {
                        pending: 'Menunggu',
                        approved: 'Disetujui',
                        rejected: 'Ditolak'
                    };
                    return labels[status] || status;
                },
                getReservationStatusStyle(status) {
                    if (status === 'approved') return 'bg-emerald-50 text-emerald-600 border-emerald-200';
                    if (status === 'rejected') return 'bg-red-50 text-red-650 border-red-200';
                    return 'bg-amber-50 text-amber-600 border-amber-200';
                },

                // Interactive Stats Redirection
                openMailsTab(type) {
                    this.activeMailTypeTab = type;
                    this.activeStatusFilter = 'all';
                    this.currentScreen = 'mails';
                },
                openMailsWithFilter(status) {
                    this.activeStatusFilter = status;
                    this.currentScreen = 'mails';
                },

                openNotificationsPage() {
                    this.currentScreen = 'notifications';
                    // Mark all notifications as read
                    this.notifications.forEach(n => n.read = true);
                },

                openNotification(notif) {
                    notif.read = true;
                    if (notif.mail_id) {
                        const m = this.mails.find(mail => mail.id === notif.mail_id);
                        if (m) {
                            this.openMailDetails(m);
                        }
                    } else {
                        this.currentScreen = 'reservations';
                    }
                    this.activeInAppNotification = null;
                },

                // Simulate incoming mail push alert
                triggerNewMailNotification() {
                    if (!this.pushNotificationsActive) {
                        console.log('Notifications blocked by user settings.');
                        return;
                    }

                    // Send mock new incoming mail record
                    const mockNotification = {
                        id: Date.now(),
                        title: 'Disposisi Surat Mendesak',
                        body: 'Dr. H. Solihin mendisposisikan surat undangan rapat BAN-PT untuk Anda tindaklanjuti.',
                        time: 'Baru saja',
                        mail_id: 1,
                        read: false
                    };

                    this.notifications.unshift(mockNotification);
                    this.activeInAppNotification = mockNotification;

                    // Clear banner after 6 seconds
                    setTimeout(() => {
                        if (this.activeInAppNotification === mockNotification) {
                            this.activeInAppNotification = null;
                        }
                    }, 6000);
                },

                // Toast success popups inside device screen
                showToast(msg) {
                    // Create an in-app banner for toast alerts
                    const toast = document.createElement('div');
                    toast.className = 'absolute bottom-20 left-1/2 -translate-x-1/2 bg-slate-900/90 text-white text-[11px] font-semibold py-2 px-4 rounded-full shadow-lg z-[120] whitespace-nowrap animate-toast pointer-events-none transition-all';
                    toast.textContent = msg;
                    
                    const screenElement = document.querySelector('.phone-shell') || document.body;
                    screenElement.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translate(-50%, 10px)';
                        setTimeout(() => toast.remove(), 300);
                    }, 2500);
                },

                downloadMockAttachment() {
                    this.showToast('Mengunduh lampiran surat (PDF Mockup)...');
                    setTimeout(() => {
                        this.showToast('Unduhan selesai: Dokumen_Lampiran_STAI_JIC.pdf');
                    }, 1500);
                }
            }
        }
    </script>
</body>
</html>
