<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrder;
use App\Models\Part;
use App\Models\Location;
use App\Models\Supplier;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Illuminate\Http\Request;
use App\Orion\Transformers\AddressDTOTransformer;

class PurchaseOrderController extends Controller
{
    use DisableAuthorization;

    protected $model = PurchaseOrder::class;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'bill_to_address' => AddressDTOTransformer::class,
        'ship_from_address' => AddressDTOTransformer::class,
        'ship_to_address' => AddressDTOTransformer::class,
    ];

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'bill_to_location_id' => 'required|exists:locations,id',
            'ship_to_location_id' => 'required|exists:locations,id',
            'bill_to_address_index' => 'required|integer|min:0',
            'ship_from_address_index' => 'required|integer|min:0',
            'ship_to_address_index' => 'required|integer|min:0',
            'parts' => 'required|array',
            'parts.*.id' => 'required|exists:parts,id',
            'parts.*.quantity' => 'required|integer|min:1',
        ]);

        $supplier = Supplier::findOrFail($validatedData['supplier_id']);
        $billToLocation = Location::findOrFail($validatedData['bill_to_location_id']);
        $shipToLocation = Location::findOrFail($validatedData['ship_to_location_id']);

        $purchaseOrder = PurchaseOrder::create([
            'supplier_id' => $validatedData['supplier_id'],
            'bill_to_location_id' => $validatedData['bill_to_location_id'],
            'ship_to_location_id' => $validatedData['ship_to_location_id'],
            'bill_to_address_index' => $validatedData['bill_to_address_index'],
            'ship_from_address_index' => $validatedData['ship_from_address_index'],
            'ship_to_address_index' => $validatedData['ship_to_address_index'],
        ]);

        foreach ($validatedData['parts'] as $partData) {
            $part = Part::findOrFail($partData['id']);
            $purchaseOrder->parts()->attach($part->id, [
                'quantity' => $partData['quantity'],
                'unit_cost' => $part->replenishment_data['purchaseTerms'][0]['cost_per_part'] ?? $part->unit_cost,
            ]);
        }

        $purchaseOrder->load('parts', 'supplier', 'location');

        return response()->json($purchaseOrder, 201);
    }

    public function includes(): array
    {
        return ['supplier', 'parts', 'location'];
    }

    public function filterableBy(): array
    {
        return ['supplier_id', 'location_id', 'created_at'];
    }

    public function sortableBy(): array
    {
        return ['created_at', 'total_cost'];
    }

    protected function afterSave(Request $request, $entity)
    {
        $entity->load('parts', 'supplier', 'location');
    }

    protected function afterFetch($entity)
    {
        $entity->load('parts', 'supplier', 'location');
        return $entity;
    }
}
