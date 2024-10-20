<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PartController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\LocationController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['as' => 'api.'], function () {
    Orion::resource('suppliers', SupplierController::class);
    Orion::resource('parts', PartController::class);
    Orion::resource('purchase-orders', PurchaseOrderController::class);
    Orion::resource('locations', LocationController::class);
});
