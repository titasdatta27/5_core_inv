<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifySku extends Model
{
    use HasFactory;

    protected $table = 'shopify_skus';
    
    protected $fillable = [
        'variant_id',
        'sku',
        'inv',
        'quantity',
        'price',
        'image_src',
        'shopify_l30',
        'available_to_sell',
        'committed',        
        'on_hand',
    ];
}
