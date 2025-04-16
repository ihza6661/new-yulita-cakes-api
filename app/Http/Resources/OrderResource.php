<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing(['payment', 'shipment']);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => (float) $this->total_amount,
            'shipping_cost' => (float) $this->shipping_cost,
            'status' => $this->status->value, // Status Order utama (Enum value)
            'status_label' => $this->status->name, // Status Order utama (Enum name/label)
            'order_date' => $this->created_at, // Atau field tanggal order spesifik jika ada

            // Tambahkan Status Pembayaran (ambil dari relasi payment)
            'payment_status' => optional($this->payment)->status?->value ?? 'N/A', // Menggunakan optional helper & nullsafe operator
            'payment_status_label' => optional($this->payment)->status?->name ?? 'N/A',

            // Tambahkan Status Pengiriman (ambil dari relasi shipment)
            'shipment_status' => optional($this->shipment)->status?->value ?? 'N/A', // Menggunakan optional helper & nullsafe operator
            'shipment_status_label' => optional($this->shipment)->status?->name ?? 'N/A',

            // Relasi lain jika perlu (sudah ada sebelumnya)
            // 'address' => new AddressResource($this->whenLoaded('address')),
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
        ];
    }
}
