<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    /** @use HasFactory<\Database\Factories\ProductsFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'stock',
        'images',
        'is_active'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    //This will help us to cast the json images to array automatically
    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
    ];
}
