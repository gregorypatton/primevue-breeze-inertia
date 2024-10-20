<?php

use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\PurchaseOrderPartController;
use App\Http\Controllers\Api\PartController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SupplierPartsController;

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

Route::group(['as' => 'api.'], function () {
    Orion::resource('suppliers', SupplierController::class);
    Orion::resource('purchase-orders', PurchaseOrderController::class)->withSoftDeletes();
    Orion::resource('purchase-order-parts', PurchaseOrderPartController::class);
    Orion::resource('parts', PartController::class);
    Orion::resource('locations', LocationController::class);

    // Relationships
    Orion::hasManyResource('suppliers', 'parts', SupplierPartsController::class);
    Orion::hasManyResource('purchase-orders', 'parts', PurchaseOrderPartController::class);
});
