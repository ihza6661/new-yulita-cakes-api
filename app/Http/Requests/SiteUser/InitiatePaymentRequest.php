<?php

namespace App\Http\Requests\SiteUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'cartItems' => 'required|array|min:1',
            'cartItems.*.product_id' => 'required|integer|exists:products,id',
            'cartItems.*.qty' => 'required|integer|min:1',
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where(function ($query) {
                    $query->where('site_user_id', $this->user()->id);
                }),
            ],
            'shipping_option' => 'required|array',
            'shipping_option.code' => 'required|string',
            'shipping_option.service' => 'required|string',
            'shipping_option.cost' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'cartItems.required' => 'Keranjang tidak boleh kosong.',
            'cartItems.min' => 'Keranjang harus berisi minimal 1 item.',
            'cartItems.*.product_id.required' => 'ID Produk dalam keranjang wajib ada.',
            'cartItems.*.product_id.exists' => 'Produk dalam keranjang tidak valid.',
            'cartItems.*.qty.required' => 'Jumlah produk dalam keranjang wajib ada.',
            'cartItems.*.qty.min' => 'Jumlah produk minimal 1.',
            'address_id.required' => 'Alamat pengiriman wajib dipilih.',
            'address_id.exists' => 'Alamat pengiriman tidak valid.',
            'shipping_option.required' => 'Opsi pengiriman wajib dipilih.',
            'shipping_option.array' => 'Format opsi pengiriman tidak valid.',
            'shipping_option.code.required' => 'Kode kurir wajib ada.',
            'shipping_option.service.required' => 'Layanan pengiriman wajib ada.',
            'shipping_option.cost.required' => 'Biaya pengiriman wajib ada.',
        ];
    }
}
