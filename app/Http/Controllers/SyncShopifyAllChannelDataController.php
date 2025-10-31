<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SyncShopifyAllChannelDataController extends Controller
{
	public function index()
	{
		return view('shopify.shopify_all_channels');
	}

	public function data(Request $request)
	{
		$rows = $this->fetchRows();
		$result = $this->mapRows($rows);

		return response()->json($result);
	}

	public function export(Request $request)
	{
		$rows = $this->fetchRows();
		$result = $this->mapRows($rows);

		$fileName = 'shopify_all_channels_' . now()->format('Y_m_d_His') . '.xlsx';
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		if (empty($result)) {
			$sheet->setCellValue('A1', 'No data available');
		} else {
			$headers = array_keys($result[0]);
			$sheet->fromArray($headers, null, 'A1');

			$rowIndex = 2;
			foreach ($result as $row) {
				$sheet->fromArray(
					array_map([$this, 'formatValueForCell'], array_values($row)),
					null,
					'A' . $rowIndex
				);
				$rowIndex++;
			}
		}

		$tempFile = tempnam(sys_get_temp_dir(), 'shopify_all_channels_');
		$writer = new Xlsx($spreadsheet);
		$writer->save($tempFile);

		return response()->download($tempFile, $fileName, [
			'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		])->deleteFileAfterSend(true);
	}

	protected function formatValueForCell($value)
	{
		if (is_bool($value)) {
			return $value ? 'TRUE' : 'FALSE';
		}

		if (is_scalar($value) || $value === null) {
			return $value ?? '';
		}

		return json_encode($value);
	}

	protected function fetchRows(): Collection
	{
		return DB::table('shopify_all_channels_data')
			->select(['sku', 'parent', 'value'])
			->orderBy('parent')
			->orderBy('sku')
			->get();
	}

	protected function mapRows(Collection $rows): array
	{
		$result = [];

		foreach ($rows as $row) {
			$value = is_string($row->value) ? json_decode($row->value, true) : ($row->value ?? []);

			$orders = $value['shopifyorders'] ?? [];
			$orders = is_array($orders) ? $orders : [];
			$getOrders = function (string $key) use ($orders) {
				$m = $orders[$key] ?? [];
				return [
					'o_l30' => (int)($m['l30'] ?? 0),
					'o_l60' => (int)($m['l60'] ?? 0),
					'q_l30' => (int)($m['qty_l30'] ?? 0),
					'q_l60' => (int)($m['qty_l60'] ?? 0),
				];
			};

			$amzO = $getOrders('Amazon');
			$ebayO = $getOrders('eBay');
			$ebay2O = $getOrders('Ebay2');
			$ebay3O = $getOrders('Ebay3');
			$temuO = $getOrders('Temu');
			$walmartO = $getOrders('Walmart');
			$reverbO = $getOrders('Reverb');
			$wayfairO = $getOrders('Wayfair');
			$sheinO = $getOrders('Shein');
			$aliO = $getOrders('AliExpress');
			$mercariO = $getOrders('Mercari');
			$dobaO = $getOrders('doba');
			$bestbuyO = $getOrders('Best Buy USA');
			$macysO = $getOrders("Macy's");

			$result[] = [
				'Parent' => $row->parent,
				'SKU' => $row->sku,
				'Shopify_INV' => $value['shopify']['inv'] ?? 0,
				'Shopify_Qty' => $value['shopify']['qty'] ?? 0,
				'Img' => $value['shopify']['img'] ?? '',
				'Ebay1_L30' => $value['ebay_one']['l30'] ?? 0,
				'Ebay1_L60' => $value['ebay_one']['l60'] ?? 0,
				'Ebay_O_L30' => $ebayO['o_l30'],
				'Ebay_O_L60' => $ebayO['o_l60'],
				'Ebay_Q_L30' => $ebayO['q_l30'],
				'Ebay_Q_L60' => $ebayO['q_l60'],
				'Ebay2_L30' => $value['ebay_two']['l30'] ?? 0,
				'Ebay2_L60' => $value['ebay_two']['l60'] ?? 0,
				'Ebay2_O_L30' => $ebay2O['o_l30'],
				'Ebay2_O_L60' => $ebay2O['o_l60'],
				'Ebay2_Q_L30' => $ebay2O['q_l30'],
				'Ebay2_Q_L60' => $ebay2O['q_l60'],
				'Ebay3_L30' => $value['ebay_three']['l30'] ?? 0,
				'Ebay3_L60' => $value['ebay_three']['l60'] ?? 0,
				'Ebay3_O_L30' => $ebay3O['o_l30'],
				'Ebay3_O_L60' => $ebay3O['o_l60'],
				'Ebay3_Q_L30' => $ebay3O['q_l30'],
				'Ebay3_Q_L60' => $ebay3O['q_l60'],
				'Amazon_L30' => $value['amazon']['l30'] ?? 0,
				'Amazon_L60' => $value['amazon']['l60'] ?? 0,
				'Amazon_O_L30' => $amzO['o_l30'],
				'Amazon_O_L60' => $amzO['o_l60'],
				'Amazon_Q_L30' => $amzO['q_l30'],
				'Amazon_Q_L60' => $amzO['q_l60'],
				'Reverb_L30' => $value['reverb']['l30'] ?? 0,
				'Reverb_L60' => $value['reverb']['l60'] ?? 0,
				'Reverb_O_L30' => $reverbO['o_l30'],
				'Reverb_O_L60' => $reverbO['o_l60'],
				'Reverb_Q_L30' => $reverbO['q_l30'],
				'Reverb_Q_L60' => $reverbO['q_l60'],
				'Macy_L30' => $value['macy']['l30'] ?? 0,
				'Macy_L60' => $value['macy']['l60'] ?? 0,
				'Macys_O_L30' => $macysO['o_l30'],
				'Macys_O_L60' => $macysO['o_l60'],
				'Macys_Q_L30' => $macysO['q_l30'],
				'Macys_Q_L60' => $macysO['q_l60'],
				'BestBuy_L30' => $value['bestbuy']['l30'] ?? 0,
				'BestBuy_L60' => $value['bestbuy']['l60'] ?? 0,
				'BestBuyUSA_O_L30' => $bestbuyO['o_l30'],
				'BestBuyUSA_O_L60' => $bestbuyO['o_l60'],
				'BestBuyUSA_Q_L30' => $bestbuyO['q_l30'],
				'BestBuyUSA_Q_L60' => $bestbuyO['q_l60'],
				'Wayfair_L30' => $value['wayfair']['l30'] ?? 0,
				'Wayfair_L60' => $value['wayfair']['l60'] ?? 0,
				'Wayfair_O_L30' => $wayfairO['o_l30'],
				'Wayfair_O_L60' => $wayfairO['o_l60'],
				'Wayfair_Q_L30' => $wayfairO['q_l30'],
				'Wayfair_Q_L60' => $wayfairO['q_l60'],
				'Walmart_L30' => $value['walmart']['l30'] ?? 0,
				'Walmart_L60' => $value['walmart']['l60'] ?? 0,
				'Walmart_O_L30' => $walmartO['o_l30'],
				'Walmart_O_L60' => $walmartO['o_l60'],
				'Walmart_Q_L30' => $walmartO['q_l30'],
				'Walmart_Q_L60' => $walmartO['q_l60'],
				'PLS_L30' => $value['pls']['l30'] ?? 0,
				'PLS_L60' => $value['pls']['l60'] ?? 0,
				'FBMkt_L30' => $value['fb_marketplace']['l30'] ?? 0,
				'FBMkt_L60' => $value['fb_marketplace']['l60'] ?? 0,
				'TikTok_L30' => $value['tiktok']['l30'] ?? 0,
				'TikTok_L60' => $value['tiktok']['l60'] ?? 0,
				'Temu_L30' => $value['temu']['l30'] ?? 0,
				'Temu_L60' => $value['temu']['l60'] ?? 0,
				'Temu_O_L30' => $temuO['o_l30'],
				'Temu_O_L60' => $temuO['o_l60'],
				'Temu_Q_L30' => $temuO['q_l30'],
				'Temu_Q_L60' => $temuO['q_l60'],
				'Business5core_L30' => $value['business5core']['l30'] ?? 0,
				'Business5core_L60' => $value['business5core']['l60'] ?? 0,
				'AliExpress_L30' => $value['aliexpress']['l30'] ?? 0,
				'AliExpress_L60' => $value['aliexpress']['l60'] ?? 0,
				'AliExpress_O_L30' => $aliO['o_l30'],
				'AliExpress_O_L60' => $aliO['o_l60'],
				'AliExpress_Q_L30' => $aliO['q_l30'],
				'AliExpress_Q_L60' => $aliO['q_l60'],
				'Shein_L30' => $value['shein']['l30'] ?? 0,
				'Shein_L60' => $value['shein']['l60'] ?? 0,
				'Shein_O_L30' => $sheinO['o_l30'],
				'Shein_O_L60' => $sheinO['o_l60'],
				'Shein_Q_L30' => $sheinO['q_l30'],
				'Shein_Q_L60' => $sheinO['q_l60'],
				'MercariW_L30' => $value['mercariWship']['l30'] ?? 0,
				'MercariW_L60' => $value['mercariWship']['l60'] ?? 0,
				'MercariW_O_L30' => $mercariO['o_l30'],
				'MercariW_O_L60' => $mercariO['o_l60'],
				'MercariW_Q_L30' => $mercariO['q_l30'],
				'MercariW_Q_L60' => $mercariO['q_l60'],
				'MercariWO_L30' => $value['mercariWithoutShip']['l30'] ?? 0,
				'MercariWO_L60' => $value['mercariWithoutShip']['l60'] ?? 0,
				'Doba_O_L30' => $dobaO['o_l30'],
				'Doba_O_L60' => $dobaO['o_l60'],
				'Doba_Q_L30' => $dobaO['q_l30'],
				'Doba_Q_L60' => $dobaO['q_l60'],
			];
		}

		return $result;
	}
}
 
