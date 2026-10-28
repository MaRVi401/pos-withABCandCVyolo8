<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - POS System')</title>
    <link rel="icon" type="image/x-icon"
        href="https://i.pinimg.com/736x/90/0c/9e/900c9e431963432cb0f5fbe7c5f5af91.jpg">
    
    <!-- SCRIPT CEK TOKEN AUTHENTICATION SEBELUM MEMATUHI LAYOUT -->
    <script>
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '/login';
        }
    </script>

    <!-- Tailwind CSS, Alpine.js & Marked.js CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <!-- Custom Style untuk Merapikan Output Markdown & Tabel Chatbot -->
    <style>
        [x-cloak] { display: none !important; }

        /* Custom Styling Tabel & Content Chatbot */
        .chat-content {
            font-size: 0.75rem; /* 12px */
            line-height: 1.5;
        }

        .chat-content p {
            margin-bottom: 0.5rem;
        }

        .chat-content p:last-child {
            margin-bottom: 0;
        }

        .chat-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.7rem;
            background-color: #ffffff;
            border-radius: 0.375rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .chat-content th {
            background-color: #f1f5f9; /* Slate 100 */
            color: #1e293b; /* Slate 800 */
            font-weight: 600;
            text-align: left;
            padding: 6px 8px;
            border-bottom: 2px solid #cbd5e1;
        }

        .chat-content td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        /* Zebra striping untuk baris tabel */
        .chat-content tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .chat-content tr:hover {
            background-color: #f1f5f9;
        }

        /* Formatting List / Poin */
        .chat-content ul {
            list-style-type: disc;
            padding-left: 1.25rem;
            margin-top: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .chat-content ol {
            list-style-type: decimal;
            padding-left: 1.25rem;
            margin-top: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .chat-content li {
            margin-bottom: 0.25rem;
        }

        .chat-content strong {
            font-weight: 600;
            color: #0f172a;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans antialiased" x-data="adminLayout()">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR PANEL ADMIN -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between hidden md:flex shrink-0">
            <div>
                <!-- Brand Logo / Header Sidebar -->
                <div class="h-16 flex items-center justify-center border-b border-slate-800 px-4">
                    <h1 class="text-xl font-bold tracking-wider text-indigo-400">POS ECOSYSTEM</h1>
                </div>

                <!-- Navigation Menu Sidebar -->
                <nav class="mt-6 px-4 space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ url('/admin/dashboard') }}"
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/dashboard*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <span>Dashboard</span>
                    </a>

                    <div class="pt-4 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Katalog & Stok</div>

                    <!-- Kategori Produk -->
                    <a href="{{ url('/admin/categories') }}"
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/categories*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <span>Kategori Produk</span>
                    </a>

                    <!-- Data Produk -->
                    <a href="{{ url('/admin/products') }}"
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/products*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <span>Data Produk</span>
                    </a>

                    <!-- Penyesuaian Stok -->
                    <a href="{{ url('/admin/stock-adjustments') }}"
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/stock-adjustments*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <span>Penyesuaian Stok</span>
                    </a>

                    <div class="pt-4 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Transaksi & POS</div>

                    <!-- Kasir (POS) -->
                    <a href="{{ url('/admin/pos') }}"
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/pos*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <span>Kasir (POS)</span>
                    </a>

                    <!-- Laporan Penjualan -->
                    <a href="{{ url('/admin/reports') }}"
                        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/reports*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <span>Laporan Penjualan</span>
                    </a>

                    <div class="pt-4 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Smart Optimasi</div>

                    <!-- Smart Bundling (ABC) -->
                    <a href="{{ url('/admin/abc') }}"
                        class="flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/abc*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <span>Smart Bundling (ABC)</span>
                        <span class="bg-indigo-500 text-xs px-2 py-0.5 rounded-full text-white font-bold">AI</span>
                    </a>

                    <div class="pt-4 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Manajemen Pengguna</div>

                    <!-- Data Pelanggan -->
                    <a href="{{ url('/admin/customers') }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/customers*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800' }}">
                        <span>Data Pelanggan</span>
                    </a>
                </nav>
            </div>

            <!-- Profile Info & Logout Footer Sidebar -->
            <div class="p-4 border-t border-slate-800 flex items-center justify-between">
                <div class="text-sm overflow-hidden">
                    <p class="font-semibold text-white truncate" x-text="user.name || 'Ahmad Yassin'"></p>
                    <p class="text-xs text-slate-400 truncate" x-text="user.role_name || 'Admin Store'"></p>
                </div>
                <button @click="logout()" title="Keluar / Logout"
                    class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col overflow-y-auto">

            <!-- TOP NAVBAR -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shrink-0">
                <h2 class="text-lg font-semibold text-gray-800">@yield('page_title', 'Dashboard Panel')</h2>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-500 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="hidden sm:inline">System Online</span>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>

        <!-- FLOATING CHATBOT WIDGET -->
        <div class="fixed bottom-6 right-6 z-50">
            <!-- Chat Toggle Button -->
            <button @click="chatOpen = !chatOpen"
                class="bg-indigo-600 hover:bg-indigo-700 text-white p-4 rounded-full shadow-2xl flex items-center justify-center transition-all hover:scale-105">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                    </path>
                </svg>
            </button>

            <!-- Chat Modal Window -->
            <div x-show="chatOpen" x-transition x-cloak
                class="absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col h-[480px]">

                <!-- Header Chat -->
                <div class="bg-indigo-600 text-white p-4 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="font-bold text-sm">AI Operational Assistant</h3>
                        <p class="text-xs text-indigo-200">Asisten cerdas pencarian stok & transaksi</p>
                    </div>
                    <button @click="chatOpen = false"
                        class="text-indigo-200 hover:text-white font-bold text-lg">&times;</button>
                </div>

                <!-- Chat Container / Messages List -->
                <div id="chatContainer" class="p-4 flex-1 overflow-y-auto bg-gray-50 space-y-3">
                    <template x-for="(msg, index) in messages" :key="index">
                        <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="msg.role === 'user' 
                                ? 'bg-indigo-600 text-white p-3 rounded-xl max-w-[85%] leading-relaxed' 
                                : 'bg-white text-gray-800 p-3 rounded-xl max-w-[90%] leading-relaxed shadow-sm border border-gray-100'">
                                
                                <!-- Render HTML via Marked.js dengan Styling CSS Custom -->
                                <div class="chat-content overflow-x-auto" x-html="marked.parse(msg.content || '')"></div>
                            </div>
                        </div>
                    </template>

                    <!-- Loading Typing Indicator -->
                    <div x-show="loading" class="flex justify-start">
                        <div class="bg-gray-200 text-gray-600 p-3 rounded-xl max-w-[85%] flex items-center gap-1">
                            <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></span>
                            <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                            <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                        </div>
                    </div>
                </div>

                <!-- Input Box & Form -->
                <form @submit.prevent="sendMessage()" class="p-3 border-t bg-white flex gap-2 shrink-0">
                    <input type="text" x-model="newMessage" :disabled="loading"
                        placeholder="Tanyakan sesuatu ke AI..."
                        class="flex-1 text-xs border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:bg-gray-100">
                    <button type="submit" :disabled="loading || !newMessage.trim()"
                        class="bg-indigo-600 text-white text-xs px-3 py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        Kirim
                    </button>
                </form>
            </div>
        </div>

    </div>

    <script>
        // 1. Cek keberadaan token lokal & role Admin (Redirect jika bukan admin/tidak ada token)
        const tokenCheck = localStorage.getItem('auth_token');
        const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');

        if (!tokenCheck || (userInfo.role_id && parseInt(userInfo.role_id) !== 1)) {
            alert('Akses khusus Admin! Akun Customer tidak diizinkan masuk ke Web Admin.');
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_info');
            window.location.href = '/login';
        }

        // 2. Interceptor global: Jika API membalas 401/403, otomatis logout & redirect
        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            const response = await originalFetch(...args);

            if (response.status === 401 || response.status === 403) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_info');
                alert(response.status === 403 ?
                    'Akses ditolak. Anda tidak memiliki hak akses Admin.' :
                    'Sesi login Anda telah berakhir atau database telah di-reset. Silakan login kembali.');
                window.location.href = '/login';
            }

            return response;
        };

        // 3. Alpine.js Component untuk Layout Admin
        function adminLayout() {
            return {
                chatOpen: false,
                loading: false,
                newMessage: '',
                user: JSON.parse(localStorage.getItem('user_info') || '{}'),
                messages: [{
                    role: 'assistant',
                    content: 'Halo Admin! Ada yang bisa saya bantu cek seputar stok barang atau ringkasan penjualan hari ini?'
                }],

                async sendMessage() {
                    if (!this.newMessage.trim() || this.loading) return;

                    const userText = this.newMessage.trim();

                    // 1. Tambahkan pesan user ke UI
                    this.messages.push({
                        role: 'user',
                        content: userText
                    });
                    this.newMessage = '';
                    this.loading = true;
                    this.scrollToBottom();

                    // 2. Ambil token & history percakapan
                    const token = localStorage.getItem('auth_token');
                    const history = this.messages.slice(0, -1); // Kirim riwayat obrolan terdahulu

                    try {
                        const response = await fetch('/api/v1/chatbot/chat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${token}`
                            },
                            body: JSON.stringify({
                                message: userText,
                                history: history
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.status === 'success') {
                            this.messages.push({
                                role: 'assistant',
                                content: data.reply
                            });
                        } else {
                            this.messages.push({
                                role: 'assistant',
                                content: data.message || 'Maaf, terjadi kesalahan saat menghubungi server AI.'
                            });
                        }
                    } catch (error) {
                        console.error('Error Chatbot API:', error);
                        this.messages.push({
                            role: 'assistant',
                            content: 'Gagal terhubung ke server AI. Pastikan koneksi internet Anda stabil.'
                        });
                    } finally {
                        this.loading = false;
                        this.scrollToBottom();
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('chatContainer');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                async logout() {
                    if (confirm("Apakah Anda yakin ingin keluar dari sistem POS?")) {
                        const token = localStorage.getItem('auth_token');

                        try {
                            await fetch('/api/v1/auth/logout', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'Authorization': `Bearer ${token}`
                                }
                            });
                        } catch (e) {
                            console.error("Gagal revoking token di API:", e);
                        }

                        alert("Anda telah berhasil logout.");
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user_info');
                        window.location.href = '/';
                    }
                }
            }
        }
    </script>

    @stack('scripts')
</body>

</html>