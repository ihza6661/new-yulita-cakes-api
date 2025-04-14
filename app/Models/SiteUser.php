<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SiteUser extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function shoppingCart()
    {
        return $this->hasOne(ShoppingCart::class, 'site_user_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'site_user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'site_user_id');
    }
}
