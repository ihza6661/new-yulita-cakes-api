<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function rules()
    {
        $categoryId = $this->route('category'); // Ambil ID kategori dari route

        return [
            'category_name' => 'required|max:50|unique:categories,category_name,' . $categoryId,
            'image' => ($this->isMethod('post') ? 'required' : 'nullable') . '|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique'   => 'Nama kategori sudah terdaftar.',
            'image.required'         => 'Gambar kategori wajib diisi.',
            'image.image'            => 'File yang diunggah harus berupa gambar.',
            'image.mimes'            => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif, svg, webp.',
            'image.max'              => 'Ukuran gambar terlalu besar. Maksimal 2MB.',
        ];
    }
}
