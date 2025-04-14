<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['category_name', 'image'];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
