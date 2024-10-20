<?php

namespace App\Http\Controllers\Api;

use App\Models\Part;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;

class PartController extends Controller
{
    protected $model = Part::class;

    use DisableAuthorization;

    public function includes(): array
    {
        return ['supplier'];
    }

    public function filterableBy(): array
    {
        return ['supplier_id', 'part_number', 'description', 'unit_cost'];
    }

    public function sortableBy(): array
    {
        return ['part_number', 'description', 'unit_cost'];
    }

    public function searchableBy(): array
    {
        return ['part_number', 'description'];
    }
}
