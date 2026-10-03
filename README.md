# Tugas Rutin 11 — E-Commerce DB + Secure Auth

Mata kuliah **Pemrograman Web** · Hardiman G. Hutauruk · Ilmu Komputer, Universitas Negeri Medan

Database e-commerce (7 tabel + relasi Eloquent) dengan autentikasi Laravel Breeze, tiga role
(admin / editor / user), middleware kustom, dan policy. Repo: `TugasWeb-P11-EcommerceAuth`.

## Pemenuhan requirement

### Bagian A — Database & Eloquent

| # | Requirement | Lokasi |
|---|-------------|--------|
| 1 | Migration 7 tabel + FK constraints | `database/migrations/2026_02_10_*` — `categories`, `products`, `addresses`, `orders`, `order_items`, `payments`, `reviews` (+ kolom `role` di `users`) |
| 2 | Seeder + factory, 50+ produk realistis | `database/seeders/ProductSeeder.php` (**54 produk**, 6 kategori, harga Rupiah); factory: `User`, `Category`, `Product`, `Address` |
| 3 | Model + relationship + minimal 1 scope | `app/Models/*` — scope `Product::active()`, `inStock()`, `search()`, `Order::status()` |
| 4 | Dokumentasi 5 query Tinker (screenshot) | `docs/TINKER.md` + screenshot di `docs/screenshots/` |

Relasi utama: `Category 1─N Product`, `User 1─N Product` (pemilik), `User 1─N Address`, `User 1─N Order`,
`Order 1─N OrderItem N─1 Product`, `Order 1─1 Payment`, `Product 1─N Review N─1 User`.

### Bagian B — Auth & Security

| # | Requirement | Lokasi |
|---|-------------|--------|
| 5 | Breeze (login / register / logout) | dipasang lewat `composer require laravel/breeze` (langkah di bawah) |
| 6 | Multi-role + custom middleware | kolom `users.role`; `app/Http/Middleware/EnsureUserHasRole.php`; alias `role` di `bootstrap/app.php` |
| 7 | Policy untuk otorisasi edit / delete | `app/Policies/ProductPolicy.php` (+ `OrderPolicy.php`), dipanggil lewat `Gate::authorize()` dan `@can` |
| 8 | Route protection + testing 2 role | `routes/web.php`, `tests/Feature/RoleAccessTest.php`, checklist di bawah |

> **Catatan:** soal menyebut `PostPolicy`. Karena domain tugas ini e-commerce, policy untuk
> edit / delete diterapkan pada entitas utama yaitu **`ProductPolicy`** — konsep dan cara kerjanya sama.

Bonus: **eager loading demo** (`docs/TINKER.md` query 5; dipakai di `ShopController` dan `ProductController`).

## Cara instalasi

Folder ini berisi **file kustom saja**. **Urutan penting:** pasang Breeze dulu, baru salin file ini
(karena Breeze membuat `routes/web.php` dan view-nya sendiri, dan file di sini harus menimpanya).

```bash
# 1. Project baru
composer create-project laravel/laravel TugasWeb-P11-EcommerceAuth
cd TugasWeb-P11-EcommerceAuth

# 2. Pasang Breeze (stack Blade)
composer require laravel/breeze --dev
php artisan breeze:install blade
```

3. **Salin isi folder ini** (`app`, `bootstrap`, `database`, `docs`, `resources`, `routes`, `tests`,
   `.env.example`) ke dalam project, pilih *Replace / timpa* untuk semua file yang bentrok.
4. Buat database kosong `tugas11_ecommerce` di phpMyAdmin.

```bash
copy .env.example .env          # Linux/Mac: cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build                   # atau: npm run dev (biarkan terminal tetap terbuka)
php artisan serve
```

Buka <http://127.0.0.1:8000>.

### Akun demo (password semuanya: `password`)

| Role | Email |
|------|-------|
| admin | `admin@toko.test` |
| editor | `editor@toko.test`, `editor2@toko.test` |
| user | `user@toko.test` |

Akun baru yang mendaftar lewat `/register` otomatis berstatus **user**.

## Hak akses per role

| Halaman | Guest | user | editor | admin |
|---------|:-----:|:----:|:------:|:-----:|
| Katalog `/` dan detail produk | ✔ | ✔ | ✔ | ✔ |
| `/dashboard`, `/profile`, `/pesanan` | ✘ → login | ✔ | ✔ | ✔ |
| `/kelola/products` (kelola produk) | ✘ → login | 403 | ✔ produk **sendiri** | ✔ semua |
| `/admin/users` (ubah role) | ✘ → login | 403 | 403 | ✔ |

## Testing 2 role (incognito) — untuk screenshot

1. Jendela biasa: login `admin@toko.test` → buka `/admin/users` (**berhasil**) dan `/kelola/products` (semua produk tampil).
2. Jendela **incognito**: login `editor@toko.test` → `/kelola/products` hanya menampilkan produk miliknya.
3. Masih sebagai editor, buka `/admin/users` → **403**.
4. Editor mencoba membuka URL edit produk milik `editor2` (`/kelola/products/{id}/edit`) → **403** (ProductPolicy).
5. Incognito baru: login `user@toko.test` → `/kelola/products` → **403**. Tanpa login → diarahkan ke `/login`.

Sebagai pembanding otomatis:

```bash
php artisan test --filter=RoleAccessTest
```

(memakai SQLite in-memory; butuh ekstensi PHP `pdo_sqlite`)

## Struktur folder (bagian yang dibuat / diubah)

```
app/
├── Http/
│   ├── Controllers/{Shop,Dashboard,Order,Product,User}Controller.php
│   ├── Middleware/EnsureUserHasRole.php      ← middleware kustom "role"
│   └── Requests/ProductRequest.php           ← validasi produk
├── Models/{User,Category,Product,Address,Order,OrderItem,Payment,Review}.php
└── Policies/{ProductPolicy,OrderPolicy}.php
bootstrap/app.php                             ← daftar alias middleware
database/{migrations,factories,seeders}/      ← 7 tabel + seeder 54 produk
resources/views/
├── layouts/{navigation,shop}.blade.php       ← navigasi sesuai role, layout katalog publik
├── components/flash.blade.php                ← pesan sukses / gagal
├── {dashboard}.blade.php, shop/, manage/products/, admin/users/, orders/
routes/web.php                                ← route publik / auth / role
tests/Feature/RoleAccessTest.php
docs/TINKER.md
```

## Keamanan yang diterapkan

- **Mass assignment:** `role` tidak ada di `$fillable` model `User`; perubahan role hanya lewat `forceFill` di `UserController` (khusus admin).
- **Pemilik produk** diambil dari sesi login (`$request->user()->id`), bukan dari input form.
- **Otorisasi berlapis:** middleware `auth` → `role:...` (level route) → `ProductPolicy` / `OrderPolicy` (level data).
- **Validasi** lewat Form Request; `Rule::in(User::ROLES)` untuk role; admin tidak bisa mengubah role akunnya sendiri.
- `@csrf` + `@method` di semua form; output Blade di-escape otomatis.
- **Integritas data:** foreign key dengan aksi hapus yang jelas; `price` pada `order_items` adalah snapshot harga; produk memakai soft delete agar riwayat pesanan tidak rusak.
