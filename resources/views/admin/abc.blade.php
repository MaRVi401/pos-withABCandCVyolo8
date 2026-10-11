@extends('layouts.admin')

@section('title', 'Smart Bundling Artificial Bee Colony - POS System')
@section('page_title', 'Smart Optimasi Promo (Artificial Bee Colony)')

@section('content')
    <div class="space-y-6" x-data="abcAnalyticsApp()" x-init="initApp()">

        <!-- Header Description -->
        <div
            class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="space-y-2">
                <!-- Header Title & Status Badge -->
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl p-2 bg-amber-100 border border-amber-200 rounded-xl shadow-sm">🐝</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-800 tracking-tight">Intelligence Swarm Engine</h2>
                            <span
                                class="bg-indigo-100 text-indigo-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase border border-indigo-200">
                                ABC Algorithm v1.0
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-amber-700">Modul Optimasi Paket Promo & Penyeimbangan Stok</p>
                    </div>
                </div>

                <!-- Deskripsi & Highlight Parametric -->
                <p class="text-xs text-gray-600 max-w-2xl leading-relaxed pl-0.5">
                    Menggunakan metode kecerdasan buatan <strong>Artificial Bee Colony (ABC)</strong> yang menyimulasikan
                    perilaku lebah saat mencari nektar. Algoritma ini secara otomatis menganalisis kombinasi produk terlaris
                    (<em>fast-moving</em>) dengan produk stok melimpah (<em>slow-moving</em>) untuk menentukan potongan
                    harga paling presisi di rentang <span
                        class="font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">5% –
                        15%</span> tanpa mengurangi batas keuntungan toko.
                </p>

                <!-- Poin Indikator Keunggulan -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-2 max-w-2xl">
                    <div
                        class="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-700">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Memutar kas stok menumpuk</span>
                    </div>
                    <div
                        class="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-700">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Mencegah diskon rugi modal</span>
                    </div>
                    <div
                        class="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-700">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Meningkatkan nilai transaksi</span>
                    </div>
                </div>
            </div>
            <button @click="generateABC()" :disabled="loading"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-5 py-3 rounded-xl shadow-md transition-all flex items-center gap-2 shrink-0">
                <span x-show="!loading">🔄 Regenerasi Paket Promo</span>
                <span x-show="loading">🐝 Lebah Sedang Mencari Solusi...</span>
            </button>
        </div>

        <!-- Error/Notice State -->
        <div x-show="errorMessage" x-cloak class="p-4 bg-red-100 border border-red-300 text-red-800 text-xs rounded-xl"
            x-text="errorMessage"></div>

        <!-- Main Result Grid -->
        <template x-if="bundle">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Card Pasangan Produk -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Kombinasi Produk Hasil Swarm
                        Intelligence</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Produk Fast Moving -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative overflow-hidden">
                            <span
                                class="absolute top-3 right-3 text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">
                                Fast Moving
                            </span>
                            <p class="text-xs text-slate-400 font-bold uppercase">Produk Penopang</p>
                            <h4 class="font-bold text-slate-800 text-base mt-1" x-text="bundle.fast_product.product_name">
                            </h4>
                            <div class="mt-4 flex justify-between items-end">
                                <div>
                                    <p class="text-[11px] text-slate-500">Harga Jual Normal</p>
                                    <p class="text-sm font-extrabold text-slate-800"
                                        x-text="'Rp ' + Number(bundle.fast_product.sell_price).toLocaleString('id-ID')"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[11px] text-slate-500">Sisa Stok</p>
                                    <p class="text-xs font-bold text-emerald-600"
                                        x-text="bundle.fast_product.stock + ' Unit'"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Produk Slow Moving -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative overflow-hidden">
                            <span
                                class="absolute top-3 right-3 text-[10px] font-bold uppercase bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">
                                Slow Moving
                            </span>
                            <p class="text-xs text-slate-400 font-bold uppercase">Produk Cuci Gudang</p>
                            <h4 class="font-bold text-slate-800 text-base mt-1" x-text="bundle.slow_product.product_name">
                            </h4>
                            <div class="mt-4 flex justify-between items-end">
                                <div>
                                    <p class="text-[11px] text-slate-500">Harga Jual Normal</p>
                                    <p class="text-sm font-extrabold text-slate-800"
                                        x-text="'Rp ' + Number(bundle.slow_product.sell_price).toLocaleString('id-ID')"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[11px] text-slate-500">Sisa Stok</p>
                                    <p class="text-xs font-bold text-amber-600"
                                        x-text="bundle.slow_product.stock + ' Unit'"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Perhitungan Rincian Harga -->
                    <div class="p-4 bg-indigo-50/60 rounded-xl border border-indigo-100 space-y-3 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Total Harga Normal Kedua Produk:</span>
                            <span class="font-bold text-slate-800"
                                x-text="'Rp ' + Number(bundle.normal_price).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Potongan Diskon Lebah ABC (<strong x-text="bundle.discount_rate"
                                    class="text-indigo-600"></strong>):</span>
                            <span class="font-bold text-red-600"
                                x-text="'- Rp ' + Number(bundle.discount_amount).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-indigo-200 font-bold text-slate-900">
                            <span>Harga Bundling Promo Kasir:</span>
                            <span class="text-indigo-700 text-base"
                                x-text="'Rp ' + Number(bundle.promo_price).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

                <!-- Card Summary & Profit Fitnes -->
                <div
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between space-y-6">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Analisis Fitnes Diskon
                        </h3>

                        <div
                            class="text-center p-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100">
                            <p class="text-xs text-indigo-600 font-bold uppercase tracking-wider">Estimasi Margin Keuntungan
                            </p>
                            <h2 class="text-3xl font-extrabold text-indigo-900 mt-2"
                                x-text="'Rp ' + Number(bundle.estimated_profit).toLocaleString('id-ID')"></h2>
                            <p class="text-[11px] text-gray-500 mt-2">Dihitung dari `Harga Promo - Total Harga Beli Modal`
                            </p>
                        </div>

                        <div class="mt-6 space-y-3 text-xs text-gray-600">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Aman dari batas rugi modal (*margin terjamin*)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Mendorong rotasi stok *slow-moving*</span>
                            </div>
                        </div>
                    </div>

                    <button @click="applyBundleToPOS()"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-3.5 rounded-xl shadow-lg transition-all text-center">
                        Terapkan Paket Ke Kasir (POS) &rarr;
                    </button>
                </div>

            </div>
        </template>

    </div>

    @push('scripts')
        <script>
            function abcAnalyticsApp() {
                return {
                    bundle: null,
                    loading: false,
                    errorMessage: '',

                    initApp() {
                        this.generateABC();
                    },

                    async generateABC() {
                        this.loading = true;
                        this.errorMessage = '';
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
                                this.bundle = result.data;
                            } else {
                                this.errorMessage = result.message || 'Gagal memproses data lebah.';
                            }
                        } catch (error) {
                            this.errorMessage = 'Gagal terhubung ke API ABC.';
                            console.error(error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    applyBundleToPOS() {
                        alert("Paket promo " + this.bundle.fast_product.product_name + " + " + this.bundle.slow_product
                            .product_name + " berhasil diaktifkan untuk Kasir!");
                    }
                }
            }
        </script>
    @endpush
@endsection
