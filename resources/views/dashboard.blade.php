<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold leading-tight text-gray-800">
            Dashboard
        </h2>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-indigo-600 p-8 text-white shadow-sm">
                <p class="text-sm font-medium text-indigo-100">
                    Dashboard Toko Online
                </p>

                <h1 class="mt-2 text-3xl font-bold">
                    Halo, {{ Auth::user()->name }}!
                </h1>

                <p class="mt-3 text-indigo-100">
                    Selamat datang kembali. Kelola toko online kamu dari sini.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">Nama Pengguna</p>
                    <h3 class="mt-2 text-lg font-semibold text-slate-800">
                        {{ Auth::user()->name }}
                    </h3>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">Role Akun</p>
                    <h3 class="mt-2 text-lg font-semibold capitalize text-indigo-600">
                        {{ Auth::user()->role }}
                    </h3>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Menu Pengelolaan
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Pilih menu untuk melanjutkan aktivitas.
                </p>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <a href="{{ route('products.index') }}"
                       class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-100 text-xl">
                            🛍️
                        </div>

                        <h3 class="mt-4 font-bold text-slate-800">
                            Daftar Produk
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Lihat produk yang tersedia di toko.
                        </p>

                        <p class="mt-4 text-sm font-semibold text-indigo-600">
                            Lihat Produk
                        </p>
                    </a>

                    @if (Auth::user()->isAdmin())
                        <a href="{{ route('admin.index') }}"
                           class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md">
                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-100 text-xl">
                                ⚙️
                            </div>

                            <h3 class="mt-4 font-bold text-slate-800">
                                Panel Admin
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Buka halaman pengelolaan administrator.
                            </p>

                            <p class="mt-4 text-sm font-semibold text-indigo-600">
                                Buka Panel Admin
                            </p>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>