@extends('layouts.admin')

@section('title', 'Dashboard Admin - POS Ecosystem')
@section('page_title', 'Ringkasan Toko & REST API')

@section('content')
    <div class="space-y-6" x-data="dashboardApp()" x-init="initDashboard()">

        <!-- Header Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Produk API</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1" x-text="products.length + ' Item'">0 Item</h3>
                </div>
                <div
                    class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 font-bold text-lg">
                    📦
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status REST API</p>
                    <h3 class="text-xl font-bold text-emerald-600 mt-1">Terhubung</h3>
                </div>
                <div
                    class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 font-bold text-lg">
                    ⚡
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Algoritma ABC</p>
                    <h3 class="text-xl font-bold text-indigo-600 mt-1">Ready</h3>
                </div>
                <div
                    class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 font-bold text-lg">
                    🐝
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">User Logged-In</p>
                    <h3 class="text-sm font-bold text-gray-800 mt-1" x-text="userInfo.name || 'Admin'">Admin</h3>
                </div>
                <div
                    class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-600 font-bold text-lg">
                    👤
                </div>
            </div>
        </div>

        <!-- Quick Action / Banner Smart Optimasi ABC -->
        <div
            class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 text-center md:text-left">
                <span
                    class="inline-block bg-amber-400 text-slate-900 text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider">
                    🐝 Artificial Bee Colony
                </span>
                <h2 class="text-xl font-bold">Optimasi Paket Bundling Otomatis</h2>
                <p class="text-indigo-200 text-xs max-w-xl leading-relaxed">
                    Jalankan simulasi lebah pekerja & pengamat untuk memasangkan produk stok menumpuk (slow-moving) dengan
                    produk terlaris (fast-moving) guna mendongkrak omset.
                </p>
            </div>
            <button @click="runAbcOptimization()" :disabled="abcLoading"
                class="bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold text-sm px-5 py-3 rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5 shrink-0 flex items-center gap-2">
                <span x-show="!abcLoading">🚀 Jalankan Optimasi Lebah</span>
                <span x-show="abcLoading">🐝 Memproses Swarm Intelligence...</span>
            </button>
        </div>

        <!-- Live CCTV Visitor Counter (YOLOv8 & Flask Integration) -->
        <div class="bg-slate-900 text-white rounded-2xl shadow-xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
                <div class="flex items-center gap-3">
                    <span class="text-xl">📹</span>
                    <div>
                        <h3 class="font-bold text-sm text-cyan-400">Live CCTV Visitor Counter (YOLOv8)</h3>
                        <p class="text-xs text-slate-400">Deteksi & Tracking Pengunjung Real-time via Python Flask Stream
                        </p>
                    </div>
                </div>
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-950 text-emerald-400 border border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> LIVE
                </span>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                    <!-- Stream Video Feed dari Flask Service -->
                    <div class="lg:col-span-2">
                        <div
                            class="relative bg-black rounded-xl overflow-hidden border border-slate-800 min-h-[300px] flex items-center justify-center shadow-inner">
                            <!-- Ditambahkan id="yolo-stream-img" -->
                            <img id="yolo-stream-img" src="http://localhost:5000/video_feed" alt="CCTV Stream Feed"
                                class="w-full h-auto object-cover rounded-xl"
                                onerror="this.onerror=null; this.src='https://via.placeholder.com/640x360/0f172a/94a3b8?text=CCTV+Offline+/+Server+Flask+Belum+Jalan';" />
                        </div>
                    </div>

                    <!-- Card Stats Total Pengunjung Real-time -->
                    <div class="space-y-4">
                        <div
                            class="p-5 text-center bg-gradient-to-br from-cyan-600 to-blue-700 rounded-xl shadow-lg border border-cyan-500/30">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-cyan-100">Pengunjung Terdeteksi
                            </p>
                            <!-- Ditambahkan x-text="visitorCount" agar reaktif dengan Alpine.js -->
                            <h1 class="text-5xl font-extrabold text-white mt-2" x-text="visitorCount">0</h1>
                            <p class="text-[11px] text-cyan-200 mt-1">Orang Dalam Area CCTV</p>
                        </div>

                        <div
                            class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 text-xs space-y-2 text-slate-300">
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                <span class="text-slate-400">Status Flask:</span>
                                <span class="font-bold text-emerald-400">Connected (Port 5000)</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                <span class="text-slate-400">Model AI:</span>
                                <span class="font-semibold text-slate-200">YOLOv8 Nano (pt)</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Target Class:</span>
                                <span class="font-semibold text-slate-200">Person (Class ID 0)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Produk dari REST API -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div
                class="p-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Katalog Produk (Diambil dari `/api/v1/products`)</h3>
                    <p class="text-xs text-gray-500">Membaca data MySQL melalui REST API & Sanctum Token</p>
                </div>
                <button @click="fetchProducts()"
                    class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-3.5 py-2 rounded-lg transition-colors flex items-center gap-2 self-start sm:self-auto">
                    <span>🔄</span> Refresh Data
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Foto</th>
                            <th class="px-6 py-3.5">SKU</th>
                            <th class="px-6 py-3.5">Nama Produk</th>
                            <th class="px-6 py-3.5">Harga Beli</th>
                            <th class="px-6 py-3.5">Harga Jual</th>
                            <th class="px-6 py-3.5">Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="item in products" :key="item.product_id">
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4">
                                    <img :src="item.image_url || 'https://via.placeholder.com/60?text=No+Img'"
                                        :alt="item.product_name"
                                        class="w-10 h-10 object-cover rounded-lg border border-gray-200 shadow-sm">
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-indigo-600" x-text="item.sku"></td>
                                <td class="px-6 py-4 font-medium text-gray-900" x-text="item.product_name"></td>
                                <td class="px-6 py-4" x-text="'Rp ' + Number(item.buy_price).toLocaleString('id-ID')"></td>
                                <td class="px-6 py-4 font-semibold text-gray-900"
                                    x-text="'Rp ' + Number(item.sell_price).toLocaleString('id-ID')"></td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                        :class="item.stock_quantity <= item.min_stock_alert ?
                                            'bg-red-100 text-red-800 border border-red-200' :
                                            'bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                        x-text="item.stock_quantity + ' Unit'">
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="loading">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Memuat data dari REST API...</td>
                        </tr>
                        <tr x-show="!loading && products.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data produk tersimpan
                                di database.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL HASIL REKOMENDASI ABC -->
        <div x-show="abcModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl border border-gray-200 overflow-hidden transform transition-all"
                @click.away="abcModalOpen = false">
                <div class="bg-indigo-900 text-white p-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🐝</span>
                        <div>
                            <h3 class="font-bold text-base">Hasil Rekomendasi Artificial Bee Colony</h3>
                            <p class="text-xs text-indigo-200">Solusi Bundling Diskon Optimal</p>
                        </div>
                    </div>
                    <button @click="abcModalOpen = false"
                        class="text-indigo-200 hover:text-white font-bold text-xl">&times;</button>
                </div>

                <div class="p-6 space-y-5" x-if="abcResult">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <span
                                class="text-[10px] font-bold uppercase text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Fast
                                Moving</span>
                            <p class="font-bold text-gray-800 text-sm mt-2"
                                x-text="abcResult?.fast_product?.product_name"></p>
                            <p class="text-xs text-gray-500 mt-1"
                                x-text="'Stok: ' + abcResult?.fast_product?.stock + ' | Rp ' + Number(abcResult?.fast_product?.sell_price).toLocaleString('id-ID')">
                            </p>
                        </div>

                        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl">
                            <span
                                class="text-[10px] font-bold uppercase text-amber-700 bg-amber-100 px-2 py-0.5 rounded">Slow
                                Moving</span>
                            <p class="font-bold text-gray-800 text-sm mt-2"
                                x-text="abcResult?.slow_product?.product_name"></p>
                            <p class="text-xs text-gray-500 mt-1"
                                x-text="'Stok: ' + abcResult?.slow_product?.stock + ' | Rp ' + Number(abcResult?.slow_product?.sell_price).toLocaleString('id-ID')">
                            </p>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Harga Normal Total:</span>
                            <span class="font-bold text-slate-800"
                                x-text="'Rp ' + Number(abcResult?.normal_price).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Diskon Lebah (<span x-text="abcResult?.discount_rate"></span>):</span>
                            <span class="font-bold text-red-600"
                                x-text="'- Rp ' + Number(abcResult?.discount_amount).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-slate-200 font-bold text-slate-900">
                            <span>Harga Paket Promo:</span>
                            <span class="text-indigo-600"
                                x-text="'Rp ' + Number(abcResult?.promo_price).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-center">
                        <p class="text-xs text-indigo-700">Estimasi Margin Profit yang Tetap Diperoleh:</p>
                        <p class="text-lg font-extrabold text-indigo-900 mt-0.5"
                            x-text="'Rp ' + Number(abcResult?.estimated_profit).toLocaleString('id-ID')"></p>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                    <button @click="abcModalOpen = false"
                        class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 rounded-lg">Tutup</button>
                    <a href="{{ url('/admin/abc') }}"
                        class="px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Buka
                        Analisis ABC Lengkap &rarr;</a>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function dashboardApp() {
                return {
                    products: [],
                    userInfo: {},
                    loading: false,
                    abcLoading: false,
                    abcModalOpen: false,
                    abcResult: null,
                    visitorCount: 0,

                    initDashboard() {
                        const user = localStorage.getItem('user_info');
                        if (user) {
                            this.userInfo = JSON.parse(user);
                        }
                        this.fetchProducts();
                        this.startYoloPolling();
                    },

                    async fetchProducts() {
                        this.loading = true;
                        const token = localStorage.getItem('auth_token');

                        try {
                            const response = await fetch('/api/v1/products', {
                                headers: {
                                    'Accept': 'application/json',
                                    'Authorization': token ? `Bearer ${token}` : ''
                                }
                            });
                            const result = await response.json();
                            if (result.success) {
                                this.products = result.data;
                            }
                        } catch (error) {
                            console.error('Gagal mengambil data produk:', error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    async runAbcOptimization() {
                        this.abcLoading = true;
                        const token = localStorage.getItem('auth_token');

                        try {
                            const response = await fetch('/api/v1/abc/generate-bundle', {
                                headers: {
                                    'Accept': 'application/json',
                                    'Authorization': token ? `Bearer ${token}` : ''
                                }
                            });
                            const result = await response.json();

                            if (result.success) {
                                this.abcResult = result.data;
                                this.abcModalOpen = true;
                            } else {
                                alert(result.message || 'Gagal menjalankan optimasi lebah.');
                            }
                        } catch (error) {
                            alert('Gagal terhubung ke REST API ABC.');
                            console.error(error);
                        } finally {
                            this.abcLoading = false;
                        }
                    },

                    startYoloPolling() {
                        const FLASK_BASE_URL = "{{ config('app.flask_url', env('FLASK_YOLO_URL', 'http://localhost:5000')) }}";

                        const streamImg = document.getElementById('yolo-stream-img');
                        if (streamImg && streamImg.src !== `${FLASK_BASE_URL}/video_feed`) {
                            streamImg.src = `${FLASK_BASE_URL}/video_feed`;
                        }

                        const fetchYoloStats = async () => {
                            try {
                                const response = await fetch(`${FLASK_BASE_URL}/api/stats`, {
                                    headers: {
                                        'ngrok-skip-browser-warning': 'true'
                                    }
                                });

                                if (response.ok) {
                                    const data = await response.json();
                                    this.visitorCount = data.total_detected;
                                }
                            } catch (error) {
                                console.error('Error fetching YOLO stats:', error);
                            }
                        };

                        fetchYoloStats();
                        setInterval(fetchYoloStats, 1000);
                    }
                }
            }
        </script>
    @endpush
@endsection