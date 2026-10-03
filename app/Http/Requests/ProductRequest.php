<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /** Otorisasi dilakukan di controller lewat ProductPolicy (Gate::authorize). */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:3', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price'       => ['required', 'numeric', 'min:0', 'max:999999999'],
            'stock'       => ['required', 'integer', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active'   => ['sometimes', 'boolean'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama produk wajib diisi.',
            'category_id.required' => 'Pilih kategori produk.',
            'category_id.exists'   => 'Kategori yang dipilih tidak valid.',
            'price.required'       => 'Harga wajib diisi.',
            'price.numeric'        => 'Harga harus berupa angka.',
            'stock.required'       => 'Stok wajib diisi.',
            'image.image'          => 'File harus berupa gambar.',
            'image.mimes'          => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'image.max'            => 'Ukuran gambar maksimal 2 MB.',
            'stock.integer'        => 'Stok harus berupa bilangan bulat.',
        ];
    }
}
