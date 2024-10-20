<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        // Fetch purchase orders logic here
        return Inertia::render('PurchaseOrders/Index');
    }

    public function create()
    {
        return Inertia::render('PurchaseOrders/Create');
    }

    public function store(Request $request)
    {
        // Store purchase order logic here
    }

    public function receive()
    {
        return Inertia::render('PurchaseOrders/Receive');
    }
}
