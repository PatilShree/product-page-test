<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function productImages()
    {
        return $this->hasMany(ProductImages::class);
    }

    public function productDiscounts()
    {
        return $this->hasOne(ProductDiscounts::class);
    }

    protected $fillable = [
        'name',
        'description',
        'slug',
        'price',
        'active'
    ];
}
