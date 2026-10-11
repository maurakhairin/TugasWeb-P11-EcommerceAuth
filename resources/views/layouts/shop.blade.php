<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Token CSRF di <meta> ini dipakai saat testing lewat Console browser (lihat README bagian testing) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Toko Online')</title>

    {{-- Vite + Tailwind sudah disiapkan Breeze. Jalankan "npm run dev" supaya CSS-nya termuat. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800">

    {{--
        Layout ini dibuat TERPISAH dari <x-app-layout> bawaan Breeze, karena halaman
        produk harus bisa dibuka tamu (belum login), sedangkan <x-app-layout>
        mengasumsikan user sudah login.
    --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('products.index') }}" class="font-bold text-lg">Toko Online</a>

            <div class="flex items-center gap-4 text-sm">
                @auth
                    <span>
                        {{ auth()->user()->name }}
                        <span class="ml-1 px-2 py-0.5 rounded bg-gray-200 text-xs">{{ auth()->user()->role }}</span>
                    </span>

                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.index') }}" class="text-indigo-600 hover:underline">Admin</a>
                    @endif

                    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:underline">Login</a>
                    <a href="{{ route('register') }}" class="hover:underline">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-3">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded bg-red-100 text-red-800 px-4 py-3">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

</body>
</html>
