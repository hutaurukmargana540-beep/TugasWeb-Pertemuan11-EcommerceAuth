<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Checkout</h2>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
      <x-flash />

      <form method="POST" action="{{ route('checkout.store', $product) }}"
            x-data="{
              qty: {{ (int) old('quantity', $quantity) }},
              price: {{ (int) $product->price }},
              addr: @js((string) old('address_id', $addresses->first()?->id ?? '')),
              rupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n) }
            }"
            class="grid gap-6 lg:grid-cols-3">
        @csrf

        <div class="space-y-6 lg:col-span-2">
          {{-- Produk --}}
          <div class="flex gap-4 rounded-lg bg-white p-4 shadow-sm">
            <x-product-image :product="$product" class="h-24 w-24 shrink-0 rounded-md border border-gray-200 !text-3xl" />
            <div class="min-w-0">
              <p class="text-sm text-gray-500">{{ $product->category->name }}</p>
              <p class="font-medium text-gray-900">{{ $product->name }}</p>
              <p class="mt-1 text-indigo-700">{{ $product->price_formatted }}</p>
              <p class="text-xs text-gray-500">Stok tersedia: {{ $product->stock }}</p>
            </div>
          </div>

          {{-- Jumlah --}}
          <div class="rounded-lg bg-white p-6 shadow-sm">
            <x-input-label for="quantity" value="Jumlah" />
            <input id="quantity" name="quantity" type="number" min="1" max="{{ $product->stock }}" x-model.number="qty"
                   class="mt-1 block w-28 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
          </div>

          {{-- Alamat --}}
          <div class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="font-medium text-gray-900">Alamat pengiriman</h3>

            @if ($addresses->isNotEmpty())
              <select name="address_id" x-model="addr"
                      class="mt-3 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($addresses as $address)
                  <option value="{{ $address->id }}">
                    {{ $address->label }} — {{ $address->recipient }}, {{ $address->street }}, {{ $address->city }}
                  </option>
                @endforeach
                <option value="">+ Pakai alamat baru</option>
              </select>
              <x-input-error :messages="$errors->get('address_id')" class="mt-2" />
            @endif

            <div x-show="addr === ''" class="mt-4 grid gap-4 sm:grid-cols-2">
              @php
                $fields = [
                  'recipient'   => ['Nama penerima', 'sm:col-span-1', 'text'],
                  'phone'       => ['Nomor telepon', 'sm:col-span-1', 'text'],
                  'street'      => ['Alamat lengkap', 'sm:col-span-2', 'text'],
                  'city'        => ['Kota', 'sm:col-span-1', 'text'],
                  'province'    => ['Provinsi', 'sm:col-span-1', 'text'],
                  'postal_code' => ['Kode pos', 'sm:col-span-1', 'text'],
                ];
              @endphp
              @foreach ($fields as $name => [$label, $span, $type])
                <div class="{{ $span }}">
                  <x-input-label :for="$name" :value="$label" />
                  <x-text-input :id="$name" :name="$name" :type="$type" class="mt-1 block w-full" :value="old($name)" />
                  <x-input-error :messages="$errors->get($name)" class="mt-2" />
                </div>
              @endforeach
            </div>
          </div>

          {{-- Pembayaran --}}
          <div class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="font-medium text-gray-900">Metode pembayaran</h3>
            <div class="mt-3 space-y-2">
              @foreach ($methods as $value => $label)
                <label class="flex items-center gap-3 text-sm">
                  <input type="radio" name="payment_method" value="{{ $value }}"
                         @checked(old('payment_method', 'transfer_bank') === $value)
                         class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                  {{ $label }}
                </label>
              @endforeach
            </div>
            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />

            <x-input-label for="notes" value="Catatan untuk penjual (opsional)" class="mt-5" />
            <textarea id="notes" name="notes" rows="2"
                      class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
          </div>
        </div>

        {{-- Ringkasan --}}
        <aside class="h-fit rounded-lg bg-white p-6 shadow-sm">
          <h3 class="font-medium text-gray-900">Ringkasan</h3>
          <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500">Harga satuan</dt><dd>{{ $product->price_formatted }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Jumlah</dt><dd x-text="qty || 0"></dd></div>
            <div class="flex justify-between border-t pt-2 font-semibold">
              <dt>Total</dt><dd class="text-indigo-700" x-text="rupiah(price * (qty || 0))"></dd>
            </div>
          </dl>
          <button type="submit"
                  class="mt-6 w-full rounded-md bg-indigo-600 px-4 py-2.5 font-medium text-white hover:bg-indigo-500">
            Buat Pesanan
          </button>
          <a href="{{ route('shop.show', $product) }}" class="mt-3 block text-center text-sm text-gray-500 hover:text-indigo-600">Batal</a>
        </aside>
      </form>
    </div>
  </div>
</x-app-layout>
