<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Produk</h2>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
      <form method="POST" action="{{ route('manage.products.update', $product) }}" enctype="multipart/form-data" class="rounded-lg bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('manage.products._form')
      </form>
    </div>
  </div>
</x-app-layout>
