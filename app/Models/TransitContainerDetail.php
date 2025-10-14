<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransitContainerDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'tab_name',
        'supplier_name',
        'company_name',
        'parent',
        'our_sku',
        'photos',
        'specification',
        'package_size',
        'product_size_link',
        'status',
        'changes',
        'no_of_units',
        'total_ctn',
        'rate',
        'unit',
        'cbm',
        'order_link',
        'comparison_link',
    ];

}
