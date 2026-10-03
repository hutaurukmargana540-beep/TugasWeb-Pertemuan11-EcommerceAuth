<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * REQ 7: otorisasi edit/delete.
 *  - admin  : boleh semuanya (lewat before())
 *  - editor : hanya boleh edit/hapus produk MILIKNYA sendiri
 *  - user   : tidak boleh mengelola produk
 * Dipakai lewat Gate::authorize() di ProductController dan @can di Blade.
 */
class ProductPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null; // null = lanjut ke method di bawah
    }

    public function viewAny(User $user): bool
    {
        return $user->isEditor();
    }

    public function create(User $user): bool
    {
        return $user->isEditor();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isEditor() && $product->user_id === $user->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
