<?php

namespace App\Console\Commands;

use App\Models\ProductMaster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncCpMasterToSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-cp-master-to-sheet';
    protected $description = 'Sync cp_master table with App_data Sheet daily';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sheetUrl = "https://script.google.com/macros/s/AKfycbzRIN8W1lR34BojgJIXJM1pGFAhdm3U1ySpWVz6nfvEGX80pMXSThpABopxJsJYRDSDmw/exec";

        $rows = ProductMaster::select('*', 'Values as values')->get();
        $total = $rows->count();
        $batchSize = 400;
        $batches = ceil($total / $batchSize);

        Log::info("Starting sync to Google Sheet: {$total} rows in {$batches} batches");

        $chunked = $rows->chunk($batchSize);
        $inserted = 0;

        foreach ($chunked as $index => $chunk) {
            $formatted = [];

            foreach ($chunk as $row) {
                $values = json_decode($row->values, true);

                $formatted[] = [
                    "parent" => $row->parent,
                    "sku" => $row->sku,
                    "status" => $values['status'] ?? '',
                    "lp" => $values['lp'] ?? 0,
                    "cp" => $values['cp'] ?? 0,
                    "frght" => $values['frght'] ?? 0,
                    "ship" => $values['ship'] ?? 0,
                    "temu_ship" => $values['temu_ship'] ?? 0,
                    "ebay2_ship" => $values['ebay2_ship'] ?? 0,
                    "initial_quantity" => $values['initial_quantity'] ?? '',
                    "label_qty" => $values['label_qty'] ?? '',
                    "wt_act" => $values['wt_act'] ?? 0,
                    "wt_decl" => $values['wt_decl'] ?? 0,
                    "l" => $values['l'] ?? 0,
                    "w" => $values['w'] ?? 0,
                    "h" => $values['h'] ?? 0,
                    "cbm" => $values['cbm'] ?? 0,
                    "l2_url" => $values['l2_url'] ?? '',
                    "dc" => $values['dc'] ?? '',
                    "pcs_per_box" => $values['pcs_per_box'] ?? '',
                    "l1" => $values['l1'] ?? '',
                    "b" => $values['b'] ?? '',
                    "h1" => $values['h1'] ?? '',
                    "weight" => $values['weight'] ?? '',
                    "msrp" => $values['msrp'] ?? '',
                    "map" => $values['map'] ?? '',
                    "upc" => $values['upc'] ?? '',
                ];
            }

            try {
                $response = Http::timeout(60)->post($sheetUrl, ['data' => $formatted]);
                $body = $response->json();

                if ($response->successful() && ($body['success'] ?? false)) {
                    $inserted += count($formatted);
                    Log::info("Batch " . ($index + 1) . "/{$batches} synced: " . count($formatted) . " rows inserted.");
                } else {
                    Log::error("Batch " . ($index + 1) . " failed: " . $response->body());
                }

                sleep(2);
            } catch (\Exception $e) {
                Log::error("Batch " . ($index + 1) . " exception: " . $e->getMessage());
            }
        }

        Log::info("Sync completed: {$inserted} / {$total} rows inserted.");
    }
}
