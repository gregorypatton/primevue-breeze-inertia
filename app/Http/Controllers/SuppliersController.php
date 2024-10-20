<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\JsonResponse;

class SuppliersController extends Controller
{
    public function index(): JsonResponse
    {
        $suppliers = Supplier::all();
        return response()->json(['suppliers' => $suppliers]);
    }
}
