@extends('layouts.shop')

@section('title', 'Katalog')

@section('content')
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Katalog Produk</h1>
      <p class="text-sm text-gray-500">{{ $products->total() }} produk tersedia</p>
    </div>

    <form method="GET" action="{{ route('shop.index') }}" class="flex gap-2">
      @if (request('kategori'))
        <input type="hidden" name="kategori" value="{{ request('kategori') }}">
      @endif
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…"
             class="w-64 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      <button class="rounded-md border border-gray-300 bg-white px-4 text-sm hover:bg-gray-50">Cari</button>
    </form>
  </div>

  <div class="mb-6 flex flex-wrap gap-2 text-sm">
    <a href="{{ route('shop.index') }}"
       class="rounded-full border px-3 py-1 {{ request('kategori') ? 'border-gray-300 bg-white' : 'border-indigo-600 bg-indigo-600 text-white' }}">Semua</a>
    @foreach ($categories as $category)
      <a href="{{ route('shop.index', ['kategori' => $category->slug]) }}"
         class="rounded-full border px-3 py-1 {{ request('kategori') === $category->slug ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 bg-white' }}">
        {{ $category->name }} <span class="opacity-60">{{ $category->products_count }}</span>
      </a>
    @endforeach
  </div>

  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @forelse ($products as $product)
      <a href="{{ route('shop.show', $product) }}" class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white hover:border-indigo-400">
        <x-product-image :product="$product" class="aspect-[4/3] w-full" />
        <div class="flex flex-1 flex-col p-4">
        <p class="text-xs text-gray-500">{{ $product->category->name }}</p>
        <h2 class="mt-1 flex-1 font-medium leading-snug">{{ $product->name }}</h2>
        <p class="mt-3 text-lg font-semibold text-indigo-700">{{ $product->price_formatted }}</p>
        <p class="mt-1 text-xs text-gray-500">
          Stok {{ $product->stock }}
          @if ($product->reviews_avg_rating) · ★ {{ number_format($product->reviews_avg_rating, 1) }} @endif
        </p>
        </div>
      </a>
    @empty
      <p class="col-span-full rounded-lg border border-dashed border-gray-300 p-10 text-center text-gray-500">
        Tidak ada produk yang cocok. <a href="{{ route('shop.index') }}" class="text-indigo-600 underline">Lihat semua produk</a>.
      </p>
    @endforelse
  </div>

  <div class="mt-8">{{ $products->links() }}</div>
@endsection
