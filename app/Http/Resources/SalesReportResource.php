<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_number' => $this->order_number,
            'order_date' => Carbon::parse($this->order_date)->translatedFormat('d M Y H:i'),
            'customer_name' => $this->customer_name,
            'product_name' => $this->product_name,
            'quantity' => (int) $this->quantity,
            'item_price' => (float) $this->item_price,
            'item_subtotal' => (float) $this->item_subtotal,
            'order_status' => $this->order_status,
            'payment_status' => $this->payment_status ?? 'N/A',
            'tracking_number' => $this->tracking_number,
        ];
    }
}
