<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::guard('admin_users')->check();
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'category_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories', 'category_name')->ignore($category->id),
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
         return [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.max'      => 'Nama kategori maksimal 50 karakter.',
            'category_name.unique'   => 'Nama kategori sudah terdaftar.',
            'image.image'            => 'File yang diunggah harus berupa gambar.',
            'image.mimes'            => 'Format gambar: jpeg, png, jpg, gif, svg, webp.',
            'image.max'              => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}