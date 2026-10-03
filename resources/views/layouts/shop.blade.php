<!DOCTYPE html>
{{-- Layout katalog publik (bisa diakses tanpa login). Halaman terautentikasi memakai <x-app-layout> bawaan Breeze. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Katalog') · TokoTest</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-800 antialiased">
  <header class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
      <a href="{{ route('shop.index') }}" class="text-lg font-bold">Toko<span class="text-indigo-600">Test</span></a>

      <nav class="flex items-center gap-4 text-sm">
        @auth
          <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-gray-600 hover:text-gray-900">Keluar</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Masuk</a>
          <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-3 py-1.5 font-medium text-white hover:bg-indigo-500">Daftar</a>
        @endauth
      </nav>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <x-flash />
    @yield('content')
  </main>
</body>
</html>
