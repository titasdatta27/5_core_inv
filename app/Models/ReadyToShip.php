<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadyToShip extends Model
{
    use HasFactory;

    protected $table = 'ready_to_ship';

    protected $fillable = [
        'parent',
        'sku',
        'supplier',
        'cbm',
        'area',
        'shipd_cbm_in_cont',
        'payment',
        'payment_confirmation',
        'model_number',
        'photo_mail_send',
        'followup_delivery',
        'packing_list',
        'container_rfq',
        'quote_result',
        'pay_term',
        'transit_inv_status',
    ];
    
}
