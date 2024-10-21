<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    protected $model = Supplier::class;

    use DisableAuthorization;

    public function includes(): array
    {
        return ['locations', 'parts'];
    }

    public function filterableBy(): array
    {
        return [
            'name',
            'account_number',
            'payment_terms',
            'lead_time_days',
            'free_shipping_threshold_usd',
        ];
    }

    public function searchableBy(): array
    {
        return [
            'name',
            'account_number',
        ];
    }

    public function sortableBy(): array
    {
        return [
            'id',
            'name',
            'lead_time_days',
            'free_shipping_threshold_usd',
            'created_at',
            'updated_at',
        ];
    }

    public function aggregates(): array
    {
        return ['parts', 'parts.bill_of_material'];
    }

    public function index(Request $request)
    {
        Log::info('SupplierController@index called', ['request' => $request->all()]);
        return parent::index($request);
    }

    protected function beforeShow(Request $request, $supplier): void
    {
        Log::info('SupplierController@beforeShow called', ['supplierId' => $supplier, 'request' => $request->all()]);
    }

    protected function afterShow(Request $request, $supplier): void
    {
        Log::info('SupplierController@afterShow called', ['supplierId' => $supplier->id, 'request' => $request->all()]);
    }
}
