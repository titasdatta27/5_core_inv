<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssemblyVideo extends Model
{
    protected $fillable = ['sku', 'value'];
    protected $casts = [
        'value' => 'array',
    ];
}

