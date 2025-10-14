<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryWarehouse extends Model
{
    use HasFactory;

    protected $table = 'inventory_warehouse';

     protected $fillable = [
        'tab_name',
        'supplier_name',
        'company_name',
        'our_sku',
        'parent',
        'no_of_units',
        'total_ctn',
        'rate',
        'unit',
        'status',
        'changes',
        'values',
        'package_size',
        'product_size_link',
        'comparison_link',
        'order_link',
        'image_src',
        'photos',
        'specification',
        'supplier_names',
    ];

    protected $casts = [
        'supplier_names' => 'array',
    ];

}
