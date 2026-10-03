@extends('layouts.shop')

@section('title', $product->name)

@section('content')
  <a href="{{ route('shop.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">&larr; Katalog</a>

  <div class="mt-4 grid gap-8 lg:grid-cols-3">
    <div class="lg:col-span-2">
      <x-product-image :product="$product" class="mb-6 aspect-[16/9] w-full rounded-lg border border-gray-200" />
      <p class="text-sm text-gray-500">{{ $product->category->name }}</p>
      <h1 class="mt-1 text-2xl font-semibold">{{ $product->name }}</h1>
      <p class="mt-4 leading-relaxed text-gray-700">{{ $product->description }}</p>

      <h2 class="mb-3 mt-10 text-lg font-semibold">
        Ulasan
        @if ($product->reviews_avg_rating)
          <span class="text-sm font-normal text-gray-500">★ {{ number_format($product->reviews_avg_rating, 1) }} dari {{ $product->reviews->count() }} ulasan</span>
        @endif
      </h2>

      <div class="space-y-3">
        @forelse ($product->reviews as $review)
          <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm">
            <p class="font-medium">{{ $review->user->name }} <span class="text-yellow-600">{{ str_repeat('★', $review->rating) }}</span></p>
            <p class="mt-1 text-gray-600">{{ $review->comment }}</p>
          </div>
        @empty
          <p class="text-sm text-gray-500">Belum ada ulasan.</p>
        @endforelse
      </div>
    </div>

    <aside class="h-fit rounded-lg border border-gray-200 bg-white p-6">
      <p class="text-3xl font-semibold text-indigo-700">{{ $product->price_formatted }}</p>
      <p class="mt-2 text-sm text-gray-500">Stok tersedia: {{ $product->stock }}</p>
      <p class="mt-1 text-sm text-gray-500">Dijual oleh {{ $product->owner?->name ?? 'Toko' }}</p>

      @if ($product->stock > 0)
        <form method="GET" action="{{ route('checkout.create', $product) }}" class="mt-6">
          <label for="qty" class="block text-sm font-medium text-gray-700">Jumlah</label>
          <input id="qty" name="qty" type="number" min="1" max="{{ $product->stock }}" value="1"
                 class="mt-1 block w-24 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <button type="submit"
                  class="mt-4 w-full rounded-md bg-indigo-600 px-4 py-2.5 font-medium text-white hover:bg-indigo-500">
            Beli Sekarang
          </button>
          @guest
            <p class="mt-2 text-xs text-gray-500">Kamu akan diminta masuk terlebih dahulu.</p>
          @endguest
        </form>
      @else
        <p class="mt-6 rounded-md bg-gray-100 px-4 py-2.5 text-center text-sm font-medium text-gray-500">Stok habis</p>
      @endif
    </aside>
  </div>
@endsection
