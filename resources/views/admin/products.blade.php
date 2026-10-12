@extends('layouts.admin')

@section('title', 'Data Produk - POS System')
@section('page_title', 'Manajemen Data Produk')

@section('content')
<div class="space-y-6" x-data="productApp()" x-init="initApp()">

    <!-- Header & Filter -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
            <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Cari SKU atau nama produk..."
                class="block w-full pl-9 pr-3 py-2 bg-slate-50 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <button @click="openModal('add')" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
            <span>➕</span> Tambah Produk
        </button>
    </div>

    <!-- Tabel Data Produk -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-100 uppercase text-[10px] font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-3.5">Gambar</th>
                        <th class="p-3.5">SKU</th>
                        <th class="p-3.5">Nama Produk</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5 text-right">Harga Beli</th>
                        <th class="p-3.5 text-right">Harga Jual</th>
                        <th class="p-3.5 text-center">Stok</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr><td colspan="8" class="p-8 text-center text-gray-400">🌀 Memuat data produk...</td></tr>
                    </template>
                    <template x-if="!loading && paginatedProducts.length === 0">
                        <tr><td colspan="8" class="p-8 text-center text-gray-400">Tidak ada produk ditemukan.</td></tr>
                    </template>
                    <template x-for="p in paginatedProducts" :key="p.product_id">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3.5">
                                <img :src="p.image_url || 'https://via.placeholder.com/40'" class="w-10 h-10 object-cover rounded-lg border">
                            </td>
                            <td class="p-3.5 font-bold text-indigo-700" x-text="p.sku"></td>
                            <td class="p-3.5 font-bold text-slate-800" x-text="p.product_name"></td>
                            <td class="p-3.5 text-gray-500" x-text="p.category?.category_name || '-'"></td>
                            <td class="p-3.5 text-right font-semibold text-gray-600" x-text="formatRupiah(p.buy_price)"></td>
                            <td class="p-3.5 text-right font-extrabold text-slate-900" x-text="formatRupiah(p.sell_price)"></td>
                            <td class="p-3.5 text-center">
                                <span :class="p.stock_quantity < 10 ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800'" class="px-2 py-0.5 rounded-full font-bold text-[10px]" x-text="p.stock_quantity"></span>
                            </td>
                            <td class="p-3.5 text-center space-x-2">
                                <button @click="openModal('edit', p)" class="text-amber-600 hover:text-amber-800 font-bold">✏️ Edit</button>
                                <button @click="deleteProduct(p.product_id)" class="text-red-600 hover:text-red-800 font-bold">🗑️ Hapus</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-slate-50/50 flex justify-between items-center text-xs" x-show="filteredProducts.length > 0">
            <span class="text-gray-500">Menampilkan <b x-text="startItem"></b> - <b x-text="endItem"></b> dari <b x-text="filteredProducts.length"></b> data</span>
            <div class="flex gap-1">
                <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 bg-white border rounded disabled:opacity-50">&laquo; Prev</button>
                <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1 bg-white border rounded disabled:opacity-50">Next &raquo;</button>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl max-h-[90vh] overflow-y-auto" @click.away="modalOpen = false">
            <h3 class="text-base font-bold text-slate-800" x-text="modalMode === 'add' ? 'Tambah Produk Baru' : 'Edit Produk'"></h3>
            <form @submit.prevent="saveProduct()" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">SKU Produk</label>
                        <input type="text" x-model="form.sku" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Kategori</label>
                        <select x-model="form.category_id" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Kategori --</option>
                            <template x-for="c in categories" :key="c.category_id">
                                <option :value="c.category_id" x-text="c.category_name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Nama Produk</label>
                    <input type="text" x-model="form.product_name" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Harga Beli</label>
                        <input type="number" x-model="form.buy_price" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Harga Jual</label>
                        <input type="number" x-model="form.sell_price" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Stok Awal</label>
                        <input type="number" x-model="form.stock_quantity" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Foto Produk</label>
                    <input type="file" @change="fileImage = $event.target.files[0]" accept="image/*" class="w-full text-xs text-gray-500">
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function productApp() {
        return {
            products: [], categories: [], searchQuery: '', loading: false, modalOpen: false, modalMode: 'add',
            form: { product_id: null, category_id: '', sku: '', product_name: '', buy_price: 0, sell_price: 0, stock_quantity: 0 },
            fileImage: null, currentPage: 1, perPage: 10,

            initApp() { this.fetchProducts(); this.fetchCategories(); },
            async fetchProducts() {
                this.loading = true;
                const token = localStorage.getItem('auth_token');
                const res = await fetch('/api/v1/products', { headers: { 'Authorization': `Bearer ${token}` } });
                const result = await res.json();
                if (result.success) this.products = result.data;
                this.loading = false;
            },
            async fetchCategories() {
                const token = localStorage.getItem('auth_token');
                const res = await fetch('/api/v1/categories', { headers: { 'Authorization': `Bearer ${token}` } });
                const result = await res.json();
                if (result.success) this.categories = result.data;
            },
            get filteredProducts() {
                if (!this.searchQuery) return this.products;
                const q = this.searchQuery.toLowerCase();
                return this.products.filter(p => p.product_name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q));
            },
            get paginatedProducts() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredProducts.slice(start, start + this.perPage);
            },
            get totalPages() { return Math.ceil(this.filteredProducts.length / this.perPage) || 1; },
            get startItem() { return this.filteredProducts.length ? (this.currentPage - 1) * this.perPage + 1 : 0; },
            get endItem() { return Math.min(this.currentPage * this.perPage, this.filteredProducts.length); },

            openModal(mode, data = null) {
                this.modalMode = mode;
                this.fileImage = null;
                this.form = data ? { ...data } : { product_id: null, category_id: '', sku: '', product_name: '', buy_price: 0, sell_price: 0, stock_quantity: 0 };
                this.modalOpen = true;
            },
            async saveProduct() {
                const token = localStorage.getItem('auth_token');
                const formData = new FormData();
                Object.keys(this.form).forEach(k => { if(this.form[k] !== null) formData.append(k, this.form[k]); });
                if (this.fileImage) formData.append('image', this.fileImage);

                const isAdd = this.modalMode === 'add';
                const url = isAdd ? '/api/v1/products' : `/api/v1/products/${this.form.product_id}`;
                if (!isAdd) formData.append('_method', 'PUT');

                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                    body: formData
                });
                const result = await res.json();
                if (res.ok) {
                    alert(result.message);
                    this.modalOpen = false;
                    this.fetchProducts();
                } else {
                    alert(result.message || 'Gagal menyimpan data produk.');
                }
            },
            async deleteProduct(id) {
                if (!confirm('Yakin ingin menghapus produk ini?')) return;
                const token = localStorage.getItem('auth_token');
                const res = await fetch(`/api/v1/products/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const result = await res.json();
                alert(result.message);
                if (res.ok) this.fetchProducts();
            },
            formatRupiah(val) { return 'Rp ' + Number(val || 0).toLocaleString('id-ID'); }
        }
    }
</script>
@endpush
@endsection