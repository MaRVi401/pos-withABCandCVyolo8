<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - POS System')</title>
    
    <!-- SCRIPT CEK TOKEN AUTHENTICATION SEBELUM MEMATUHI LAYOUT -->
    <script>
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '/login';
        }
    </script>

    <!-- Tailwind CSS & Alpine.js CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans antialiased" x-data="adminLayout()">

    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR PANEL ADMIN -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between hidden md:flex shrink-0">
            <div>
                <!-- Brand Logo / Header Sidebar -->
                <div class="h-16 flex items-center justify-center border-b border-slate-800 px-4">
                    <h1 class="text-xl font-bold tracking-wider text-indigo-400">POS ECOSYSTEM</h1>
                </div>

                <!-- Navigation Menu Sidebar -->
                <nav class="mt-6 px-4 space-y-1">
                    <a href="{{ url('/admin/dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium bg-indigo-600 rounded-lg text-white shadow-sm">
                        <span>Dashboard</span>
                    </a>

                    <div class="pt-4 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Katalog & Stok</div>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 rounded-lg transition-colors">
                        <span>Kategori Produk</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 rounded-lg transition-colors">
                        <span>Data Produk</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 rounded-lg transition-colors">
                        <span>Penyesuaian Stok</span>
                    </a>

                    <div class="pt-4 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Transaksi & POS</div>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 rounded-lg transition-colors">
                        <span>Kasir (POS)</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 rounded-lg transition-colors">
                        <span>Laporan Penjualan</span>
                    </a>

                    <div class="pt-4 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Smart Optimasi</div>
                    <a href="#" class="flex items-center justify-between px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 rounded-lg transition-colors">
                        <span>Smart Bundling (ABC)</span>
                        <span class="bg-indigo-500 text-xs px-2 py-0.5 rounded-full text-white font-bold">AI</span>
                    </a>
                </nav>
            </div>

            <!-- Profile Info & Logout Footer Sidebar -->
            <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                <div class="text-sm overflow-hidden">
                    <p class="font-semibold text-white truncate" x-text="user.name || 'Ahmad Yassin'"></p>
                    <p class="text-xs text-slate-400 truncate" x-text="user.role_name || 'Admin Store'"></p>
                </div>
                <button @click="logout()" title="Keluar / Logout" class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            
            <!-- TOP NAVBAR -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shrink-0">
                <h2 class="text-lg font-semibold text-gray-800">@yield('page_title', 'Dashboard Panel')</h2>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-500 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="hidden sm:inline">System Online</span>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>

        <!-- FLOATING CHATBOT WIDGET -->
        <div class="fixed bottom-6 right-6 z-50">
            <button @click="chatOpen = !chatOpen" class="bg-indigo-600 hover:bg-indigo-700 text-white p-4 rounded-full shadow-2xl flex items-center justify-center transition-all hover:scale-105">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
            </button>

            <div x-show="chatOpen" x-transition class="absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="bg-indigo-600 text-white p-4 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-sm">AI Operational Assistant</h3>
                        <p class="text-xs text-indigo-200">Asisten cerdas pencarian stok & transaksi</p>
                    </div>
                    <button @click="chatOpen = false" class="text-indigo-200 hover:text-white font-bold text-lg">&times;</button>
                </div>

                <div class="p-4 h-64 overflow-y-auto bg-gray-50 text-xs space-y-3">
                    <div class="bg-indigo-100 text-indigo-900 p-3 rounded-xl max-w-[85%] leading-relaxed">
                        Halo Admin! Ada yang bisa saya bantu cek seputar stok barang atau ringkasan penjualan hari ini?
                    </div>
                </div>

                <div class="p-3 border-t bg-white flex gap-2">
                    <input type="text" placeholder="Tanyakan sesuatu ke AI..." class="flex-1 text-xs border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <button class="bg-indigo-600 text-white text-xs px-3 py-2 rounded-lg font-medium hover:bg-indigo-700">Kirim</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function adminLayout() {
            return {
                chatOpen: false,
                user: JSON.parse(localStorage.getItem('user_info') || '{}'),
                async logout() {
                    // Alert Konfirmasi sebelum keluar
                    if (confirm("Apakah Anda yakin ingin keluar dari sistem POS?")) {
                        const token = localStorage.getItem('auth_token');

                        try {
                            // Panggil API Logout untuk revoking token Sanctum di database
                            await fetch('/api/v1/auth/logout', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'Authorization': `Bearer ${token}`
                                }
                            });
                        } catch (e) {
                            console.error("Gagal revoking token di API:", e);
                        }

                        // Alert Notifikasi Berhasil Logout
                        alert("Anda telah berhasil logout.");

                        // Hapus data autentikasi dari LocalStorage
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user_info');

                        // Redirect kembali ke Halaman Login
                        window.location.href = '/login';
                    }
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>