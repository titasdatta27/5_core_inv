<?php

namespace App\Http\Controllers;

use App\Models\ArrivedContainer;
use App\Models\ProductMaster;
use App\Models\ShopifySku;
use App\Models\Supplier;
use App\Models\TransitContainerDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArrivedContainerController extends Controller
{
    public function index(){

        $allRecords = ArrivedContainer::where(function($q){
            $q->whereNull('status')->orWhereRaw("TRIM(status) = ''");
        })->get();

        $tabs = ArrivedContainer::where(function($q){
            $q->whereNull('status')->orWhereRaw("TRIM(status) = ''");
        })->distinct()->pluck('tab_name')->toArray();

        if (empty($tabs)) {
            $tabs = ['Container 1'];
        }

        $skuParentMap = ProductMaster::pluck('parent', 'sku')
            ->mapWithKeys(function ($parent, $sku) {
                $normSku = strtoupper(trim(preg_replace('/\s+/', ' ', $sku)));
                return [$normSku => strtoupper(trim($parent))];
            })->toArray();

        $supplierData = Supplier::select('name', 'parent')->get();
        $parentSupplierMap = [];
        foreach ($supplierData as $supplier) {
            $parentList = array_map('trim', explode(',', $supplier->parent));
            foreach ($parentList as $parent) {
                $key = strtoupper(trim(preg_replace('/\s+/', ' ', $parent)));
                $parentSupplierMap[$key][] = $supplier->name;
            }
        }

        $shopifyImages = ShopifySku::pluck('image_src', 'sku')->mapWithKeys(function ($value, $key) {
            $normSku = strtoupper(trim(preg_replace('/\s+/', ' ', $key)));
            return [$normSku => $value];
        })->toArray();

        $productValuesMap = ProductMaster::pluck('Values', 'sku')->mapWithKeys(function ($value, $key) {
            $normSku = strtoupper(trim(preg_replace('/\s+/', ' ', $key)));
            return [$normSku => $value];
        })->toArray();

        $allRecords->transform(function ($record) use ($skuParentMap, $parentSupplierMap, $shopifyImages, $productValuesMap) {
            $sku = strtoupper(trim(preg_replace('/\s+/', ' ', $record->our_sku ?? '')));

            $parent = $skuParentMap[$sku] ?? null;

            if (empty($record->parent) && $parent) {
                $record->parent = $parent;
            }

            $parentKey = strtoupper(trim(preg_replace('/\s+/', ' ', $record->parent ?? '')));
            $record->supplier_names = $parentSupplierMap[$parentKey] ?? [];

            $record->image_src = $shopifyImages[$sku] ?? null;
            $record->Values = $productValuesMap[$sku] ?? null;

            return $record;
        });

        $groupedData = $allRecords->groupBy('tab_name');
        foreach ($tabs as $tab) {
            if (!isset($groupedData[$tab])) {
                $groupedData[$tab] = collect([]);
            }
        }

        return view('purchase-master.transit_container.arrived-conatiner', [
            'tabs' => $tabs,
            'groupedData' => $groupedData
        ]);
    }

    public function pushArrivedContainer(Request $request)
    {
        $tabName = $request->input('tab_name');
        $rows = $request->input('data', []);

        foreach ($rows as $row) {
            ArrivedContainer::updateOrCreate(
                [
                    'our_sku' => $row['our_sku'],
                    'tab_name' => $row['tab_name'] ?? $tabName,
                ],
                [
                'supplier_name'     => $row['supplier_name'] ?? null,
                'company_name'      => $row['company_name'] ?? null,
                'parent'            => $row['parent'] ?? null,
                'no_of_units'       => !empty($row['no_of_units']) ? (int) $row['no_of_units'] : null,
                'total_ctn'         => !empty($row['total_ctn']) ? (int) $row['total_ctn'] : null,
                'rate'              => !empty($row['rate']) ? (float) $row['rate'] : null,
                'unit'              => $row['unit'] ?? null,
                'changes'           => $row['changes'] ?? null,
                'package_size'      => $row['package_size'] ?? null,
                'product_size_link' => $row['product_size_link'] ?? null,
                'comparison_link'   => $row['comparison_link'] ?? null,
                'order_link'        => $row['order_link'] ?? null,
                'image_src'         => $row['image_src'] ?? null,
                'photos'            => $row['photos'] ?? null,
                'specification'     => $row['specification'] ?? null,
            ]);

            if (!empty($row['id'])) {
                TransitContainerDetail::where('id', $row['id'])->update([
                    'status' => 'inactive',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Inventory pushed successfully',
            'count'   => count($rows),
        ]);
    }
}
