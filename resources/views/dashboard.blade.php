<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
      <x-flash />

      <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
        <p class="text-gray-700">Halo, <strong>{{ auth()->user()->name }}</strong>. Anda masuk sebagai
          <span class="rounded bg-indigo-50 px-2 py-0.5 text-sm font-medium text-indigo-700">{{ auth()->user()->role }}</span>.</p>

        <p class="mt-1 text-sm text-gray-500">
          @if (auth()->user()->isAdmin())
            Admin dapat mengelola semua produk dan mengatur role pengguna.
          @elseif (auth()->user()->isEditor())
            Editor dapat menambah produk dan hanya mengubah atau menghapus produk miliknya sendiri.
          @else
            Anda dapat menelusuri katalog dan melihat riwayat pesanan.
          @endif
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $label => $value)
          <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">{{ $label }}</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $value }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</x-app-layout>
