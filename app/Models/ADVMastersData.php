<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class ADVMastersData extends Model
{
    use HasFactory;

    protected $table = 'adv_masters_datas';
    protected $primaryKey = 'adv_masters_data_id';  
    public $timestamps = false;


    protected function getAmazonAdRunningSaveAdvMasterDataProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'AMAZON')->first();
                $updateAmazon->spent = $request->spendl30Total;
                $updateAmazon->clicks = $request->clicksL30Total;
                $updateAmazon->ad_sales = $request->salesL30Total;
                $updateAmazon->ad_sold = $request->soldL30Total;
                $updateAmazon->save();

                $updateAmazonkw = ADVMastersData::where('channel', 'AMZ KW')->first();
                $updateAmazonkw->spent = $request->kwSpendL30Total;
                $updateAmazonkw->clicks = $request->kwClicksL30Total;
                $updateAmazonkw->ad_sales = $request->kwSalesL30Total;
                $updateAmazonkw->ad_sold = $request->kwSoldL30Total;
                $updateAmazonkw->save();

                $updateAmazonpt = ADVMastersData::where('channel', 'AMZ PT')->first();
                $updateAmazonpt->spent = $request->ptSpendL30Total;
                $updateAmazonpt->clicks = $request->ptClicksL30Total;
                $updateAmazonpt->ad_sales = $request->ptSalesL30Total;
                $updateAmazonpt->ad_sold = $request->ptSoldL30Total;
                $updateAmazonpt->save();

                $updateAmazonhl = ADVMastersData::where('channel', 'AMZ HL')->first();
                $updateAmazonhl->spent = $request->hlSpendL30Total;
                $updateAmazonhl->clicks = $request->hlClicksL30Total;
                $updateAmazonhl->ad_sales = $request->hlSalesL30Total;
                $updateAmazonhl->ad_sold = $request->hlSoldL30Total;
                $updateAmazonhl->save();
    
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getEbayRunningDataSaveProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateEbay = ADVMastersData::where('channel', 'EBAY')->first();
                $updateEbay->spent = $request->spendL30Total;
                $updateEbay->clicks = $request->clicksL30Total;
                $updateEbay->ad_sales = $request->salesL30Total;
                $updateEbay->ad_sold = $request->soldL30Total;
                $updateEbay->save();

                $updateEbaykw = ADVMastersData::where('channel', 'EB KW')->first();
                $updateEbaykw->spent = $request->kwSpendL30Total;
                $updateEbaykw->clicks = $request->kwClicksL30Total;
                $updateEbaykw->ad_sales = $request->kwSalesL30Total;
                $updateEbaykw->ad_sold = $request->kwSoldL30Total;
                $updateEbaykw->save();

                $updateEbaypt = ADVMastersData::where('channel', 'EB PMT')->first();
                $updateEbaypt->spent = $request->pmtSpendL30Total;
                $updateEbaypt->clicks = $request->pmtClicksL30Total;
                $updateEbaypt->ad_sales = $request->pmtSalesL30Total;
                $updateEbaypt->ad_sold = $request->pmtSoldL30Total;
                $updateEbaypt->save();
    
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getAmzonAdvSaveMissingDataProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'AMAZON')->first();
                $updateAmazon->missing_ads = $request->totalMissingAds;
                $updateAmazon->save();

                $updateAmazonkw = ADVMastersData::where('channel', 'AMZ KW')->first();
                $updateAmazonkw->missing_ads = $request->kwMissing;
                $updateAmazonkw->save();

                $updateAmazonpt = ADVMastersData::where('channel', 'AMZ PT')->first();
                $updateAmazonpt->missing_ads = $request->ptMissing;
                $updateAmazonpt->save();
    
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getEbayMissingSaveDataProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'EBAY')->first();
                $updateAmazon->missing_ads = $request->totalMissingAds;
                $updateAmazon->save();

                $updateAmazonkw = ADVMastersData::where('channel', 'EB KW')->first();
                $updateAmazonkw->missing_ads = $request->kwMissing;
                $updateAmazonkw->save();

                $updateAmazonpt = ADVMastersData::where('channel', 'EB PMT')->first();
                $updateAmazonpt->missing_ads = $request->ptMissing;
                $updateAmazonpt->save();
    
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getAmazonTotalSalesSaveDataProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'AMAZON')->first();
                $updateAmazon->l30_sales = $request->totalSales;
                $updateAmazon->save();
    
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getAdvEbayTotalSaveDataProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'EBAY')->first();
                $updateAmazon->l30_sales = $request->totalSales;
                $updateAmazon->save();
    
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getAdvWalmartRunningSaveDataProceed($request)
    {
         try {
            DB::beginTransaction();

                $updateEbay = ADVMastersData::where('channel', 'WALMART')->first();
                $updateEbay->spent = $request->spendL30Total;
                $updateEbay->clicks = $request->clicksL30Total;
                $updateEbay->ad_sales = $request->salesL30Total;
                $updateEbay->ad_sold = $request->soldL30Total;
                $updateEbay->save();
    
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getAdvEbay3AdRunningDataSaveProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'EBAY 3')->first();
                $updateAmazon->spent = $request->spendL30Total;
                $updateAmazon->clicks = $request->clicksL30Total;
                $updateAmazon->ad_sales = $request->salesL30Total;
                $updateAmazon->ad_sold = $request->soldL30Total;
                $updateAmazon->save();

                $updateAmazonkw = ADVMastersData::where('channel', 'EB KW3')->first();
                $updateAmazonkw->spent = $request->kwSpendL30Total;
                $updateAmazonkw->clicks = $request->kwClicksL30Total;
                $updateAmazonkw->ad_sales = $request->kwSalesL30Total;
                $updateAmazonkw->ad_sold = $request->kwSoldL30Total;
                $updateAmazonkw->save();

                $updateAmazonpt = ADVMastersData::where('channel', 'EB PMT3')->first();
                $updateAmazonpt->spent = $request->pmtSpendL30Total;
                $updateAmazonpt->clicks = $request->pmtClicksL30Total;
                $updateAmazonpt->ad_sales = $request->pmtSalesL30Total;
                $updateAmazonpt->ad_sold = $request->pmtSoldL30Total;
                $updateAmazonpt->save();
     
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getEbay2AdvRunningAdDataSaveProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'EBAY 2')->first();
                $updateAmazon->spent = $request->spendL30Total;
                $updateAmazon->clicks = $request->clicksL30Total;
                $updateAmazon->ad_sales = $request->salesL30Total;
                $updateAmazon->ad_sold = $request->soldL30Total;
                $updateAmazon->save();

                $updateAmazonpmt = ADVMastersData::where('channel', 'EB PMT2')->first();
                $updateAmazonpmt->spent = $request->pmpSpendL30Total;
                $updateAmazonpmt->clicks = $request->pmtClicksL30Total;
                $updateAmazonpmt->ad_sales = $request->pmtSalesL30Total;
                $updateAmazonpmt->ad_sold = $request->pmpSoldL30Total;
                $updateAmazonpmt->save();
    
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getEbay2TotsalSaleDataSaveProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'EBAY 2')->first();
                $updateAmazon->l30_sales = $request->totalSales;
                $updateAmazon->save();

            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getEbay3TotalSaleSaveDataProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'EBAY 3')->first();
                $updateAmazon->l30_sales = $request->salesTotal;
                $updateAmazon->save();

            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getAdvEbay2MissingSaveDataProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateEbay2 = ADVMastersData::where('channel', 'EBAY 2')->first();
                $updateEbay2->missing_ads = $request->ptMissing;
                $updateEbay2->save();

                $updateEbay2 = ADVMastersData::where('channel', 'EB PMT2')->first();
                $updateEbay2->missing_ads = $request->ptMissing;
                $updateEbay2->save();

            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getEbay3MissingDataSaveProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateEbay2 = ADVMastersData::where('channel', 'EBAY 3')->first();
                $updateEbay2->missing_ads = $request->totalMissingAds;
                $updateEbay2->save();

                $updateEbay2 = ADVMastersData::where('channel', 'EB KW3')->first();
                $updateEbay2->missing_ads = $request->kwMissing;
                $updateEbay2->save();

                $updateEbay2 = ADVMastersData::where('channel', 'EB PMT3')->first();
                $updateEbay2->missing_ads = $request->ptMissing;
                $updateEbay2->save();

            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }

    protected function getAdvShopifyGShoppingSaveDataProceed($request)
    {
        try {
            DB::beginTransaction();

                $updateAmazon = ADVMastersData::where('channel', 'G SHOPPING')->first();
                $updateAmazon->spent = $request->spendL30Total;
                $updateAmazon->clicks = $request->clicksl30Total;
                $updateAmazon->ad_sales = $request->adSalesl30Total;
                $updateAmazon->ad_sold = $request->adSoldl30Total;
                $updateAmazon->save();
                 
            DB::commit();
            return 1; 
        } catch (\Exception $e) {
            DB::rollBack(); 
            return 0;
        }
    }


}
