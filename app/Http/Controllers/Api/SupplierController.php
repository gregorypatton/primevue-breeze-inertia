<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\Controller;

class SupplierController extends Controller
{
    protected $model = Supplier::class;

    use DisableAuthorization;
    public function includes(): array
    {
        return ['locations'];
    }
}
