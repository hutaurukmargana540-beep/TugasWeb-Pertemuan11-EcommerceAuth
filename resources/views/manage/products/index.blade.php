<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        {{ auth()->user()->isAdmin() ? 'Semua Produk' : 'Produk Saya' }}
      </h2>
      @can('create', App\Models\Product::class)
        <a href="{{ route('manage.products.create') }}"
           class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Tambah produk</a>
      @endcan
    </div>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <x-flash />

      <form method="GET" class="mb-4 flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama produk…"
               class="w-64 rounded-md border-gray-300 text-sm shadow-sm">
        <button class="rounded-md border border-gray-300 bg-white px-4 text-sm hover:bg-gray-50">Cari</button>
      </form>

      <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50 text-left text-gray-500">
            <tr>
              <th class="px-4 py-3 font-medium">Gambar</th>
              <th class="px-4 py-3 font-medium">Produk</th>
              <th class="px-4 py-3 font-medium">Kategori</th>
              <th class="px-4 py-3 font-medium">Harga</th>
              <th class="px-4 py-3 font-medium">Stok</th>
              <th class="px-4 py-3 font-medium">Pemilik</th>
              <th class="px-4 py-3 font-medium">Status</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse ($products as $product)
              <tr>
                <td class="px-4 py-3"><x-product-image :product="$product" class="h-12 w-12 rounded-md !text-xl" /></td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $product->category->name }}</td>
                <td class="px-4 py-3">{{ $product->price_formatted }}</td>
                <td class="px-4 py-3">{{ $product->stock }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $product->owner?->name ?? '—' }}</td>
                <td class="px-4 py-3">{{ $product->is_active ? 'Tampil' : 'Disembunyikan' }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-right">
                  {{-- Tombol hanya muncul jika ProductPolicy mengizinkan --}}
                  @can('update', $product)
                    <a href="{{ route('manage.products.edit', $product) }}" class="text-indigo-600 hover:underline">Edit</a>
                  @endcan
                  @can('delete', $product)
                    <form method="POST" action="{{ route('manage.products.destroy', $product) }}" class="ms-3 inline"
                          onsubmit="return confirm('Hapus produk ini?')">
                      @csrf
                      @method('DELETE')
                      <button class="text-red-600 hover:underline">Hapus</button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="px-4 py-10 text-center text-gray-500">Belum ada produk.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-6">{{ $products->links() }}</div>
    </div>
  </div>
</x-app-layout>
