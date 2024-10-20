<?php

namespace App\Http\Controllers\Api;

use App\Models\Part;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Illuminate\Http\Request;

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

    protected function keyName(): string
    {
        return 'id';
    }

    protected function paginationLimit(): int
    {
        return 50; // Adjust this value based on your needs
    }

    public function index(Request $request)
    {
        $query = $this->buildIndexFetchQuery($request);

        $supplierIdFilter = $request->input('filter.supplier_id');
        if ($supplierIdFilter) {
            $query->where('supplier_id', $supplierIdFilter);
        }

        $parts = $query->paginate($this->paginationLimit());

        return $this->response($parts);
    }
}
