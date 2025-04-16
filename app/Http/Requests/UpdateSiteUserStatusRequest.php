<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSiteUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin_users')->check();
    }

    public function rules(): array
    {
        return [
            'is_active' => 'required|boolean',
        ];
    }

     public function messages(): array
    {
         return [
            'is_active.required' => 'Status akun wajib diisi.',
            'is_active.boolean'  => 'Status akun harus berupa nilai boolean (true/false atau 1/0).',
        ];
    }
}
