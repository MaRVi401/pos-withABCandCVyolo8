@extends('layouts.admin')

@section('title', 'Laporan Penjualan - POS System')
@section('page_title', 'Laporan Penjualan & Pendapatan')

@section('content')
<div class="space-y-6" x-data="reportApp()" x-init="initApp()">

    <!-- Header Filter, Search & Export Buttons -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        
        <!-- Filter Tanggal & Search Input -->
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-gray-500 uppercase">Dari Tanggal</label>
                <input type="date" x-model="startDate" @change="fetchReports()"
                    class="block w-full px-3 py-2 bg-slate-50 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <span class="text-gray-400 font-bold hidden sm:inline pt-4">&rarr;</span>

            <div class="space-y-1">
                <label class="text-[11px] font-bold text-gray-500 uppercase">Sampai Tanggal</label>
                <input type="date" x-model="endDate" @change="fetchReports()"
                    class="block w-full px-3 py-2 bg-slate-50 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div class="space-y-1 flex-1 sm:w-64">
                <label class="text-[11px] font-bold text-gray-500 uppercase">Cari Transaksi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">🔍</span>
                    <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Invoice, kasir, produk..."
                        class="block w-full pl-9 pr-3 py-2 bg-slate-50 border border-gray-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <div class="pt-4">
                <button @click="fetchReports()" :disabled="loading"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                    <span x-show="!loading">🔍 Filter</span>
                    <span x-show="loading" x-cloak>🌀 Memuat...</span>
                </button>
            </div>
        </div>

        <!-- Tombol Export Excel & PDF -->
        <div class="flex items-center gap-2.5 w-full lg:w-auto justify-end">
            <button @click="exportExcel()" :disabled="transactions.length === 0"
                class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2">
                <span>📊</span>
                <span>Export Excel (.xlsx)</span>
            </button>
            <button @click="exportPDF()" :disabled="transactions.length === 0"
                class="bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2">
                <span>📄</span>
                <span>Export PDF</span>
            </button>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Omset -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                <span>Total Omset</span>
                <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">💰</span>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-800" x-text="formatRupiah(summary.total_sales)"></h3>
            <p class="text-[11px] text-slate-500">Total pendapatan kotor</p>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                <span>Jumlah Transaksi</span>
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">🧾</span>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-800" x-text="summary.total_count + ' Nota'"></h3>
            <p class="text-[11px] text-slate-500">Transaksi sukses</p>
        </div>

        <!-- Total Produk Terjual -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                <span>Produk Terjual</span>
                <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">📦</span>
            </div>
            <h3 class="text-2xl font-extrabold text-slate-800" x-text="summary.total_items_sold + ' Unit'"></h3>
            <p class="text-[11px] text-slate-500">Total item keluar</p>
        </div>

        <!-- Total Diskon Promo -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-wider">
                <span>Diskon Diberikan</span>
                <span class="p-2 bg-purple-50 text-purple-600 rounded-xl">🐝</span>
            </div>
            <h3 class="text-2xl font-extrabold text-purple-700" x-text="formatRupiah(summary.total_discount)"></h3>
            <p class="text-[11px] text-slate-500">Termasuk Promo ABC</p>
        </div>
    </div>

    <!-- Tabel Riwayat Penjualan -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" id="reportTableContainer">
        <div class="p-5 border-b border-gray-200 bg-slate-50/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800 text-sm">Rincian Transaksi Penjualan</h3>
            <span class="text-xs text-gray-500 font-semibold" x-text="'Periode: ' + summary.start_date + ' s/d ' + summary.end_date"></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-100 uppercase text-[10px] font-extrabold text-slate-500 tracking-wider">
                    <tr>
                        <th class="p-3.5">No. Invoice</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Kasir</th>
                        <th class="p-3.5">Detail Item Produk</th>
                        <th class="p-3.5 text-right">Total Normal</th>
                        <th class="p-3.5 text-right">Diskon</th>
                        <th class="p-3.5 text-right">Total Bayar</th>
                        <th class="p-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-400">
                                <span class="text-xl animate-spin">🌀</span> Memuat data laporan...
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && paginatedTransactions.length === 0">
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-400 font-semibold">
                                Tidak ada data transaksi yang sesuai.
                            </td>
                        </tr>
                    </template>

                    <template x-for="t in paginatedTransactions" :key="t.transaction_id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 font-bold text-indigo-700" x-text="t.invoice_number"></td>
                            <td class="p-3.5 text-gray-500 whitespace-nowrap" x-text="t.transaction_date"></td>
                            <td class="p-3.5 font-semibold text-gray-800 whitespace-nowrap" x-text="t.admin?.name || 'Kasir'"></td>
                            <td class="p-3.5">
                                <ul class="space-y-0.5">
                                    <template x-for="item in t.items" :key="item.item_id">
                                        <li class="text-[11px]" x-text="'• ' + (item.product?.product_name || 'Produk') + ' (' + item.quantity + 'x)'"></li>
                                    </template>
                                </ul>
                            </td>
                            <td class="p-3.5 text-right font-semibold text-gray-800 whitespace-nowrap" x-text="formatRupiah(t.total_amount)"></td>
                            <td class="p-3.5 text-right font-semibold text-red-600 whitespace-nowrap" x-text="formatRupiah(t.discount_amount)"></td>
                            <td class="p-3.5 text-right font-extrabold text-slate-900 whitespace-nowrap" x-text="formatRupiah(t.final_amount)"></td>
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">
                                    LUNAS
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Footer Pagination -->
        <div class="p-4 border-t border-gray-200 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs" x-show="filteredTransactions.length > 0">
            <div class="text-gray-500">
                Menampilkan <span class="font-bold text-gray-800" x-text="startItem"></span> - <span class="font-bold text-gray-800" x-text="endItem"></span> dari <span class="font-bold text-gray-800" x-text="filteredTransactions.length"></span> transaksi
            </div>

            <div class="flex items-center gap-1.5">
                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="px-3 py-1.5 border border-gray-300 rounded-lg font-bold bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    &laquo; Prev
                </button>

                <template x-for="page in totalPages" :key="page">
                    <button @click="currentPage = page"
                        :class="currentPage === page ? 'bg-indigo-600 text-white font-extrabold' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 font-bold'"
                        class="px-3 py-1.5 rounded-lg text-xs transition-colors" x-text="page"></button>
                </template>

                <button @click="currentPage++" :disabled="currentPage === totalPages"
                    class="px-3 py-1.5 border border-gray-300 rounded-lg font-bold bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next &raquo;
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function reportApp() {
        return {
            transactions: [],
            searchQuery: '',
            summary: {
                start_date: '',
                end_date: '',
                total_sales: 0,
                total_discount: 0,
                total_count: 0,
                total_items_sold: 0,
            },
            startDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
            endDate: new Date().toISOString().split('T')[0],
            loading: false,

            // Variabel Pagination
            currentPage: 1,
            perPage: 10,

            initApp() {
                this.fetchReports();
            },

            async fetchReports() {
                this.loading = true;
                const token = localStorage.getItem('auth_token');
                try {
                    const res = await fetch(`/api/v1/reports/sales?start_date=${this.startDate}&end_date=${this.endDate}`, {
                        headers: {
                            'Authorization': token ? `Bearer ${token}` : '',
                            'Accept': 'application/json'
                        }
                    });
                    const result = await res.json();
                    if (result.success) {
                        this.transactions = result.data;
                        this.summary = result.summary;
                        this.currentPage = 1; // Reset ke halaman 1 setiap filter berubah
                    }
                } catch (e) {
                    console.error('Gagal memuat laporan:', e);
                } finally {
                    this.loading = false;
                }
            },

            // Filtering berdasarkan Search Query
            get filteredTransactions() {
                if (!this.searchQuery) return this.transactions;

                const q = this.searchQuery.toLowerCase();
                return this.transactions.filter(t => {
                    const matchInvoice = t.invoice_number.toLowerCase().includes(q);
                    const matchAdmin   = (t.admin?.name || '').toLowerCase().includes(q);
                    const matchProduct = t.items.some(i => (i.product?.product_name || '').toLowerCase().includes(q));

                    return matchInvoice || matchAdmin || matchProduct;
                });
            },

            // Data Terpaginasi
            get paginatedTransactions() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredTransactions.slice(start, start + this.perPage);
            },

            get totalPages() {
                return Math.ceil(this.filteredTransactions.length / this.perPage) || 1;
            },

            get startItem() {
                if (this.filteredTransactions.length === 0) return 0;
                return (this.currentPage - 1) * this.perPage + 1;
            },

            get endItem() {
                return Math.min(this.currentPage * this.perPage, this.filteredTransactions.length);
            },

            // Download File Excel (.xlsx) Rapi via Fetch Blob (Membawa Token)
            async exportExcel() {
                const token = localStorage.getItem('auth_token');
                const url = `/api/v1/reports/sales/export-excel?start_date=${this.startDate}&end_date=${this.endDate}`;
                
                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Authorization': token ? `Bearer ${token}` : '',
                            'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Gagal mengunduh file Excel. Pastikan Anda telah login sebagai Admin.');
                    }

                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = `Laporan_Penjualan_${this.startDate}_sd_${this.endDate}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(downloadUrl);
                } catch (e) {
                    alert(e.message);
                }
            },

            // Download File PDF Rapi via Fetch Blob (Membawa Token)
            async exportPDF() {
                const token = localStorage.getItem('auth_token');
                const url = `/api/v1/reports/sales/export-pdf?start_date=${this.startDate}&end_date=${this.endDate}`;

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Authorization': token ? `Bearer ${token}` : '',
                            'Accept': 'application/pdf'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Gagal mengunduh file PDF. Pastikan Anda telah login sebagai Admin.');
                    }

                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = `Laporan_Penjualan_${this.startDate}_sd_${this.endDate}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(downloadUrl);
                } catch (e) {
                    alert(e.message);
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