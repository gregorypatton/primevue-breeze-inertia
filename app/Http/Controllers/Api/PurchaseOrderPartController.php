<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrderPart;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Requests\Request;

class PurchaseOrderPartController extends Controller
{
    use DisableAuthorization;

    protected $model = PurchaseOrderPart::class;

    public function includes(): array
    {
        return ['purchaseOrder', 'part'];
    }

    public function filterableBy(): array
    {
        return [
            'id',
            'purchase_order_id',
            'part_id',
            'quantity_ordered',
            'unit_cost',
            'total_cost',
            'quantity_invoiced',
            'quantity_received',
            'status',
        ];
    }

    public function sortableBy(): array
    {
        return [
            'id',
            'quantity_ordered',
            'unit_cost',
            'total_cost',
            'quantity_invoiced',
            'quantity_received',
            'status',
        ];
    }

    public function searchableBy(): array
    {
        return [
            'notes',
        ];
    }

    public function index(Request $request)
    {
        return parent::index($request);
    }
}
