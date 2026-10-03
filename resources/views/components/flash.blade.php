{{-- Pesan flash sukses / gagal + ringkasan error validasi. Dipakai di semua halaman <x-app-layout>. --}}
@if (session('success'))
  <div role="alert" class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
    {{ session('success') }}
  </div>
@endif
@if (session('error'))
  <div role="alert" class="mb-6 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
    {{ session('error') }}
  </div>
@endif
