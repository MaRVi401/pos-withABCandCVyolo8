@extends('layouts.admin')

@section('title', 'Penyesuaian Stok - POS System')
@section('page_title', 'Riwayat & Penyesuaian Stok')

@section('content')
<div class="space-y-6" x-data="stockApp()" x-init="initApp()">

    <!-- Header & Action Button -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
            <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Cari SKU atau nama produk..."
                class="block w-full pl-9 pr-3 py-2 bg-slate-50 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <button @click="openModal()" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
            <span>⚖️</span> Adjust Stok Baru
        </button>
    </div>

    <!-- Tabel Riwayat Penyesuaian -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-100 uppercase text-[10px] font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">SKU / Produk</th>
                        <th class="p-3.5 text-center">Jenis Adjust</th>
                        <th class="p-3.5 text-center">Jumlah</th>
                        <th class="p-3.5">Alasan / Catatan</th>
                        <th class="p-3.5">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr><td colspan="6" class="p-8 text-center text-gray-400">🌀 Memuat data penyesuaian...</td></tr>
                    </template>
                    <template x-if="!loading && paginatedAdjustments.length === 0">
                        <tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada riwayat penyesuaian stok.</td></tr>
                    </template>
                    <template x-for="a in paginatedAdjustments" :key="a.adjustment_id">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3.5 text-gray-500 whitespace-nowrap" x-text="a.created_at"></td>
                            <td class="p-3.5 font-bold text-slate-800">
                                <span class="text-indigo-600" x-text="a.product?.sku"></span> - <span x-text="a.product?.product_name"></span>
                            </td>
                            <td class="p-3.5 text-center">
                                <span :class="getTypeBadge(a.adjustment_type)" class="px-2.5 py-0.5 rounded-full font-extrabold text-[10px] uppercase" x-text="a.adjustment_type"></span>
                            </td>
                            <td class="p-3.5 text-center font-extrabold text-slate-900" x-text="a.quantity_changed"></td>
                            <td class="p-3.5 text-gray-600" x-text="a.reason || '-'"></td>
                            <td class="p-3.5 font-semibold text-gray-700" x-text="a.user?.name || 'Admin'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-slate-50/50 flex justify-between items-center text-xs" x-show="filteredAdjustments.length > 0">
            <span class="text-gray-500">Menampilkan <b x-text="startItem"></b> - <b x-text="endItem"></b> dari <b x-text="filteredAdjustments.length"></b> data</span>
            <div class="flex gap-1">
                <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 bg-white border rounded disabled:opacity-50">&laquo; Prev</button>
                <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1 bg-white border rounded disabled:opacity-50">Next &raquo;</button>
            </div>
        </div>
    </div>

    <!-- Modal Form Adjust Stok -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl" @click.away="modalOpen = false">
            <h3 class="text-base font-bold text-slate-800">Form Penyesuaian Stok</h3>
            <form @submit.prevent="saveAdjustment()" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Pilih Produk</label>
                    <select x-model="form.product_id" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Produk --</option>
                        <template x-for="p in products" :key="p.product_id">
                            <option :value="p.product_id" x-text="p.sku + ' - ' + p.product_name + ' (Stok: ' + p.stock_quantity + ')'"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Tipe Adjust</label>
                    <select x-model="form.adjustment_type" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="IN">Stok Masuk (IN +)</option>
                        <option value="OUT">Stok Keluar (OUT -)</option>
                        <option value="CORRECTION">Koreksi Total (CORRECTION =)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Jumlah Unit</label>
                    <input type="number" x-model="form.quantity" min="1" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Alasan Penyesuaian</label>
                    <textarea x-model="form.reason" required placeholder="Contoh: Barang rusak, retur supplier, selisih opname..." rows="2" class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl">Proses Adjust</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function stockApp() {
        return {
            adjustments: [], products: [], searchQuery: '', loading: false, modalOpen: false,
            form: { product_id: '', adjustment_type: 'IN', quantity: 1, reason: '' },
            currentPage: 1, perPage: 10,

            initApp() { this.fetchAdjustments(); this.fetchProducts(); },
            async fetchAdjustments() {
                this.loading = true;
                const token = localStorage.getItem('auth_token');
                const res = await fetch('/api/v1/stock-adjustments', { headers: { 'Authorization': `Bearer ${token}` } });
                const result = await res.json();
                if (result.success) this.adjustments = result.data;
                this.loading = false;
            },
            async fetchProducts() {
                const token = localStorage.getItem('auth_token');
                const res = await fetch('/api/v1/products', { headers: { 'Authorization': `Bearer ${token}` } });
                const result = await res.json();
                if (result.success) this.products = result.data;
            },
            get filteredAdjustments() {
                if (!this.searchQuery) return this.adjustments;
                const q = this.searchQuery.toLowerCase();
                return this.adjustments.filter(a => (a.product?.product_name || '').toLowerCase().includes(q) || (a.product?.sku || '').toLowerCase().includes(q));
            },
            get paginatedAdjustments() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredAdjustments.slice(start, start + this.perPage);
            },
            get totalPages() { return Math.ceil(this.filteredAdjustments.length / this.perPage) || 1; },
            get startItem() { return this.filteredAdjustments.length ? (this.currentPage - 1) * this.perPage + 1 : 0; },
            get endItem() { return Math.min(this.currentPage * this.perPage, this.filteredAdjustments.length); },

            openModal() {
                this.form = { product_id: '', adjustment_type: 'IN', quantity: 1, reason: '' };
                this.modalOpen = true;
            },
            async saveAdjustment() {
                const token = localStorage.getItem('auth_token');
                const res = await fetch('/api/v1/stock-adjustments', {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const result = await res.json();
                if (res.ok) {
                    alert(result.message);
                    this.modalOpen = false;
                    this.fetchAdjustments();
                } else {
                    alert(result.message || 'Gagal menyesuaikan stok.');
                }
            },
            getTypeBadge(type) {
                if (type === 'IN') return 'bg-emerald-100 text-emerald-800';
                if (type === 'OUT') return 'bg-red-100 text-red-800';
                return 'bg-amber-100 text-amber-800';
            }
        }
    }
</script>
@endpush
@endsection