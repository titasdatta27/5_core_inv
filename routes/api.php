<?php

use App\Http\Controllers\GoogleSheetsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ProductMaster\ProductMasterController;
use App\Http\Controllers\PricingMaster\PricingMasterViewsController;
use App\Http\Controllers\PurchaseMaster\SupplierRFQController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/data', [ApiController::class, 'getData']);

Route::post('/data', [ApiController::class, 'storeData']);

Route::post('/update-amazon-column', [ApiController::class, 'updateAmazonColumn']);
Route::post('/update-amazon-fba-column', [ApiController::class, 'updateAmazonFBAColumn']);
Route::post('/update-ebay-column', [ApiController::class, 'updateEbayColumn']);
Route::post('/update-ebay2-column', [ApiController::class, 'updateEbay2Column']);
Route::post('/update-shopifyB2C-column', [ApiController::class, 'updateShopifyB2CColumn']);
Route::post('/update-macy-column', [ApiController::class, 'updateMacyColumn']);



Route::post('/junglescout', [\App\Http\Controllers\JungleScoutController::class, 'fetchProducts']);

Route::post('/sync-sheets', [GoogleSheetsController::class, 'syncAllSheets']);

Route::get('/sync-inv-l30-to-sheet', [ApiController::class, 'syncInvAndL30ToSheet']);

// Public API - No authentication required
Route::get('/product', [ProductMasterController::class, 'getProductBySku']);
Route::get('/test-doba-connection', [PricingMasterViewsController::class, 'testDobaConnection']); // Debug route
Route::get('/debug-doba-signature', [PricingMasterViewsController::class, 'debugDobaSignature']); // Signature debug
Route::get('/test-doba-item-validation', [PricingMasterViewsController::class, 'testDobaItemValidation']); // Test item validation
Route::get('/advanced-doba-debug', [PricingMasterViewsController::class, 'advancedDobaDebug']); // Advanced debug with multiple methods
Route::post('/update-doba-price', [PricingMasterViewsController::class, 'pushdobaPriceBySku']); // Doba price update API

// Supplier open rfq form url
//please dont delete this section 🙏
Route::prefix('rfq-form')->group(function() {
    Route::post('/{slug}/submit', [SupplierRFQController::class, 'submitRfqForm'])->name('rfq-form.submit');
    Route::get('/{slug}', [SupplierRFQController::class, 'showRfqForm'])->name('rfq-form.show');
});