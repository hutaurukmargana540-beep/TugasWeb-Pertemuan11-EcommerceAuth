<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Pesanan Saya</h2>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
      <x-flash />

      <div class="space-y-3">
        @forelse ($orders as $order)
          <a href="{{ route('orders.show', $order) }}" class="block rounded-lg bg-white p-4 shadow-sm hover:ring-1 hover:ring-indigo-300">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <p class="font-medium">{{ $order->order_number }}</p>
              <span class="rounded bg-gray-100 px-2 py-0.5 text-xs">{{ $order->status }}</span>
            </div>
            <p class="mt-1 text-sm text-gray-500">
              {{ $order->items->count() }} item · {{ $order->total_formatted }} · {{ $order->created_at->translatedFormat('d M Y') }}
            </p>
          </a>
        @empty
          <p class="rounded-lg border border-dashed border-gray-300 p-10 text-center text-gray-500">
            Belum ada pesanan. <a href="{{ route('shop.index') }}" class="text-indigo-600 underline">Lihat katalog</a>.
          </p>
        @endforelse
      </div>

      <div class="mt-6">{{ $orders->links() }}</div>
    </div>
  </div>
</x-app-layout>
