<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="E-Office STAI JIC Surakarta — Sistem Manajemen Surat Elektronik">
    <title>@yield('title', 'E-Office') — STAI JIC Surakarta</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons (Phosphor) -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>

    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts / Styles (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans flex h-screen overflow-hidden selection:bg-secondary selection:text-white">

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/50 z-40 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="w-64 bg-white border-r border-gray-200 flex flex-col fixed inset-y-0 left-0 z-50 transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 ease-in-out shadow-lg md:shadow-none">
        
        <!-- Brand / Logo -->
        <div class="h-16 flex items-center px-6 border-b border-gray-100">
            <img src="{{ asset('Logo_STAIJIC.png') }}" alt="Logo STAI JIC Surakarta" class="h-10 w-auto mr-3" style="height: 40px; width: auto; max-width: 100%; object-fit: contain;" onerror="this.outerHTML='<div class=\'h-10 w-10 rounded bg-primary flex items-center justify-center text-white font-bold mr-3\'>JIC</div>\'">
            <div>
                <h1 class="font-bold text-gray-800 text-lg leading-tight">E-Office</h1>
                <p class="text-[10px] text-gray-500 font-medium tracking-wider">STAI JIC Surakarta</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <div class="px-3 mb-2 mt-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">Menu Utama</div>
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('dashboard') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-squares-four text-lg {{ request()->routeIs('dashboard') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Dashboard
            </a>

            <div class="px-3 mb-2 mt-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">Surat & Arsip</div>
            
            <a href="{{ route('mails.incoming.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('mails.incoming.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-envelope-simple-open text-lg {{ request()->routeIs('mails.incoming.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Surat Masuk
                @if(($sidebarCounts['pending_incoming'] ?? 0) > 0)
                    <span class="nav-badge">{{ $sidebarCounts['pending_incoming'] }}</span>
                @endif
            </a>

            <a href="{{ route('mails.outgoing.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('mails.outgoing.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-paper-plane-right text-lg {{ request()->routeIs('mails.outgoing.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Surat Keluar
            </a>

            <a href="{{ route('mails.internal.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('mails.internal.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-arrows-left-right text-lg {{ request()->routeIs('mails.internal.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Surat Internal
            </a>

            <a href="{{ route('mails.archive.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('mails.archive.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-archive text-lg {{ request()->routeIs('mails.archive.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Arsip Surat
            </a>

            <div class="px-3 mb-2 mt-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">Tugas & Alur</div>

            <a href="{{ route('dispositions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('dispositions.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-git-branch text-lg {{ request()->routeIs('dispositions.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Disposisi
                @if(($sidebarCounts['pending_dispositions'] ?? 0) > 0)
                    <span class="nav-badge">{{ $sidebarCounts['pending_dispositions'] }}</span>
                @endif
            </a>

            <a href="{{ route('number_reservations.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('number_reservations.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-hash text-lg {{ request()->routeIs('number_reservations.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Reservasi Nomor
                @if(auth()->user()->isAdmin() && ($sidebarCounts['pending_reservations'] ?? 0) > 0)
                    <span class="nav-badge">{{ $sidebarCounts['pending_reservations'] }}</span>
                @endif
            </a>

            <div class="px-3 mb-2 mt-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">Pusat Informasi</div>
            
            <a href="{{ route('administrasi.tata-arsip.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('administrasi.tata-arsip.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-books text-lg {{ request()->routeIs('administrasi.tata-arsip.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Tata Arsip & SOP
            </a>


            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
            <div class="px-3 mb-2 mt-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">Administrasi Sistem</div>
            
            <a href="{{ route('administrasi.nomor-surat.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('administrasi.nomor-surat.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-hash text-lg {{ request()->routeIs('administrasi.nomor-surat.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Nomor Surat
            </a>
            @endif

            @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('administrasi.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('administrasi.users.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-users text-lg {{ request()->routeIs('administrasi.users.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Kelola Pengguna
            </a>

            <div class="px-3 mb-2 mt-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">Master Data</div>
            <a href="{{ route('administrasi.units.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('administrasi.units.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-buildings text-lg {{ request()->routeIs('administrasi.units.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Kelola Unit
            </a>
            <a href="{{ route('administrasi.document-types.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors font-medium text-sm {{ request()->routeIs('administrasi.document-types.*') ? 'bg-green-50 text-primary border-l-4 border-secondary' : 'text-gray-600 hover:bg-gray-50 hover:text-primary' }}">
                <i class="ph ph-files text-lg {{ request()->routeIs('administrasi.document-types.*') ? 'text-secondary' : 'text-gray-400' }}"></i> 
                Kelola Jenis Surat
            </a>
            @endif
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            <div class="text-xs text-center text-gray-400 font-medium">
                &copy; {{ date('Y') }} STAI JIC Surakarta
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- TOP BAR -->
        <header class="h-16 bg-primary text-white flex items-center justify-between px-4 sm:px-6 shadow-sm z-10 shrink-0">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="md:hidden p-1 rounded-md hover:bg-white/10 transition">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <h2 class="font-semibold text-lg hidden sm:block tracking-wide">@yield('page-title', 'Dashboard')</h2>
            </div>

            <div class="flex items-center gap-4">
                @yield('header-actions')
                
                <!-- User Profile Dropdown -->
                <div class="relative group">
                    <button class="flex items-center gap-3 focus:outline-none pl-4 border-l border-white/20">
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-semibold leading-tight">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-[11px] text-green-100">{{ auth()->user()->role_label ?? 'Administrator' }}</div>
                        </div>
                        <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=User&background=8AC249&color=fff' }}" alt="Avatar" class="h-9 w-9 rounded-full object-cover border-2 border-white/20">
                    </button>
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-100 py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                <i class="ph ph-sign-out text-lg"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-gray-50">
            
            @if(session('success'))
                <div class="alert alert-success animate-[fadeIn_0.3s_ease-out]">
                    <i class="ph ph-check-circle text-xl mt-0.5"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger animate-[fadeIn_0.3s_ease-out]">
                    <i class="ph ph-x-circle text-xl mt-0.5"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger animate-[fadeIn_0.3s_ease-out]">
                    <i class="ph ph-warning-circle text-xl mt-0.5"></i>
                    <div>
                        <strong class="block mb-1">Terdapat kesalahan input:</strong>
                        <ul class="list-disc pl-5 space-y-1 text-sm">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
            
        </main>
    </div>

    <!-- Scripts -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                el.style.transition = 'opacity 0.5s, transform 0.5s';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    </script>
    
    @yield('scripts')
</body>
</html>
