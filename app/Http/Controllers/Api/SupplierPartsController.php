<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use App\Models\Part;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\RelationController;
use Orion\Http\Requests\Request;

class SupplierPartsController extends RelationController
{
    use DisableAuthorization;

    protected $model = Supplier::class;
    protected $relation = 'parts';

    public function includes(): array
    {
        return ['supplier'];
    }

    public function filterableBy(): array
    {
        return ['part_number', 'description', 'unit_cost'];
    }

    public function sortableBy(): array
    {
        return ['part_number', 'description', 'unit_cost'];
    }

    public function searchableBy(): array
    {
        return ['part_number', 'description'];
    }

    public function index(Request $request, $parentKey)
    {
        return parent::index($request, $parentKey);
    }
}
