@props(['product', 'class' => 'aspect-square w-full'])

@php
    // Placeholder per kategori: [emoji, warna awal, warna akhir]
    $themes = [
        'Elektronik'        => ['📱', '#6366f1', '#8b5cf6'],
        'Fashion Pria'      => ['👔', '#0ea5e9', '#2563eb'],
        'Fashion Wanita'    => ['👗', '#ec4899', '#f43f5e'],
        'Rumah Tangga'      => ['🏠', '#f59e0b', '#ea580c'],
        'Olahraga'          => ['⚽', '#10b981', '#059669'],
        'Buku & Alat Tulis' => ['📚', '#14b8a6', '#0891b2'],
    ];
    [$icon, $from, $to] = $themes[$product->category->name ?? ''] ?? ['🛍️', '#6b7280', '#374151'];
@endphp

@if ($product->image_url)
  <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
       {{ $attributes->merge(['class' => $class.' bg-white object-contain']) }}>
@else
  <div role="img" aria-label="{{ $product->name }}"
       {{ $attributes->merge(['class' => $class.' flex items-center justify-center text-5xl']) }}
       style="background: linear-gradient(135deg, {{ $from }}, {{ $to }});">
    <span class="drop-shadow">{{ $icon }}</span>
  </div>
@endif
