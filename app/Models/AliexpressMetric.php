<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AliexpressMetric extends Model
{
    use HasFactory;

    protected $table = 'aliexpress_metric';

    protected $fillable = [
        'product_id',
        'price',
        'l30',
        'l60',
    ];
}