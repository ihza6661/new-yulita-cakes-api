<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    protected $fillable = ['site_user_id'];

    // Relasi ke SiteUser
    public function user()
    {
        return $this->belongsTo(SiteUser::class, 'site_user_id');
    }

    // Relasi ke Item Keranjang
    public function items()
    {
        return $this->hasMany(ShoppingCartItem::class, 'shopping_cart_id');
    }
}
