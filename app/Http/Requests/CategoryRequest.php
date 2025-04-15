<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::guard('admin_users')->check();
    }

    public function rules(): array
    {
        return [
            'category_name' => 'required|string|max:50|unique:categories,category_name',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.max'      => 'Nama kategori maksimal 50 karakter.',
            'category_name.unique'   => 'Nama kategori sudah terdaftar.',
            'image.required'         => 'Gambar kategori wajib diunggah.',
            'image.image'            => 'File yang diunggah harus berupa gambar.',
            'image.mimes'            => 'Format gambar: jpeg, png, jpg, gif, svg, webp.',
            'image.max'              => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
