<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin_users')->check();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ];
    }

    public function messages(): array
    {
         return [
            'status.required' => 'Status pesanan wajib diisi.',
            'status.enum'    => 'Status pesanan yang dipilih tidak valid.',
        ];
    }
}
