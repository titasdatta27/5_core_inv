<?php

namespace App\Console\Commands;

use App\Models\ProductMaster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncCpMasterToSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-cp-master-to-sheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync cp_master table with App_data Sheet daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rows = ProductMaster::select('*', 'Values as values')->get();

        $formatted = [];
        foreach ($rows as $row) {
            $values = json_decode($row->values, true);
            if (empty($values) || !is_array($values)) {
                Log::warning("Invalid JSON in SKU: {$row->sku}");
                continue;
            }

            $formatted[] = [
                "parent"          => $row->parent,
                "sku"             => $row->sku,
                "status"          => $values['status'] ?? '',
                "lp"              => $values['lp'] ?? 0,
                "cp"              => $values['cp'] ?? 0,
                "frght"           => $values['frght'] ?? 0,
                "ship"            => $values['ship'] ?? 0,
                "temu_ship"       => $values['temu_ship'] ?? 0,
                "ebay2_ship"      => $values['ebay2_ship'] ?? 0,
                "initial_quantity" => $values['initial_quantity'] ?? '',
                "label_qty"       => $values['label_qty'] ?? '',
                "wt_act"          => $values['wt_act'] ?? 0,
                "wt_decl"         => $values['wt_decl'] ?? 0,
                "l"               => $values['l'] ?? 0,
                "w"               => $values['w'] ?? 0,
                "h"               => $values['h'] ?? 0,
                "cbm"             => $values['cbm'] ?? 0,
                "l2_url"          => $values['l2_url'] ?? '',
                "dc"              => $values['dc'] ?? '',
                "pcs_per_box"     => $values['pcs_per_box'] ?? '',
                "l1"              => $values['l1'] ?? '',
                "b"               => $values['b'] ?? '',
                "h1"              => $values['h1'] ?? '',
                "weight"          => $values['weight'] ?? '',
                "msrp"            => $values['msrp'] ?? '',
                "map"             => $values['map'] ?? '',
                "upc"             => $values['upc'] ?? '',
            ];
        }
        // Send to Google Apps Script
        $url = "https://script.google.com/macros/s/AKfycbzaPKeAG-YaXIizurhIwOEoSlU-ipwIRcpZjbdNMaGcRN6_FMkkC6LTyAS8O7Ms3GBTJA/exec";
        $response = Http::withBody(
            json_encode(["task" => "sync_cp_master", "data" => $formatted]),
            'application/json'
        )->post($url);


        $this->info("Sync complete: " . $response->body());
    }
}
