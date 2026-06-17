<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login E-Office - STAI JIC Surakarta</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900 min-h-screen">

    <div class="min-h-screen flex flex-col lg:flex-row w-full overflow-x-hidden">
        
        <!-- Left Branding Column (Hidden on mobile/tablet, visible on desktop >= 1024px) -->
        <div class="hidden lg:flex lg:w-[55%] bg-[#1a5c44] text-white flex-col justify-between p-12 relative overflow-hidden lg:border-r lg:border-[#124130]">
            <!-- Pattern Background -->
            <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="islamic-pattern" width="80" height="80" patternUnits="userSpaceOnUse">
                            <path d="M40 0 L80 40 L40 80 L0 40 Z M0 0 L40 40 L0 80 Z M80 0 L40 40 L80 80 Z" fill="none" stroke="#ffffff" stroke-width="1" />
                            <circle cx="40" cy="40" r="12" fill="none" stroke="#ffffff" stroke-width="1" />
                            <path d="M40 15 L40 65 M15 40 L65 40" fill="none" stroke="#ffffff" stroke-width="1" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#islamic-pattern)" />
                </svg>
            </div>

            <!-- Top decorative/branding tag -->
            <div class="z-10 flex items-center gap-2">
                <div class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-xs font-semibold tracking-widest uppercase text-emerald-200/80">E-Office STAI JIC Surakarta</span>
            </div>

            <!-- Main Branding Content -->
            <div class="z-10 flex flex-col items-start max-w-lg my-auto">
                <div class="p-4 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 mb-8 shadow-inner">
                    <img src="{{ asset('Logo_STAIJIC.png') }}" alt="Logo STAI JIC Surakarta" class="h-20 w-auto object-contain">
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-white mb-4 leading-tight">
                    STAI JIC Surakarta
                </h1>
                <div class="h-1 w-16 bg-emerald-400 rounded-full mb-6"></div>
                <p class="text-emerald-100/90 text-lg font-normal leading-relaxed">
                    Platform E-Office untuk tata kelola persuratan elektronik, disposisi, dan administrasi kampus yang modern, cepat, dan terintegrasi.
                </p>
            </div>

            <!-- Footer of left side -->
            <div class="z-10 text-xs text-emerald-200/60 font-medium">
                &copy; {{ date('Y') }} STAI JIC Surakarta. All rights reserved.
            </div>
        </div>
        
        <!-- Right Form Column (100% on mobile, 45% on desktop >= 1024px) -->
        <div class="w-full lg:w-[45%] bg-gray-50 flex items-center justify-center p-6 sm:p-12 relative min-h-screen lg:min-h-0">
            <div class="w-full max-w-[420px] bg-white rounded-2xl shadow-xl border border-gray-100 p-8 sm:p-10 transition-all duration-300 hover:shadow-2xl">
                
                <div class="mb-6 text-center">
                    <img
                        src="{{ asset('Logo_STAIJIC.png') }}"
                        alt="Logo STAI JIC Surakarta"
                        class="max-h-[80px] w-auto mx-auto object-contain"
                    >
                </div>

                <h2 class="text-[22px] font-bold text-gray-900 mb-6 text-center tracking-tight">
                    Masuk ke akun Anda
                </h2>

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-[14px] font-medium text-gray-700 mb-[6px]">
                            Username
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                            @class([
                                'w-full h-[44px] px-3.5 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#1a5c44]/20 focus:border-[#1a5c44] transition-all text-sm',
                                'border-[#d1d5db]' => !$errors->has('email'),
                                'border-red-500 focus:ring-red-500/20 focus:border-red-500' => $errors->has('email'),
                            ])
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-[6px]">
                            <label for="password" class="block text-[14px] font-medium text-gray-700">
                                Password
                            </label>
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            @class([
                                'w-full h-[44px] px-3.5 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#1a5c44]/20 focus:border-[#1a5c44] transition-all text-sm',
                                'border-[#d1d5db]' => !$errors->has('password'),
                                'border-red-500 focus:ring-red-500/20 focus:border-red-500' => $errors->has('password'),
                            ])
                        >
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 text-right">
                            <a href="#" class="text-xs font-semibold text-[#1a5c44] hover:text-[#113d2d] hover:underline transition-colors">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-[#1a5c44] hover:bg-[#113d2d] active:bg-[#0c2c20] text-white font-semibold rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1a5c44] transition-colors cursor-pointer shadow-sm text-sm"
                    >
                        Login
                    </button>
                </form>
            </div>
        </div>
        
    </div>

</body>
</html>
