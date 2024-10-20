<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function create()
    {
        return Inertia::render('PurchaseOrders/Create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'parts' => 'required|array',
            'parts.*.part_id' => 'required|exists:parts,id',
            'parts.*.quantity' => 'required|integer|min:1',
            'parts.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $purchaseOrder = PurchaseOrder::create([
            'supplier_id' => $validatedData['supplier_id'],
            'status' => 'draft',
        ]);

        foreach ($validatedData['parts'] as $partData) {
            $purchaseOrder->parts()->attach($partData['part_id'], [
                'quantity' => $partData['quantity'],
                'unit_cost' => $partData['unit_cost'],
            ]);
        }

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order created successfully.');
    }
}
