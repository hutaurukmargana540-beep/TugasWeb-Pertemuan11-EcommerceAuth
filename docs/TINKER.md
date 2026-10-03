# Dokumentasi 5 Query Tinker (REQ 4)

Jalankan setelah `php artisan migrate:fresh --seed`:

```bash
php artisan tinker
```

Ambil **screenshot** hasil tiap query dan simpan di `docs/screenshots/` dengan nama `tinker-1.png` … `tinker-5.png`.

> Jika perintah `use` tidak dikenali di Tinker versi Anda, tulis nama class lengkap seperti di bawah.

---

## Query 1 — Scope `active()` + `inStock()` + eager loading `category`

Memperlihatkan **scope** pada model `Product`.

```php
App\Models\Product::with('category')->active()->inStock()->orderByDesc('price')->limit(5)->get()->map(fn ($p) => [$p->name, $p->category->name, $p->price_formatted]);
```

Hasil yang diharapkan: 5 produk termahal yang aktif dan stoknya ada (laptop, monitor, smartphone, dst.) beserta nama kategorinya.

## Query 2 — Relasi `hasMany` + `withCount`

```php
App\Models\Category::withCount('products')->orderBy('name')->get()->pluck('products_count', 'name');
```

Hasil yang diharapkan: 6 kategori, masing-masing berjumlah **9** produk (total 54).

## Query 3 — Relasi bertingkat (`Order → items → product`, `Order → user`)

```php
App\Models\Order::with(['user:id,name', 'items.product:id,name'])->latest()->first();
```

Hasil yang diharapkan: satu pesanan dengan relasi `user` dan koleksi `items`, tiap item membawa `product`.

## Query 4 — Agregat: rata-rata rating dan total pendapatan

```php
App\Models\Product::withAvg('reviews', 'rating')->whereHas('reviews')->orderByDesc('reviews_avg_rating')->first(['id', 'name']);

App\Models\Payment::where('status', 'paid')->sum('amount');
```

Hasil yang diharapkan: produk dengan rating rata-rata tertinggi, dan total nilai pembayaran berstatus `paid`.

## Query 5 — Bonus: demo eager loading (N+1 vs `with`)

```php
$db = Illuminate\Support\Facades\DB::class;

$db::enableQueryLog();
App\Models\Product::limit(10)->get()->each(fn ($p) => $p->category->name);
count($db::getQueryLog());     // tanpa eager loading  => 11 query (1 + 10)

$db::flushQueryLog();
App\Models\Product::with('category')->limit(10)->get()->each(fn ($p) => $p->category->name);
count($db::getQueryLog());     // dengan eager loading => 2 query
```

Kesimpulan untuk laporan: `with('category')` mengganti 10 query terpisah menjadi 1 query tambahan.
Hal yang sama dipakai di `ShopController@index` dan `ProductController@index`.
