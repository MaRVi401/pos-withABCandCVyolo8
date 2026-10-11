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

        <!-- Data Table Produk dari REST API -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div
                class="p-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Katalog Produk (Diambil langsung dari `/api/v1/products`)
                    </h3>
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
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">Memuat data dari REST API...</td>
                        </tr>
                        <tr x-show="!loading && products.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data produk tersimpan
                                di database.</td>
                        </tr>
                    </tbody>
                </table>
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
                    initDashboard() {
                        const user = localStorage.getItem('user_info');
                        if (user) {
                            this.userInfo = JSON.parse(user);
                        }
                        this.fetchProducts();
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
                            console.error('Gagal mengambil data dari API:', error);
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
