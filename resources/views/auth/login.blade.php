<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - POS System Admin</title>
    <link rel="icon" type="image/x-icon"
        href="https://i.pinimg.com/736x/90/0c/9e/900c9e431963432cb0f5fbe7c5f5af91.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-slate-900 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8" x-data="loginApp()">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">POS Ecosystem</h1>
            <p class="text-sm text-gray-500 mt-1">Masuk ke Panel Web Admin Kasir</p>
        </div>

        <!-- Alert Error Login -->
        <div x-show="errorMessage" x-cloak
            class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 text-xs font-semibold rounded-xl flex items-center gap-2">
            <span>⚠️</span>
            <span x-text="errorMessage"></span>
        </div>

        <!-- Form Login -->
        <form @submit.prevent="submitLogin()" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email Admin</label>
                <input type="email" x-model="email" placeholder="admin@pos.com" required
                    class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Password</label>
                <input type="password" x-model="password" placeholder="••••••••" required
                    class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
            </div>

            <button type="submit" :disabled="loading"
                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-sm py-2.5 rounded-xl shadow transition-all flex items-center justify-center gap-2">
                <span x-show="!loading">Masuk ke System</span>
                <span x-show="loading" x-cloak class="flex items-center gap-2">
                    <span class="animate-spin">🌀</span> Memproses...
                </span>
            </button>
        </form>

        <!-- Informasi Akun Testing -->
        <div class="mt-6 space-y-2">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600">
                <p class="font-bold text-slate-800 mb-1 flex items-center justify-between">
                    <span>Akun Testing Admin:</span>
                    <span class="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded-full uppercase">Bisa Login</span>
                </p>
                <p><strong>Email:</strong> admin@pos.com</p>
                <p><strong>Password:</strong> 12345678</p>
            </div>
            
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600">
                <p class="font-bold text-slate-800 mb-1 flex items-center justify-between">
                    <span>Akun Testing Customer:</span>
                    <span class="bg-red-100 text-red-800 text-[10px] px-2 py-0.5 rounded-full uppercase">Akses Ditolak</span>
                </p>
                <p><strong>Email:</strong> customer@pos.com</p>
                <p><strong>Password:</strong> 12345678</p>
            </div>
        </div>
    </div>

    <script>
        function loginApp() {
            return {
                email: 'admin@pos.com',
                password: '12345678',
                loading: false,
                errorMessage: '',
                async submitLogin() {
                    this.loading = true;
                    this.errorMessage = '';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const response = await fetch('/api/v1/auth/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({
                                email: this.email,
                                password: this.password
                            })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            const user = result.data.user;

                            // GUARD DIBATASI: Cek apakah role_id BUKAN 1 (Bukan Admin)
                            const isUserAdmin = (user.role_id && parseInt(user.role_id) === 1) || 
                                              (user.role_name && user.role_name.toLowerCase() === 'admin');

                            if (!isUserAdmin) {
                                this.errorMessage = 'Akses ditolak! Akun Anda adalah Customer dan tidak diizinkan masuk ke Web Admin.';
                                localStorage.removeItem('auth_token');
                                localStorage.removeItem('user_info');
                                return;
                            }

                            // Jika terkonfirmasi Admin, simpan ke LocalStorage dan redirect
                            localStorage.setItem('auth_token', result.data.access_token);
                            localStorage.setItem('user_info', JSON.stringify(user));

                            window.location.href = '/admin/dashboard';
                        } else {
                            this.errorMessage = result.message || 'Login gagal. Periksa kembali email & password.';
                        }
                    } catch (error) {
                        this.errorMessage = 'Gagal terhubung ke API Server.';
                        console.error('Login error:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>

</html>