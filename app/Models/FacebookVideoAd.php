<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookVideoAd extends Model
{
    protected $fillable = ['sku', 'value'];

    protected $casts = [
        'value' => 'array',
    ];
}
