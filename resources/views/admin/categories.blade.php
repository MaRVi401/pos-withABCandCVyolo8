@extends('layouts.admin')

@section('title', 'Kategori Produk - POS System')
@section('page_title', 'Manajemen Kategori Produk')

@section('content')
<div class="space-y-6" x-data="categoryApp()" x-init="initApp()">

    <!-- Header & Search -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="relative w-full sm:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
            <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Cari nama kategori..."
                class="block w-full pl-9 pr-3 py-2 bg-slate-50 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <button @click="openModal('add')" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
            <span>➕</span> Tambah Kategori
        </button>
    </div>

    <!-- Tabel Data -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-100 uppercase text-[10px] font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-3.5">No.</th>
                        <th class="p-3.5">Nama Kategori</th>
                        <th class="p-3.5">Deskripsi</th>
                        <th class="p-3.5 text-center">Jumlah Produk</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr><td colspan="5" class="p-8 text-center text-gray-400">🌀 Memuat data kategori...</td></tr>
                    </template>
                    <template x-if="!loading && paginatedCategories.length === 0">
                        <tr><td colspan="5" class="p-8 text-center text-gray-400">Tidak ada kategori ditemukan.</td></tr>
                    </template>
                    <template x-for="(c, index) in paginatedCategories" :key="c.category_id">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3.5 font-bold text-gray-400" x-text="startItem + index"></td>
                            <td class="p-3.5 font-bold text-slate-800" x-text="c.category_name"></td>
                            <td class="p-3.5 text-gray-500" x-text="c.description || '-'"></td>
                            <td class="p-3.5 text-center font-extrabold text-indigo-600" x-text="(c.products_count || 0) + ' Item'"></td>
                            <td class="p-3.5 text-center space-x-2">
                                <button @click="openModal('edit', c)" class="text-amber-600 hover:text-amber-800 font-bold">✏️ Edit</button>
                                <button @click="deleteCategory(c.category_id)" class="text-red-600 hover:text-red-800 font-bold">🗑️ Hapus</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-slate-50/50 flex justify-between items-center text-xs" x-show="filteredCategories.length > 0">
            <span class="text-gray-500">Menampilkan <b x-text="startItem"></b> - <b x-text="endItem"></b> dari <b x-text="filteredCategories.length"></b> data</span>
            <div class="flex gap-1">
                <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 bg-white border rounded disabled:opacity-50">&laquo; Prev</button>
                <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1 bg-white border rounded disabled:opacity-50">Next &raquo;</button>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl" @click.away="modalOpen = false">
            <h3 class="text-base font-bold text-slate-800" x-text="modalMode === 'add' ? 'Tambah Kategori Baru' : 'Edit Kategori'"></h3>
            <form @submit.prevent="saveCategory()" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nama Kategori</label>
                    <input type="text" x-model="form.category_name" required class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Deskripsi</label>
                    <textarea x-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function categoryApp() {
        return {
            categories: [], searchQuery: '', loading: false, modalOpen: false, modalMode: 'add',
            form: { category_id: null, category_name: '', description: '' },
            currentPage: 1, perPage: 10,

            initApp() { this.fetchCategories(); },
            async fetchCategories() {
                this.loading = true;
                const token = localStorage.getItem('auth_token');
                const res = await fetch('/api/v1/categories', { headers: { 'Authorization': `Bearer ${token}` } });
                const result = await res.json();
                if (result.success) this.categories = result.data;
                this.loading = false;
            },
            get filteredCategories() {
                if (!this.searchQuery) return this.categories;
                return this.categories.filter(c => c.category_name.toLowerCase().includes(this.searchQuery.toLowerCase()));
            },
            get paginatedCategories() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredCategories.slice(start, start + this.perPage);
            },
            get totalPages() { return Math.ceil(this.filteredCategories.length / this.perPage) || 1; },
            get startItem() { return this.filteredCategories.length ? (this.currentPage - 1) * this.perPage + 1 : 0; },
            get endItem() { return Math.min(this.currentPage * this.perPage, this.filteredCategories.length); },

            openModal(mode, data = null) {
                this.modalMode = mode;
                this.form = data ? { ...data } : { category_id: null, category_name: '', description: '' };
                this.modalOpen = true;
            },
            async saveCategory() {
                const token = localStorage.getItem('auth_token');
                const isAdd = this.modalMode === 'add';
                const url = isAdd ? '/api/v1/categories' : `/api/v1/categories/${this.form.category_id}`;
                const method = isAdd ? 'POST' : 'PUT';

                const res = await fetch(url, {
                    method,
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const result = await res.json();
                if (res.ok) {
                    alert(result.message);
                    this.modalOpen = false;
                    this.fetchCategories();
                } else {
                    alert(result.message || 'Gagal menyimpan data.');
                }
            },
            async deleteCategory(id) {
                if (!confirm('Yakin ingin menghapus kategori ini?')) return;
                const token = localStorage.getItem('auth_token');
                const res = await fetch(`/api/v1/categories/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const result = await res.json();
                alert(result.message);
                if (res.ok) this.fetchCategories();
            }
        }
    }
</script>
@endpush
@endsection