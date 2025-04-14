<?php

namespace App\Http\Requests\SiteUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'recipient_name' => 'required|string|max:50',
            'phone_number' => ['required', 'string', 'regex:/^(08|\+628)[0-9]{8,12}$/', 'max:15'],
            'address_line1' => 'required|string|max:100',
            'address_line2' => 'nullable|string|max:50',
            'province' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'postal_code' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'phone_number.regex' => 'Format nomor telepon tidak valid (contoh: 081234567890 atau +6281234567890).',
            'address_line1.required' => 'Alamat baris 1 wajib diisi.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'postal_code.required' => 'Kode pos wajib diisi.',
            'postal_code.regex' => 'Kode pos harus terdiri dari 5 digit angka.',
            'is_default.boolean' => 'Status default harus bernilai true atau false.',
        ];
    }
}
