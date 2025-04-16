<?php

namespace App\Http\Requests;

use App\Enums\ShipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin_users')->check();
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::enum(ShipmentStatus::class)],
            'tracking_number' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status pengiriman wajib diisi jika ingin diubah.',
            'status.enum'    => 'Status pengiriman yang dipilih tidak valid.',
            'tracking_number.max' => 'Nomor pelacakan terlalu panjang (maks 100 karakter).',
        ];
    }
}
