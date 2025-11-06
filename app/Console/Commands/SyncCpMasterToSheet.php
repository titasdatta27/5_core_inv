<?php

namespace App\Console\Commands;

use App\Models\ProductMaster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

            $formatted[] = [
                "parent"     => $row->parent,
                "sku"        => $row->sku,
                "status"     => $values['status'] ?? '',
                "lp"         => $values['lp'] ?? 0,
                "cp"         => $values['cp'] ?? 0,
                "frght"      => $values['frght'] ?? 0,
                "ship"       => $values['ship'] ?? 0,
                "label_qty"  => $values['label_qty'] ?? '',
                "wt_act"     => $values['wt_act'] ?? 0,
                "wt_decl"    => $values['wt_decl'] ?? 0,
                "l"          => $values['l'] ?? 0,
                "w"          => $values['w'] ?? 0,
                "h"          => $values['h'] ?? 0,
                "cbm"        => $values['cbm'] ?? 0,
                "l2_url"     => $values['l2_url'] ?? '',
                "pcs_per_box" => $values['pcs_per_box'] ?? null,
                "dc"         => $values['dc'] ?? null,
                "l1"         => $values['l1'] ?? null,
                "b"          => $values['b'] ?? null,
                "h1"         => $values['h1'] ?? null,
                "weight"     => $values['weight'] ?? null,
                "msrp"       => $values['msrp'] ?? null,
                "map"       => $values['map'] ?? null,
                "upc"        => $values['upc'] ?? null,
            ];
        }

        // Send to Google Apps Script
        $url = "https://script.google.com/macros/s/AKfycbypziPWX3a8_gm7PufLYTT6i_noHnxI9hNjjFBVR-0N2TzKjjIVONFLZzBqQn2uzGll6Q/exec";
        $response = Http::post($url, [
            "task" => "sync_cp_master",
            "data" => $formatted
        ]);

        $this->info("Sync complete: " . $response->body());
    
    }
}
