<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrder;
use App\Models\Part;
use App\Models\Location;
use App\Models\Supplier;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Requests\Request;
use App\Orion\Transformers\AddressDTOTransformer;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    use DisableAuthorization;

    protected $model = PurchaseOrder::class;

    protected $casts = [
        'bill_to_address' => AddressDTOTransformer::class,
        'ship_from_address' => AddressDTOTransformer::class,
        'ship_to_address' => AddressDTOTransformer::class,
        'status' => PurchaseOrderStatus::class,
    ];

    public function store(Request $request)
    {
        try {
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

            return DB::transaction(function () use ($validatedData) {
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
                    'status' => PurchaseOrderStatus::DRAFT,
                ]);

                foreach ($validatedData['parts'] as $partData) {
                    $part = Part::findOrFail($partData['id']);
                    $purchaseOrder->parts()->attach($part->id, [
                        'quantity' => $partData['quantity'],
                        'unit_cost' => $part->replenishment_data['purchaseTerms'][0]['cost_per_part'] ?? $part->unit_cost,
                    ]);
                }

                $purchaseOrder->load(['parts', 'supplier', 'billToLocation', 'shipToLocation']);

                return response()->json($purchaseOrder, 201);
            });
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating the purchase order.'], 500);
        }
    }

    public function includes(): array
    {
        return ['supplier', 'parts', 'billToLocation', 'shipToLocation'];
    }

    public function filterableBy(): array
    {
        return [
            'id',
            'number',
            'supplier_id',
            'bill_to_location_id',
            'ship_to_location_id',
            'status',
            'total_cost',
            'created_at',
            'updated_at',
        ];
    }

    public function sortableBy(): array
    {
        return [
            'id',
            'number',
            'created_at',
            'updated_at',
            'total_cost',
            'status',
        ];
    }

    public function searchableBy(): array
    {
        return [
            'number',
            'supplier.name',
        ];
    }

    protected function afterSave(Request $request, $entity)
    {
        $entity->load(['parts', 'supplier', 'billToLocation', 'shipToLocation']);
    }

    protected function afterFetch($entity)
    {
        $entity->load(['parts', 'supplier', 'billToLocation', 'shipToLocation']);
        return $entity;
    }

    public function index(Request $request)
    {
        return parent::index($request);
    }
}
