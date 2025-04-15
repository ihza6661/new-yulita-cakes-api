<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin_users')->check();
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:50',
            'category_id' => 'required|exists:categories,id',
            'original_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'label' => 'nullable|string|max:50',
            'images'   => 'nullable|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'product_name.required' => 'Nama produk wajib diisi.',
            'product_name.max'      => 'Nama produk maksimal 50 karakter.',
            'category_id.required'  => 'Kategori wajib dipilih.',
            'category_id.exists'    => 'Kategori tidak valid.',
            'original_price.required' => 'Harga asli wajib diisi.',
            'original_price.numeric'=> 'Harga asli harus berupa angka.',
            'original_price.min'    => 'Harga asli minimal 0.',
            'sale_price.numeric'    => 'Harga diskon harus berupa angka.',
            'sale_price.min'        => 'Harga diskon minimal 0.',
            'stock.required'        => 'Stok produk wajib diisi.',
            'stock.integer'         => 'Stok produk harus berupa angka bulat.',
            'stock.min'             => 'Stok produk minimal 0.',
            'weight.required'       => 'Berat produk wajib diisi.',
            'weight.numeric'        => 'Berat produk harus berupa angka.',
            'weight.min'            => 'Berat produk minimal 0.',
            'images.min'            => 'Unggah setidaknya satu gambar produk.',
            'images.*.required'     => 'Setiap file gambar wajib diisi.',
            'images.*.image'        => 'File harus berupa gambar.',
            'images.*.mimes'        => 'Format gambar: jpeg, png, jpg, gif, webp.',
            'images.*.max'          => 'Ukuran gambar maksimal 2MB per file.',
        ];
    }
}