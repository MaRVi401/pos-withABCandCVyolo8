<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS System & Smart Optimization Engine</title>
    <link rel="icon" type="image/x-icon"
        href="https://i.pinimg.com/736x/90/0c/9e/900c9e431963432cb0f5fbe7c5f5af91.jpg">

    <!-- Fonts & Tailwind CSS CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white" x-data="welcomeApp()" x-init="checkAuth()">

    <!-- Header / Navbar -->
    <header class="fixed top-0 inset-x-0 z-50 bg-slate-900/80 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo Brand -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-extrabold text-white text-xl shadow-lg shadow-indigo-500/30">
                    🐝
                </div>
                <div>
                    <span class="font-extrabold text-lg tracking-tight text-white block leading-none">POS<span class="text-indigo-400">SMART</span></span>
                    <span class="text-[10px] font-semibold text-slate-400 tracking-wider uppercase">ABC Engine Inside</span>
                </div>
            </div>

            <!-- Navigation Buttons Dynamic -->
            <div class="flex items-center gap-4">
                <template x-if="isLoggedIn">
                    <a href="{{ url('/admin/dashboard') }}" 
                        class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                        <span>📊 Dashboard Admin</span>
                        <span>&rarr;</span>
                    </a>
                </template>

                <template x-if="!isLoggedIn">
                    <a href="{{ url('/login') }}" 
                        class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                        <span>🔑 Login Sistem</span>
                        <span>&rarr;</span>
                    </a>
                </template>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 md:pt-40 md:pb-28 overflow-hidden">
        <!-- Glow Effect Background -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-indigo-600/20 blur-[120px] rounded-full pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-8">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-800/80 border border-slate-700 text-indigo-400 text-xs font-bold uppercase tracking-wider">
                <span>🤖 Artificial Bee Colony (ABC) Integrated</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white leading-tight max-w-4xl mx-auto">
                Sistem Kasir Modern & Optimasi Paket Promo <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">Berbasis AI</span>
            </h1>

            <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
                Kelola penjualan transaksi kasir, inventaris barang, penyesuaian stok, serta analisis *bundling* produk pintar untuk memaksimalkan profit secara otomatis.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
                <template x-if="isLoggedIn">
                    <a href="{{ url('/admin/pos') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-sm px-8 py-3.5 rounded-xl transition-all shadow-xl shadow-indigo-600/30">
                        🚀 Buka Mesin Kasir POS
                    </a>
                </template>
                <template x-if="!isLoggedIn">
                    <a href="{{ url('/login') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-sm px-8 py-3.5 rounded-xl transition-all shadow-xl shadow-indigo-600/30">
                        Masuk Ke Aplikasi Kasir
                    </a>
                </template>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-slate-950/50 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center space-y-3 mb-12">
                <h2 class="text-2xl font-bold text-white">Fitur Unggulan Sistem POS</h2>
                <p class="text-slate-400 text-xs sm:text-sm">Dilengkapi modul lengkap untuk menunjang operasional toko ritel & grosir</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-slate-800/50 border border-slate-700/80 p-6 rounded-2xl space-y-3">
                    <div class="w-12 h-12 bg-indigo-500/10 text-indigo-400 rounded-xl flex items-center justify-center text-2xl font-bold">
                        🛒
                    </div>
                    <h3 class="text-base font-bold text-white">Checkout Transaksi Atomik</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Proses kasir super cepat dengan validasi stok otomatis (*stock guard*), dukungan potongan promo bundling, serta cetak struk.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-slate-800/50 border border-slate-700/80 p-6 rounded-2xl space-y-3">
                    <div class="w-12 h-12 bg-purple-500/10 text-purple-400 rounded-xl flex items-center justify-center text-2xl font-bold">
                        🐝
                    </div>
                    <h3 class="text-base font-bold text-white">AI Bundling ABC Engine</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Algoritma Artificial Bee Colony menganalisis produk *slow-moving* & profit untuk menghasilkan kombinasi diskon terbaik.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-slate-800/50 border border-slate-700/80 p-6 rounded-2xl space-y-3">
                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-400 rounded-xl flex items-center justify-center text-2xl font-bold">
                        📊
                    </div>
                    <h3 class="text-base font-bold text-white">Laporan & Export File</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Pantau laporan omset harian dan unduh langsung berkas Laporan Penjualan rapi berbentuk PDF dan Excel (.xlsx).
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 border-t border-slate-800 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} POS System & Artificial Bee Colony Engine. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function welcomeApp() {
            return {
                isLoggedIn: false,
                checkAuth() {
                    const token = localStorage.getItem('auth_token');
                    this.isLoggedIn = !!token;
                }
            }
        }
    </script>
</body>
</html>