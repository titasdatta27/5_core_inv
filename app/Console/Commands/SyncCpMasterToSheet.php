<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\ProductMaster;

class SyncCpMasterToSheet extends Command
{
    protected $signature = 'app:sync-cp-master-to-sheet';
    protected $description = 'Sync Google Sheet changes back into ProductMaster table';

    public function handle()
    {
        // Your Google Apps Script that returns sheet JSON
        $url = "https://script.google.com/a/macros/5core.com/s/AKfycbzigewbwSGwRFGEKoM75LeYC_vGyKFEpaxQEX5ovwyOq44kCGF5d3V6Sc5i8gVltCNH/exec";

        $response = Http::get($url);
        if ($response->failed()) {
            $this->error("Failed to fetch data from Google Sheet");
            return;
        }

        $data = $response->json();
        if (!is_array($data)) {
            $this->error("Invalid response format from sheet");
            return;
        }

        foreach ($data as $row) {
            $sku = $row['sku'] ?? null;
            if (!$sku) continue;

            $values = [
                "status"          => $row['status'] ?? '',
                "lp"              => $row['lp'] ?? 0,
                "cp"              => $row['cp'] ?? 0,
                "frght"           => $row['frght'] ?? 0,
                "ship"            => $row['ship'] ?? 0,
                "temu_ship"       => $row['temu_ship'] ?? 0,
                "ebay2_ship"      => $row['ebay2_ship'] ?? 0,
                "initial_quantity"=> $row['initial_quantity'] ?? '',
                "label_qty"       => $row['label_qty'] ?? '',
                "wt_act"          => $row['wt_act'] ?? 0,
                "wt_decl"         => $row['wt_decl'] ?? 0,
                "l"               => $row['l'] ?? 0,
                "w"               => $row['w'] ?? 0,
                "h"               => $row['h'] ?? 0,
                "cbm"             => $row['cbm'] ?? 0,
                "l2_url"          => $row['l2_url'] ?? '',
                "dc"              => $row['dc'] ?? '',
                "pcs_per_box"     => $row['pcs_per_box'] ?? '',
                "l1"              => $row['l1'] ?? '',
                "b"               => $row['b'] ?? '',
                "h1"              => $row['h1'] ?? '',
                "weight"          => $row['weight'] ?? '',
                "msrp"            => $row['msrp'] ?? '',
                "map"             => $row['map'] ?? '',
                "upc"             => $row['upc'] ?? '',
            ];

            ProductMaster::updateOrCreate(
                ['sku' => $sku],
                ['parent' => $row['parent'] ?? null, 'values' => json_encode($values)]
            );
        }

        $this->info("✅ Sheet → DB sync completed successfully!");
    }
}
