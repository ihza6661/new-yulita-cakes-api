<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier',
        'service',
        'tracking_number',
        'status',
    ];

    protected $casts = [
        'status' => ShipmentStatus::class,
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
