<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Pesanan {{ $order->order_number }}</h2>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
      <x-flash />

      <div class="rounded-lg bg-white p-6 shadow-sm text-sm">
        <p>Status: <strong>{{ $order->status }}</strong></p>
        <p class="mt-1">Pembayaran:
          @if ($order->payment)
            {{ str_replace('_', ' ', $order->payment->method) }} — {{ $order->payment->status }}
          @else
            —
          @endif
        </p>
        @if ($order->address)
          <p class="mt-1 text-gray-600">Dikirim ke {{ $order->address->recipient }}, {{ $order->address->street }}, {{ $order->address->city }}</p>
        @endif
      </div>

      <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50 text-left text-gray-500">
            <tr><th class="px-4 py-3 font-medium">Produk</th><th class="px-4 py-3 font-medium">Harga</th><th class="px-4 py-3 font-medium">Qty</th><th class="px-4 py-3 text-right font-medium">Subtotal</th></tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach ($order->items as $item)
              <tr>
                <td class="px-4 py-3">{{ $item->product->name }}</td>
                <td class="px-4 py-3">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">{{ $item->quantity }}</td>
                <td class="px-4 py-3 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr><td colspan="3" class="px-4 py-3 text-right font-medium">Total</td><td class="px-4 py-3 text-right font-semibold">{{ $order->total_formatted }}</td></tr>
          </tfoot>
        </table>
      </div>

      <a href="{{ route('orders.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Semua pesanan</a>
    </div>
  </div>
</x-app-layout>
