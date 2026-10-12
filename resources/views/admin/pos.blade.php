@extends('layouts.admin')

@section('title', 'Kasir Penjualan (POS) - POS System')
@section('page_title', 'Kasir Penjualan (Point of Sale)')

@section('content')
    <div class="h-[calc(100vh-8.5rem)] flex flex-col lg:flex-row gap-5" x-data="posApp()" x-init="initApp()">

        <!-- KIRI: Katalog Produk & Search Bar -->
        <div class="flex-1 bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col overflow-hidden">

            <div class="p-4 border-b border-gray-200 space-y-3 bg-slate-50/50">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">🔍</span>
                        <input type="text" x-model="searchQuery" placeholder="Cari produk atau SKU..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <button @click="loadABCBundle()" :disabled="loadingABC"
                        class="w-full sm:w-auto bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm flex items-center justify-center gap-2 shrink-0 disabled:opacity-50">
                        <span>🐝</span>
                        <span x-text="loadingABC ? 'Memuat Promo...' : 'Terapkan Promo ABC'"></span>
                    </button>
                </div>

                <template x-if="activeBundle">
                    <div
                        class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between text-xs text-amber-900">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🎉</span>
                            <div>
                                <span class="font-bold"
                                    x-text="'Paket Bundling ABC (' + activeBundle.discount_rate + ' Diskon)'"></span>
                                <p class="text-[11px] text-amber-700"
                                    x-text="activeBundle.fast_product.product_name + ' + ' + activeBundle.slow_product.product_name">
                                </p>
                            </div>
                        </div>

                        <!-- Tombol Tambah Paket (Disabled jika stok habis) -->
                        <button @click="addBundleToCart()" :disabled="!isBundleAvailable"
                            :class="isBundleAvailable ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-sm' :
                                'bg-gray-300 text-gray-500 cursor-not-allowed'"
                            class="font-bold px-3 py-1.5 rounded-lg text-[11px] transition-all">
                            <span x-text="isBundleAvailable ? '+ Tambah Paket' : '⚠️ Stok Habis'"></span>
                        </button>
                    </div>
                </template>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                <div x-show="loadingProducts"
                    class="flex flex-col items-center justify-center h-64 text-gray-400 space-y-2">
                    <span class="text-2xl animate-spin">🌀</span>
                    <p class="text-xs">Memuat katalog produk...</p>
                </div>

                <div x-show="!loadingProducts && filteredProducts.length === 0"
                    class="flex flex-col items-center justify-center h-64 text-gray-400 space-y-2" x-cloak>
                    <span class="text-3xl">📦</span>
                    <p class="text-xs font-semibold">Produk tidak ditemukan.</p>
                </div>

                <div x-show="!loadingProducts && filteredProducts.length > 0"
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5" x-cloak>
                    <template x-for="product in filteredProducts" :key="product.product_id">
                        <div @click="addToCart(product)"
                            class="bg-white border border-gray-200 hover:border-indigo-500 rounded-xl p-3 cursor-pointer transition-all hover:shadow-md flex flex-col justify-between group relative overflow-hidden">

                            <span
                                :class="product.stock_quantity < 10 ? 'bg-red-100 text-red-700 border-red-200' :
                                    'bg-slate-100 text-slate-600 border-slate-200'"
                                class="absolute top-2.5 right-2.5 text-[10px] font-bold px-2 py-0.5 rounded-full border">
                                Stok: <span x-text="product.stock_quantity"></span>
                            </span>

                            <div class="space-y-1.5 pt-4">
                                <p class="text-[10px] font-bold text-gray-400 uppercase" x-text="product.sku"></p>
                                <h4 class="font-bold text-gray-800 text-xs line-clamp-2 group-hover:text-indigo-600"
                                    x-text="product.product_name"></h4>
                            </div>

                            <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between">
                                <span class="font-extrabold text-indigo-700 text-xs"
                                    x-text="formatRupiah(product.sell_price)"></span>
                                <span
                                    class="text-indigo-600 bg-indigo-50 group-hover:bg-indigo-600 group-hover:text-white p-1 rounded-lg text-xs">➕</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- KANAN: Keranjang & Ringkasan Belanja -->
        <div
            class="w-full lg:w-96 bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between overflow-hidden">

            <div class="p-4 border-b border-gray-200 bg-slate-50/50 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🛒</span>
                    <h3 class="font-bold text-gray-800 text-sm">Keranjang Belanja</h3>
                </div>
                <button @click="clearCart()" x-show="cart.length > 0"
                    class="text-xs text-red-500 hover:text-red-700 font-semibold" x-cloak>Kosongkan</button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-if="cart.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 space-y-2 py-12">
                        <span class="text-4xl">🛍️</span>
                        <p class="text-xs font-semibold">Keranjang masih kosong</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div
                        class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex justify-between items-center text-xs">
                        <div class="space-y-1 flex-1 pr-2">
                            <h5 class="font-bold text-gray-800 line-clamp-1" x-text="item.product_name"></h5>
                            <p class="text-[11px] text-gray-500"
                                x-text="formatRupiah(item.sell_price) + ' x ' + item.quantity"></p>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="flex items-center border border-gray-300 rounded-lg bg-white overflow-hidden">
                                <button @click="updateQty(index, -1)"
                                    class="px-2 py-0.5 hover:bg-gray-100 font-bold">-</button>
                                <span class="px-2 font-bold text-gray-800 text-[11px]" x-text="item.quantity"></span>
                                <button @click="updateQty(index, 1)"
                                    class="px-2 py-0.5 hover:bg-gray-100 font-bold">+</button>
                            </div>
                            <span class="font-bold text-indigo-700 w-16 text-right"
                                x-text="formatRupiah(item.sell_price * item.quantity)"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-4 border-t border-gray-200 bg-slate-50/50 space-y-3">
                <div class="space-y-1.5 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span class="font-bold text-gray-800" x-text="formatRupiah(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-red-600" x-show="discountAmount > 0" x-cloak>
                        <span>Diskon Promo ABC:</span>
                        <span class="font-bold" x-text="'- ' + formatRupiah(discountAmount)"></span>
                    </div>
                    <div class="flex justify-between text-base pt-2 border-t border-gray-200 font-extrabold text-gray-900">
                        <span>Total Tagihan:</span>
                        <span class="text-indigo-700" x-text="formatRupiah(finalTotal)"></span>
                    </div>
                </div>

                <button @click="openPaymentModal()" :disabled="cart.length === 0"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-extrabold text-xs py-3.5 rounded-xl shadow-md text-center">
                    Proses Pembayaran (Checkout) &rarr;
                </button>
            </div>
        </div>

        <!-- MODAL PEMBAYARAN -->
        <div x-show="showModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5" @click.outside="showModal = false">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-gray-800 text-sm">Pembayaran Kasir</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Metode Pembayaran</label>
                        <select x-model="paymentMethodId"
                            class="w-full p-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="1">Cash (Tunai)</option>
                            <option value="2">QRIS</option>
                            <option value="3">Transfer Bank</option>
                            <option value="4">Credit / Debit Card</option>
                        </select>
                    </div>

                    <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100 flex justify-between items-center">
                        <span class="font-semibold text-gray-600">Total Tagihan:</span>
                        <span class="text-lg font-extrabold text-indigo-700" x-text="formatRupiah(finalTotal)"></span>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Uang Diterima (Rp)</label>
                        <input type="number" x-model.number="paidAmount" placeholder="Input nominal bayar..."
                            class="w-full p-2.5 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-bold text-gray-800">
                    </div>

                    <div class="p-3 rounded-xl border flex justify-between items-center"
                        :class="changeAmount >= 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' :
                            'bg-red-50 border-red-200 text-red-800'">
                        <span class="font-semibold" x-text="changeAmount >= 0 ? 'Kembalian:' : 'Uang Kurang:'"></span>
                        <span class="text-sm font-extrabold" x-text="formatRupiah(Math.abs(changeAmount))"></span>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button @click="showModal = false"
                        class="flex-1 py-2.5 border border-gray-300 rounded-xl font-bold text-gray-600 hover:bg-gray-50">Batal</button>
                    <button @click="submitTransaction()" :disabled="submitting || changeAmount < 0"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl font-bold shadow-md">
                        <span x-text="submitting ? 'Memproses...' : 'Selesaikan Transaksi'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            function posApp() {
                return {
                    products: [],
                    cart: [],
                    searchQuery: '',
                    loadingProducts: false,
                    loadingABC: false,
                    activeBundle: null,
                    discountAmount: 0,
                    showModal: false,
                    paymentMethodId: 1,
                    paidAmount: 0,
                    submitting: false,

                    initApp() {
                        this.fetchProducts();
                    },

                    async fetchProducts() {
                        this.loadingProducts = true;
                        const token = localStorage.getItem('auth_token');
                        try {
                            const res = await fetch('/api/v1/products', {
                                headers: {
                                    'Authorization': token ? `Bearer ${token}` : ''
                                }
                            });
                            const result = await res.json();
                            if (result.success) {
                                this.products = result.data;
                            }
                        } catch (e) {
                            console.error('Gagal mengambil produk:', e);
                        } finally {
                            this.loadingProducts = false;
                        }
                    },

                    get filteredProducts() {
                        if (!this.searchQuery) return this.products;
                        const q = this.searchQuery.toLowerCase();
                        return this.products.filter(p =>
                            p.product_name.toLowerCase().includes(q) ||
                            p.sku.toLowerCase().includes(q)
                        );
                    },

                    addToCart(product) {
                        if (product.stock_quantity <= 0) {
                            alert('Stok produk ini sedang habis!');
                            return;
                        }

                        const existingIndex = this.cart.findIndex(item => item.product_id === product.product_id);
                        if (existingIndex > -1) {
                            if (this.cart[existingIndex].quantity + 1 > product.stock_quantity) {
                                alert('Jumlah pesanan melebihi stok yang tersedia!');
                                return;
                            }
                            this.cart[existingIndex].quantity++;
                        } else {
                            this.cart.push({
                                product_id: product.product_id,
                                product_name: product.product_name,
                                sell_price: parseFloat(product.sell_price),
                                stock_quantity: product.stock_quantity,
                                quantity: 1
                            });
                        }
                    },

                    updateQty(index, change) {
                        const item = this.cart[index];
                        const newQty = item.quantity + change;
                        if (newQty <= 0) {
                            this.cart.splice(index, 1);
                        } else if (newQty > item.stock_quantity) {
                            alert('Jumlah pesanan melebihi stok yang tersedia!');
                        } else {
                            item.quantity = newQty;
                        }
                    },

                    clearCart() {
                        this.cart = [];
                        this.discountAmount = 0;
                        this.activeBundle = null;
                    },

                    get subtotal() {
                        return this.cart.reduce((sum, item) => sum + (item.sell_price * item.quantity), 0);
                    },

                    get finalTotal() {
                        return Math.max(0, this.subtotal - this.discountAmount);
                    },

                    get changeAmount() {
                        return (this.paidAmount || 0) - this.finalTotal;
                    },

                    // Cek ketersediaan stok real-time kedua produk bundling di katalog
                    get isBundleAvailable() {
                        if (!this.activeBundle) return false;

                        const fast = this.products.find(p => p.product_id === this.activeBundle.fast_product.product_id);
                        const slow = this.products.find(p => p.product_id === this.activeBundle.slow_product.product_id);

                        const fastStock = fast ? fast.stock_quantity : 0;
                        const slowStock = slow ? slow.stock_quantity : 0;

                        return fastStock > 0 && slowStock > 0;
                    },

                    async loadABCBundle() {
                        this.loadingABC = true;
                        const token = localStorage.getItem('auth_token');
                        try {
                            const res = await fetch('/api/v1/abc/generate-bundle', {
                                headers: {
                                    'Authorization': token ? `Bearer ${token}` : ''
                                }
                            });
                            const result = await res.json();
                            if (result.success) {
                                this.activeBundle = result.data;
                            } else {
                                alert(result.message || 'Gagal memuat rekomendasi promo.');
                            }
                        } catch (e) {
                            alert('Gagal terhubung ke modul ABC.');
                        } finally {
                            this.loadingABC = false;
                        }
                    },

                    addBundleToCart() {
                        if (!this.activeBundle) return;

                        // 1. Cari data produk fast & slow secara real-time dari katalog
                        const fast = this.products.find(p => p.product_id === this.activeBundle.fast_product.product_id);
                        const slow = this.products.find(p => p.product_id === this.activeBundle.slow_product.product_id);

                        if (!fast || !slow) {
                            alert('Data produk paket promo tidak ditemukan di katalog!');
                            return;
                        }

                        // 2. Hitung jumlah yang sudah ada di cart saat ini
                        const cartFast = this.cart.find(i => i.product_id === fast.product_id);
                        const cartSlow = this.cart.find(i => i.product_id === slow.product_id);

                        const currentFastQty = cartFast ? cartFast.quantity : 0;
                        const currentSlowQty = cartSlow ? cartSlow.quantity : 0;

                        // 3. VALIDASI ATOMIK (ALL-OR-NOTHING):
                        // Cek apakah ketersediaan sisa stok CUKUP untuk menambah 1 paket bundling lagi
                        if ((currentFastQty + 1 > fast.stock_quantity) || (currentSlowQty + 1 > slow.stock_quantity)) {
                            alert(
                                'Gagal menambah paket! Stok salah satu atau kedua produk bundling sudah tidak mencukupi. Silakan tekan tombol "Terapkan Promo ABC" untuk meregenerasi paket promo baru.');
                            return;
                        }

                        // 4. Jika KEDUA produk terbukti aman stoknya, baru masukkan BERSAMAAN ke cart
                        this.addToCart(fast);
                        this.addToCart(slow);

                        // 5. Akumulasikan diskon promo ABC
                        this.discountAmount += parseFloat(this.activeBundle.discount_amount);
                    },

                    openPaymentModal() {
                        this.paidAmount = this.finalTotal;
                        this.showModal = true;
                    },

                    async submitTransaction() {
                        if (this.changeAmount < 0) {
                            alert('Nominal uang pembayaran kurang!');
                            return;
                        }

                        this.submitting = true;
                        const token = localStorage.getItem('auth_token');

                        const payload = {
                            payment_method_id: parseInt(this.paymentMethodId),
                            paid_amount: this.paidAmount,
                            discount_amount: this.discountAmount,
                            items: this.cart.map(i => ({
                                product_id: i.product_id,
                                quantity: i.quantity
                            }))
                        };

                        try {
                            const res = await fetch('/api/v1/transactions', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'Authorization': token ? `Bearer ${token}` : ''
                                },
                                body: JSON.stringify(payload)
                            });

                            const result = await res.json();

                            if (result.success) {
                                alert(
                                    `Transaksi Berhasil!\nKode Invoice: ${result.data.invoice_number}\nKembalian: ${this.formatRupiah(result.data.change_amount)}`
                                    );
                                this.showModal = false;
                                this.clearCart();
                                this.fetchProducts(); // Refresh katalog & stok produk
                            } else {
                                alert(result.message || 'Transaksi gagal diproses.');
                            }
                        } catch (e) {
                            alert('Gagal terhubung ke API transaksi.');
                        } finally {
                            this.submitting = false;
                        }
                    },

                    formatRupiah(val) {
                        return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
                    }
                }
            }
        </script>
    @endpush
@endsection
