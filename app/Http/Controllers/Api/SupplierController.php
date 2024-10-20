<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request;

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

    public function index(Request $request)
    {
        return parent::index($request);
    }
}
