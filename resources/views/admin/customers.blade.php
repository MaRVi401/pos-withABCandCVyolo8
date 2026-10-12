@extends('layouts.admin')

@section('title', 'Manajemen Pelanggan - POS System')
@section('page_title', 'Manajemen Pelanggan / Customer')

@section('content')
<div class="space-y-6" x-data="customerApp()" x-init="initApp()">

    <!-- Header & Search -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="relative w-full sm:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
            <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Cari nama, email, atau telepon..."
                class="block w-full pl-9 pr-3 py-2 bg-slate-50 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <button @click="openModal('add')" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
            <span>👤➕</span> Tambah Pelanggan
        </button>
    </div>

    <!-- Tabel Data Pelanggan -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-100 uppercase text-[10px] font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-3.5">No.</th>
                        <th class="p-3.5">Nama Pelanggan</th>
                        <th class="p-3.5">Email</th>
                        <th class="p-3.5">No. Telepon</th>
                        <th class="p-3.5">Alamat</th>
                        <th class="p-3.5 text-center">Status</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr><td colspan="7" class="p-8 text-center text-gray-400">🌀 Memuat data pelanggan...</td></tr>
                    </template>
                    <template x-if="!loading && paginatedCustomers.length === 0">
                        <tr><td colspan="7" class="p-8 text-center text-gray-400">Tidak ada data pelanggan ditemukan.</td></tr>
                    </template>
                    <template x-for="(c, index) in paginatedCustomers" :key="c.user_id">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3.5 font-bold text-gray-400" x-text="startItem + index"></td>
                            <td class="p-3.5 font-bold text-slate-800" x-text="c.name"></td>
                            <td class="p-3.5 text-gray-600" x-text="c.email"></td>
                            <td class="p-3.5 text-gray-600" x-text="c.phone || '-'"></td>
                            <td class="p-3.5 text-gray-500 max-w-xs truncate" x-text="c.address || '-'"></td>
                            <td class="p-3.5 text-center">
                                <button @click="toggleStatus(c)"
                                    :class="c.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                                    class="px-2.5 py-0.5 rounded-full font-extrabold text-[10px] uppercase transition-transform hover:scale-105">
                                    <span x-text="c.is_active ? 'Aktif' : 'Non-Aktif'"></span>
                                </button>
                            </td>
                            <td class="p-3.5 text-center space-x-2">
                                <button @click="openModal('edit', c)" class="text-amber-600 hover:text-amber-800 font-bold">✏️ Edit</button>
                                <button @click="deleteCustomer(c.user_id)" class="text-red-600 hover:text-red-800 font-bold">🗑️ Hapus</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-slate-50/50 flex justify-between items-center text-xs" x-show="filteredCustomers.length > 0">
            <span class="text-gray-500">Menampilkan <b x-text="startItem"></b> - <b x-text="endItem"></b> dari <b x-text="filteredCustomers.length"></b> data</span>
            <div class="flex gap-1">
                <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 bg-white border rounded disabled:opacity-50">&laquo; Prev</button>
                <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-3 py-1 bg-white border rounded disabled:opacity-50">Next &raquo;</button>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl" @click.away="modalOpen = false">
            <h3 class="text-base font-bold text-slate-800" x-text="modalMode === 'add' ? 'Tambah Pelanggan Baru' : 'Edit Data Pelanggan'"></h3>
            <form @submit.prevent="saveCustomer()" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" x-model="form.name" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Email</label>
                    <input type="email" x-model="form.email" required class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">
                        Password <span x-show="modalMode === 'edit'" class="text-gray-400 normal-case font-normal">(Kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" x-model="form.password" :required="modalMode === 'add'" class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">No. Telepon / WhatsApp</label>
                    <input type="text" x-model="form.phone" class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Alamat</label>
                    <textarea x-model="form.address" rows="2" class="w-full px-3 py-2 border rounded-xl text-xs font-semibold outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="is_active" x-model="form.is_active" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-xs font-semibold text-gray-700">Akun Pelanggan Aktif</label>
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
    function customerApp() {
        return {
            customers: [], searchQuery: '', loading: false, modalOpen: false, modalMode: 'add',
            form: { user_id: null, name: '', email: '', password: '', phone: '', address: '', is_active: true },
            currentPage: 1, perPage: 10,

            initApp() { this.fetchCustomers(); },
            async fetchCustomers() {
                this.loading = true;
                const token = localStorage.getItem('auth_token');
                const res = await fetch('/api/v1/customers', { headers: { 'Authorization': `Bearer ${token}` } });
                const result = await res.json();
                if (result.success) this.customers = result.data;
                this.loading = false;
            },
            get filteredCustomers() {
                if (!this.searchQuery) return this.customers;
                const q = this.searchQuery.toLowerCase();
                return this.customers.filter(c => 
                    c.name.toLowerCase().includes(q) || 
                    c.email.toLowerCase().includes(q) || 
                    (c.phone || '').toLowerCase().includes(q)
                );
            },
            get paginatedCustomers() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredCustomers.slice(start, start + this.perPage);
            },
            get totalPages() { return Math.ceil(this.filteredCustomers.length / this.perPage) || 1; },
            get startItem() { return this.filteredCustomers.length ? (this.currentPage - 1) * this.perPage + 1 : 0; },
            get endItem() { return Math.min(this.currentPage * this.perPage, this.filteredCustomers.length); },

            openModal(mode, data = null) {
                this.modalMode = mode;
                this.form = data ? { ...data, password: '' } : { user_id: null, name: '', email: '', password: '', phone: '', address: '', is_active: true };
                this.modalOpen = true;
            },
            async saveCustomer() {
                const token = localStorage.getItem('auth_token');
                const isAdd = this.modalMode === 'add';
                const url = isAdd ? '/api/v1/customers' : `/api/v1/customers/${this.form.user_id}`;
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
                    this.fetchCustomers();
                } else {
                    alert(result.message || 'Gagal menyimpan data pelanggan.');
                }
            },
            async toggleStatus(c) {
                const token = localStorage.getItem('auth_token');
                const res = await fetch(`/api/v1/customers/${c.user_id}/toggle-status`, {
                    method: 'PATCH',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const result = await res.json();
                if (res.ok) {
                    c.is_active = !c.is_active;
                } else {
                    alert('Gagal mengubah status.');
                }
            },
            async deleteCustomer(id) {
                if (!confirm('Yakin ingin menghapus data pelanggan ini?')) return;
                const token = localStorage.getItem('auth_token');
                const res = await fetch(`/api/v1/customers/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const result = await res.json();
                alert(result.message);
                if (res.ok) this.fetchCustomers();
            }
        }
    }
</script>
@endpush
@endsection