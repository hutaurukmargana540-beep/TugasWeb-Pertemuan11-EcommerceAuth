{{-- Menimpa navigasi bawaan Breeze: menu menyesuaikan role pengguna.
     Memakai komponen Breeze (x-nav-link, x-dropdown, dst.) dan Alpine.js. --}}
@php
  $user = auth()->user();
  $links = [
    ['route' => 'dashboard',    'match' => 'dashboard',      'label' => 'Dashboard', 'show' => true],
    ['route' => 'shop.index',   'match' => 'shop.*',         'label' => 'Katalog',   'show' => true],
    ['route' => 'orders.index', 'match' => 'orders.*',       'label' => 'Pesanan Saya', 'show' => true],
    ['route' => 'manage.products.index', 'match' => 'manage.*', 'label' => 'Kelola Produk', 'show' => $user->hasRole('admin', 'editor')],
    ['route' => 'admin.users.index',      'match' => 'admin.*',  'label' => 'Pengguna',      'show' => $user->isAdmin()],
  ];
@endphp

<nav x-data="{ open: false }" class="border-b border-gray-100 bg-white">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 justify-between">
      <div class="flex">
        <div class="flex shrink-0 items-center">
          <a href="{{ route('dashboard') }}" class="text-lg font-bold text-gray-800">Toko<span class="text-indigo-600">Test</span></a>
        </div>

        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
          @foreach ($links as $link)
            @if ($link['show'])
              <x-nav-link :href="route($link['route'])" :active="request()->routeIs($link['match'])">{{ $link['label'] }}</x-nav-link>
            @endif
          @endforeach
        </div>
      </div>

      <div class="hidden sm:ms-6 sm:flex sm:items-center">
        <x-dropdown align="right" width="48">
          <x-slot name="trigger">
            <button class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:text-gray-700 focus:outline-none">
              <div>{{ $user->name }} <span class="ms-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">{{ $user->role }}</span></div>
              <div class="ms-1">
                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
              </div>
            </button>
          </x-slot>

          <x-slot name="content">
            <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-dropdown-link>
            </form>
          </x-slot>
        </x-dropdown>
      </div>

      <div class="-me-2 flex items-center sm:hidden">
        <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none" aria-label="Menu">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': open, 'inline-flex': ! open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            <path :class="{'hidden': ! open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    <div class="space-y-1 pb-3 pt-2">
      @foreach ($links as $link)
        @if ($link['show'])
          <x-responsive-nav-link :href="route($link['route'])" :active="request()->routeIs($link['match'])">{{ $link['label'] }}</x-responsive-nav-link>
        @endif
      @endforeach
    </div>

    <div class="border-t border-gray-200 pb-1 pt-4">
      <div class="px-4">
        <div class="text-base font-medium text-gray-800">{{ $user->name }}</div>
        <div class="text-sm font-medium text-gray-500">{{ $user->email }}</div>
      </div>
      <div class="mt-3 space-y-1">
        <x-responsive-nav-link :href="route('profile.edit')">Profil</x-responsive-nav-link>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-responsive-nav-link>
        </form>
      </div>
    </div>
  </div>
</nav>
