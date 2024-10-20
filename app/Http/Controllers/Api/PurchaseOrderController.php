<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrder;
use App\Models\Part;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    use DisableAuthorization;

    protected $model = PurchaseOrder::class;

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_to_location_id' => 'required|exists:locations,id',
            'ship_to_location_id' => 'required|exists:locations,id',
            'parts' => 'required|array',
            'parts.*.id' => 'required|exists:parts,id',
            'parts.*.quantity' => 'required|integer|min:1',
        ]);

        $purchaseOrder = PurchaseOrder::create([
            'supplier_id' => $validatedData['supplier_id'],
            'bill_to_location_id' => $validatedData['bill_to_location_id'],
            'ship_to_location_id' => $validatedData['ship_to_location_id'],
            // Add other fields as necessary
        ]);

        foreach ($validatedData['parts'] as $partData) {
            $part = Part::findOrFail($partData['id']);
            $purchaseOrder->parts()->attach($part->id, [
                'quantity' => $partData['quantity'],
                'unit_cost' => $part->unit_cost,
                // Add other fields as necessary
            ]);
        }

        return response()->json($purchaseOrder->load('parts', 'billToLocation', 'shipToLocation'), 201);
    }

    public function includes(): array
    {
        return ['supplier', 'parts', 'billToLocation', 'shipToLocation'];
    }

    public function filterableBy(): array
    {
        return ['supplier_id', 'bill_to_location_id', 'ship_to_location_id', 'created_at'];
    }

    public function sortableBy(): array
    {
        return ['created_at', 'total_cost'];
    }
}
