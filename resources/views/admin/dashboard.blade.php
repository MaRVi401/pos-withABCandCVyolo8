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

        <!-- Live CCTV Visitor Counter & Height Analytics (YOLOv8 & Flask Integration) -->
        <div class="bg-slate-900 text-white rounded-2xl shadow-xl border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
                <div class="flex items-center gap-3">
                    <span class="text-xl">📹</span>
                    <div>
                        <h3 class="font-bold text-sm text-cyan-400">Live CCTV Analytics & Visitor Counter (YOLOv8)</h3>
                        <p class="text-xs text-slate-400">Deteksi, Estimasi Gender, Tinggi Badan & Analytics Real-time via Python
                            Flask</p>
                    </div>
                </div>
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-950 text-emerald-400 border border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> LIVE STREAM
                </span>
            </div>

            <div class="p-6">
                <!-- GRID UTAMA: STREAM CCTV & METRICS CARD -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <!-- Stream Video Feed dari Flask Service -->
                    <div class="lg:col-span-2">
                        <div
                            class="relative bg-black rounded-xl overflow-hidden border border-slate-800 min-h-[340px] flex items-center justify-center shadow-inner">
                            <img id="yolo-stream-img" :src="flaskUrl ? `${flaskUrl}/video_feed` : ''" alt="CCTV Stream Feed"
                                class="w-full h-auto object-cover rounded-xl"
                                onerror="this.onerror=null; this.src='https://via.placeholder.com/640x480?text=CCTV+Stream+Offline';" />
                        </div>
                        <!-- Kalibrasi lantai -->
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <button type="button" @click="openCalibration()"
                                class="px-4 py-2 rounded-lg text-xs font-bold bg-cyan-600 hover:bg-cyan-500 text-white">
                                📐 Kalibrasi Lantai
                            </button>
                            <button type="button" x-show="calibInfo" @click="toggleCalibLine()"
                                class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-100 border border-slate-700">
                                <span x-text="calibLineVisible ? '🙈 Sembunyikan Garis' : '👁 Tampilkan Garis'"></span>
                            </button>
                            <span x-show="calibInfo" class="text-[11px] font-semibold text-emerald-400">
                                ✔ Terkalibrasi: <span x-text="calibInfo ? `${calibInfo.width_cm} × ${calibInfo.length_cm} cm` : ''"></span>
                            </span>
                            <span x-show="!calibInfo" class="text-[11px] font-semibold text-amber-400">
                                ⚠ Belum dikalibrasi, tinggi badan masih estimasi kasar.
                            </span>
                        </div>
                    </div>

                    <!-- Side Cards Stats Real-time -->
                    <div class="space-y-4">
                        <!-- Card 1: Jumlah Pengunjung -->
                        <div
                            class="p-5 bg-gradient-to-br from-cyan-600 to-blue-700 rounded-xl shadow-lg border border-cyan-500/30 text-center">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-cyan-100">Pengunjung Terdeteksi
                            </p>
                            <h1 class="text-5xl font-extrabold text-white mt-1" x-text="visitorCount">0</h1>
                            <p class="text-[11px] text-cyan-200 mt-1">Orang Dalam Area CCTV</p>
                        </div>

                        <!-- Card Gender Split: Laki-laki & Perempuan -->
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                class="p-4 bg-gradient-to-br from-sky-600 to-blue-800 rounded-xl shadow-lg border border-sky-400/30 text-center">
                                <p class="text-[11px] font-extrabold uppercase tracking-wider text-sky-100">Laki-Laki</p>
                                <h2 class="text-3xl font-extrabold text-white mt-1" x-text="maleCount">0</h2>
                                <p class="text-[10px] text-sky-200 mt-0.5">Estimasi</p>
                            </div>

                            <div
                                class="p-4 bg-gradient-to-br from-pink-600 to-rose-700 rounded-xl shadow-lg border border-pink-400/30 text-center">
                                <p class="text-[11px] font-extrabold uppercase tracking-wider text-pink-100">Perempuan</p>
                                <h2 class="text-3xl font-extrabold text-white mt-1" x-text="femaleCount">0</h2>
                                <p class="text-[10px] text-pink-200 mt-0.5">Estimasi</p>
                            </div>
                        </div>

                        <!-- Card 2: Rata-Rata Tinggi Badan -->
                        <div
                            class="p-5 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-xl shadow-lg border border-purple-500/30 text-center">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-purple-100">Rata-Rata Tinggi
                                Badan</p>
                            <div class="flex items-baseline justify-center gap-1 mt-1">
                                <h1 class="text-4xl font-extrabold text-white" x-text="avgHeight">0.0</h1>
                                <span class="text-lg font-bold text-purple-200">cm</span>
                            </div>
                            <p class="text-[11px] text-purple-200 mt-1">Estimasi Real-time YOLOv8</p>
                        </div>

                        <!-- System Technical Details -->
                        <div
                            class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 text-xs space-y-2 text-slate-300">
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                <span class="text-slate-400">Status Flask:</span>
                                <span class="font-bold" :class="flaskConnected ? 'text-emerald-400' : 'text-red-400'"
                                    x-text="flaskConnected ? 'Connected (Port 5000)' : 'Disconnected'">Connected (Port 5000)</span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                <span class="text-slate-400">Model AI:</span>
                                <span class="font-semibold text-slate-200">YOLOv8 Ensemble (pt)</span>
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

        <!-- MODAL KALIBRASI LANTAI -->
        <div x-show="calibOpen" x-cloak @keydown.escape.window="calibOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div class="bg-slate-900 text-slate-100 rounded-2xl border border-slate-700 shadow-2xl w-full max-w-4xl max-h-[92vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-cyan-400">Kalibrasi Lantai CCTV</h3>
                    <button type="button" @click="calibOpen = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>

                <div class="p-4 space-y-4">
                    <p class="text-xs text-slate-300">
                        Tarik <b>garis kotak</b> sampai pas dengan tepi lantai di gambar. Titik putih di sudut dipakai
                        untuk mengatur kemiringan. Tarik bagian dalam kotak untuk menggeser seluruhnya.
                        <b class="text-cyan-300">Garis biru (atas &amp; bawah) = Lebar</b>,
                        <b class="text-amber-300">garis kuning (kiri &amp; kanan) = Panjang</b>.
                        Kalau sudut lantai yang sebenarnya ada di luar jangkauan kamera, tarik titik atau garis ke area gelap di
                        sekeliling gambar (garis putus-putus = tepi kamera), ikuti arah garis tepi lantai.
                    </p>

                    <div class="relative bg-black rounded-xl border border-slate-800 select-none p-4 flex justify-center">
                        <template x-if="calibLoading">
                            <div class="p-10 text-center text-xs text-slate-400">Mengambil gambar dari CCTV...</div>
                        </template>
                        <template x-if="!calibLoading && calibImg">
                            <div id="calib-box" class="relative" style="touch-action: none; background: #0b1220"
                                :style="`width: min(100%, calc(66vh * ${calibFrame.w / calibFrame.h})); aspect-ratio: ${calibFrame.w} / ${calibFrame.h}`"
                                @pointermove.window="onCalibDrag($event)" @pointerup.window="calibDrag = null"
                                @pointercancel.window="calibDrag = null">

                                <!-- Gambar CCTV di tengah; area gelap di sekelilingnya = di luar jangkauan kamera -->
                                <img :src="calibImg" alt="Snapshot CCTV" class="absolute select-none" draggable="false"
                                    :style="`left: ${calibMargin / (1 + 2 * calibMargin) * 100}%; top: ${calibMargin / (1 + 2 * calibMargin) * 100}%; width: ${100 / (1 + 2 * calibMargin)}%; height: ${100 / (1 + 2 * calibMargin)}%`" />
                                <div class="absolute pointer-events-none"
                                    style="border: 1px dashed rgba(255,255,255,0.45)"
                                    :style="`left: ${calibMargin / (1 + 2 * calibMargin) * 100}%; top: ${calibMargin / (1 + 2 * calibMargin) * 100}%; width: ${100 / (1 + 2 * calibMargin)}%; height: ${100 / (1 + 2 * calibMargin)}%`"></div>

                                <!-- Kotak lantai: tarik GARIS untuk menggeser sisi, tarik bagian dalam untuk menggeser semuanya -->
                                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute"
                                    style="left: 0; top: 0; width: 100%; height: 100%; pointer-events: none">
                                    <polygon :points="calibPoints.map(p => `${stageX(p[0])},${stageY(p[1])}`).join(' ')"
                                        fill="rgba(34,211,238,0.15)" style="pointer-events: all; cursor: move"
                                        @pointerdown.prevent="startCalibDrag($event, 'all')"></polygon>
                                    <line :x1="pctX(0)" :y1="pctY(0)" :x2="pctX(1)" :y2="pctY(1)" stroke="#22d3ee" stroke-width="3" vector-effect="non-scaling-stroke" style="pointer-events: none"></line>
                                    <line :x1="pctX(0)" :y1="pctY(0)" :x2="pctX(1)" :y2="pctY(1)" stroke="transparent" stroke-width="22" vector-effect="non-scaling-stroke"
                                        style="pointer-events: stroke; cursor: move" @pointerdown.prevent="startCalibDrag($event, 'e0')"></line>
                                    <line :x1="pctX(1)" :y1="pctY(1)" :x2="pctX(2)" :y2="pctY(2)" stroke="#fbbf24" stroke-width="3" vector-effect="non-scaling-stroke" style="pointer-events: none"></line>
                                    <line :x1="pctX(1)" :y1="pctY(1)" :x2="pctX(2)" :y2="pctY(2)" stroke="transparent" stroke-width="22" vector-effect="non-scaling-stroke"
                                        style="pointer-events: stroke; cursor: move" @pointerdown.prevent="startCalibDrag($event, 'e1')"></line>
                                    <line :x1="pctX(2)" :y1="pctY(2)" :x2="pctX(3)" :y2="pctY(3)" stroke="#22d3ee" stroke-width="3" vector-effect="non-scaling-stroke" style="pointer-events: none"></line>
                                    <line :x1="pctX(2)" :y1="pctY(2)" :x2="pctX(3)" :y2="pctY(3)" stroke="transparent" stroke-width="22" vector-effect="non-scaling-stroke"
                                        style="pointer-events: stroke; cursor: move" @pointerdown.prevent="startCalibDrag($event, 'e2')"></line>
                                    <line :x1="pctX(3)" :y1="pctY(3)" :x2="pctX(0)" :y2="pctY(0)" stroke="#fbbf24" stroke-width="3" vector-effect="non-scaling-stroke" style="pointer-events: none"></line>
                                    <line :x1="pctX(3)" :y1="pctY(3)" :x2="pctX(0)" :y2="pctY(0)" stroke="transparent" stroke-width="22" vector-effect="non-scaling-stroke"
                                        style="pointer-events: stroke; cursor: move" @pointerdown.prevent="startCalibDrag($event, 'e3')"></line>
                                </svg>

                                <!-- Titik sudut kecil (tanpa tulisan) untuk mengatur kemiringan/perspektif -->
                                <template x-for="(p, i) in calibPoints" :key="i">
                                    <div class="absolute flex items-center justify-center"
                                        style="width: 34px; height: 34px; transform: translate(-50%, -50%); cursor: grab; touch-action: none"
                                        :style="`left: ${stageX(p[0])}%; top: ${stageY(p[1])}%`"
                                        @pointerdown.prevent="startCalibDrag($event, i)">
                                        <span class="block rounded-full bg-white border-2 border-slate-900 shadow"
                                            style="width: 12px; height: 12px"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <p x-show="calibError && !calibLoading && !calibImg" class="p-6 text-center text-xs text-red-400" x-text="calibError"></p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                        <label class="text-xs text-slate-300">
                            Lebar lantai (garis biru), cm
                            <input type="number" min="1" step="any" x-model.number="calibWidth"
                                class="mt-1 w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-cyan-500" />
                        </label>
                        <label class="text-xs text-slate-300">
                            Panjang lantai (garis kuning), cm
                            <input type="number" min="1" step="any" x-model.number="calibLength"
                                class="mt-1 w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-cyan-500" />
                        </label>
                        <div class="flex gap-2">
                            <button type="button" @click="calibPoints = defaultQuad()"
                                class="flex-1 px-3 py-2 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-700 border border-slate-700">Reset kotak</button>
                            <button type="button" @click="reloadSnapshot()"
                                class="flex-1 px-3 py-2 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-700 border border-slate-700">Ambil ulang</button>
                        </div>
                    </div>

                    <p class="text-xs" :class="calibMsgOk ? 'text-emerald-400' : 'text-red-400'" x-show="calibMsg" x-text="calibMsg"></p>

                    <div class="flex flex-wrap justify-between gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="deleteCalibration()" x-show="calibInfo"
                            class="px-4 py-2 rounded-lg text-xs font-bold bg-red-950 hover:bg-red-900 text-red-300 border border-red-900">
                            Hapus kalibrasi
                        </button>
                        <span x-show="!calibInfo"></span>
                        <div class="flex gap-2">
                            <button type="button" @click="calibOpen = false"
                                class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-700 border border-slate-700">Tutup</button>
                            <button type="button" @click="saveCalibration()"
                                :disabled="calibPoints.length !== 4 || !(calibWidth > 0) || !(calibLength > 0) || calibSaving"
                                class="px-4 py-2 rounded-lg text-xs font-bold bg-cyan-600 hover:bg-cyan-500 text-white disabled:opacity-40 disabled:cursor-not-allowed">
                                <span x-show="!calibSaving">Simpan Kalibrasi</span>
                                <span x-show="calibSaving">Menyimpan...</span>
                            </button>
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
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Memuat data dari REST API...
                            </td>
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
                    maleCount: 0,
                    femaleCount: 0,
                    avgHeight: 0.0,
                    flaskConnected: false,
                    flaskUrl: "{{ rtrim(config('app.flask_url', 'http://127.0.0.1:5000'), '/') }}",

                    // --- Kalibrasi lantai ---
                    calibInfo: null,
                    calibOpen: false,
                    calibLoading: false,
                    calibSaving: false,
                    calibImg: '',
                    calibFrame: { w: 1, h: 1 },
                    calibPoints: [],
                    calibDrag: null,
                    calibMargin: 0.25,
                    calibLineVisible: true,
                    calibDragIdx: [],
                    calibDragLast: null,
                    calibWidth: null,
                    calibLength: null,
                    calibMsg: '',
                    calibMsgOk: false,
                    calibError: '',

                    initDashboard() {
                        const user = localStorage.getItem('user_info');
                        if (user) {
                            this.userInfo = JSON.parse(user);
                        }
                        this.fetchProducts();
                        this.startYoloStatsPolling();
                        this.loadCalibration();
                    },

                    // ================= KALIBRASI LANTAI =================
                    async loadCalibration() {
                        try {
                            const r = await fetch(`${this.flaskUrl}/api/calibration`, {
                                headers: { 'ngrok-skip-browser-warning': 'true' }
                            });
                            const d = await r.json();
                            this.calibInfo = d.calibration || null;
                            // samakan tampilan garis di video dengan pilihan terakhir pengguna
                            const saved = localStorage.getItem('calib_line_visible');
                            if (saved !== null) this.setCalibLine(saved === '1');
                        } catch (e) {
                            this.calibInfo = null;
                        }
                    },

                    async setCalibLine(show) {
                        try {
                            const r = await fetch(`${this.flaskUrl}/api/calibration/overlay`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'ngrok-skip-browser-warning': 'true' },
                                body: JSON.stringify({ show })
                            });
                            const d = await r.json();
                            this.calibLineVisible = !!d.show;
                            localStorage.setItem('calib_line_visible', d.show ? '1' : '0');
                        } catch (e) {
                            console.error('Gagal mengubah tampilan garis kalibrasi:', e);
                        }
                    },

                    toggleCalibLine() {
                        return this.setCalibLine(!this.calibLineVisible);
                    },

                    async openCalibration() {
                        this.calibOpen = true;
                        this.calibMsg = '';
                        if (this.calibInfo) {
                            this.calibWidth = this.calibInfo.width_cm;
                            this.calibLength = this.calibInfo.length_cm;
                        }
                        await this.reloadSnapshot();
                    },

                    defaultQuad() {
                        const w = this.calibFrame.w, h = this.calibFrame.h;
                        return [
                            [w * 0.30, h * 0.50], [w * 0.70, h * 0.50],
                            [w * 0.85, h * 0.88], [w * 0.15, h * 0.88]
                        ];
                    },

                    async reloadSnapshot() {
                        this.calibLoading = true;
                        this.calibError = '';
                        try {
                            const r = await fetch(`${this.flaskUrl}/api/snapshot?t=${Date.now()}`, {
                                headers: { 'ngrok-skip-browser-warning': 'true' }
                            });
                            if (!r.ok) throw new Error('Stream CCTV belum aktif. Pastikan video tampil lalu coba lagi.');
                            this.calibFrame = {
                                w: parseInt(r.headers.get('X-Frame-Width')) || 1,
                                h: parseInt(r.headers.get('X-Frame-Height')) || 1
                            };
                            const blob = await r.blob();
                            if (this.calibImg) URL.revokeObjectURL(this.calibImg);
                            this.calibImg = URL.createObjectURL(blob);
                            if (this.calibInfo && this.calibInfo.points && this.calibInfo.frame_w) {
                                const k = this.calibFrame.w / this.calibInfo.frame_w;
                                this.calibPoints = this.calibInfo.points.map(p => [p[0] * k, p[1] * k]);
                            } else {
                                this.calibPoints = this.defaultQuad();
                            }
                        } catch (e) {
                            this.calibImg = '';
                            this.calibError = e.message || 'Gagal mengambil gambar dari CCTV.';
                        } finally {
                            this.calibLoading = false;
                        }
                    },

                    stageX(x) {
                        return (x + this.calibMargin * this.calibFrame.w) / (this.calibFrame.w * (1 + 2 * this.calibMargin)) * 100;
                    },

                    stageY(y) {
                        return (y + this.calibMargin * this.calibFrame.h) / (this.calibFrame.h * (1 + 2 * this.calibMargin)) * 100;
                    },

                    pctX(i) {
                        return this.calibPoints.length === 4 ? this.stageX(this.calibPoints[i][0]) : 0;
                    },

                    pctY(i) {
                        return this.calibPoints.length === 4 ? this.stageY(this.calibPoints[i][1]) : 0;
                    },

                    calibXY(ev) {
                        const rect = document.getElementById('calib-box').getBoundingClientRect();
                        const m = this.calibMargin;
                        return [
                            (ev.clientX - rect.left) / rect.width * this.calibFrame.w * (1 + 2 * m) - m * this.calibFrame.w,
                            (ev.clientY - rect.top) / rect.height * this.calibFrame.h * (1 + 2 * m) - m * this.calibFrame.h
                        ];
                    },

                    startCalibDrag(ev, which) {
                        this.calibDrag = which;
                        if (which === 'all') {
                            this.calibDragIdx = [0, 1, 2, 3];
                        } else if (typeof which === 'string') {          // 'e0'..'e3' = satu sisi (2 titik)
                            const k = parseInt(which.slice(1));
                            this.calibDragIdx = [k, (k + 1) % 4];
                        } else {                                          // angka = satu sudut
                            this.calibDragIdx = [which];
                        }
                        this.calibDragLast = this.calibXY(ev);
                    },

                    onCalibDrag(ev) {
                        if (this.calibDrag === null) return;
                        const [x, y] = this.calibXY(ev);
                        const m = this.calibMargin, W = this.calibFrame.w, H = this.calibFrame.h;
                        const minX = -m * W, maxX = W + m * W, minY = -m * H, maxY = H + m * H;
                        const idx = this.calibDragIdx;
                        let dx = x - this.calibDragLast[0], dy = y - this.calibDragLast[1];
                        const xs = idx.map(i => this.calibPoints[i][0]), ys = idx.map(i => this.calibPoints[i][1]);
                        dx = Math.max(minX - Math.min(...xs), Math.min(maxX - Math.max(...xs), dx));
                        dy = Math.max(minY - Math.min(...ys), Math.min(maxY - Math.max(...ys), dy));
                        this.calibPoints = this.calibPoints.map((p, i) => idx.includes(i) ? [p[0] + dx, p[1] + dy] : p);
                        this.calibDragLast = [this.calibDragLast[0] + dx, this.calibDragLast[1] + dy];
                    },

                    async saveCalibration() {
                        this.calibSaving = true;
                        this.calibMsg = '';
                        try {
                            const r = await fetch(`${this.flaskUrl}/api/calibration`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'ngrok-skip-browser-warning': 'true' },
                                body: JSON.stringify({
                                    points: this.calibPoints.map(p => [Math.round(p[0] * 10) / 10, Math.round(p[1] * 10) / 10]),
                                    width_cm: this.calibWidth,
                                    length_cm: this.calibLength,
                                    frame_w: this.calibFrame.w,
                                    frame_h: this.calibFrame.h
                                })
                            });
                            const d = await r.json();
                            if (!r.ok || !d.success) throw new Error(d.message || 'Gagal menyimpan kalibrasi.');
                            this.calibInfo = d.calibration;
                            this.calibMsgOk = true;
                            this.calibMsg = 'Kalibrasi tersimpan.';
                            setTimeout(() => { this.calibOpen = false; }, 900);
                        } catch (e) {
                            this.calibMsgOk = false;
                            this.calibMsg = e.message;
                        } finally {
                            this.calibSaving = false;
                        }
                    },

                    async deleteCalibration() {
                        if (!confirm('Hapus kalibrasi lantai?')) return;
                        try {
                            await fetch(`${this.flaskUrl}/api/calibration`, {
                                method: 'DELETE', headers: { 'ngrok-skip-browser-warning': 'true' }
                            });
                            this.calibInfo = null;
                            this.calibPoints = this.defaultQuad();
                            this.calibMsgOk = true;
                            this.calibMsg = 'Kalibrasi dihapus.';
                        } catch (e) {
                            this.calibMsgOk = false;
                            this.calibMsg = 'Gagal menghapus kalibrasi.';
                        }
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

                    startYoloStatsPolling() {
                        // Polling statistik JSON saja dari Flask (/api/stats) setiap 1 detik
                        const fetchYoloStats = async () => {
                            try {
                                const response = await fetch(`${this.flaskUrl}/api/stats`, {
                                    headers: {
                                        'ngrok-skip-browser-warning': 'true'
                                    }
                                });

                                if (response.ok) {
                                    const data = await response.json();
                                    this.visitorCount = data.detected_count ?? data.total_detected ?? 0;
                                    this.maleCount = data.male_count ?? 0;
                                    this.femaleCount = data.female_count ?? 0;
                                    this.avgHeight = data.avg_height_cm ?? 0.0;
                                    this.flaskConnected = true;
                                } else {
                                    this.flaskConnected = false;
                                }
                            } catch (error) {
                                this.flaskConnected = false;
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
