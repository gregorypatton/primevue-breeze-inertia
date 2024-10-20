<?php

namespace App\Http\Controllers\Api;

use App\Models\Part;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Requests\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);

        $supplierIdFilter = $request->input('filter.supplier_id');
        if ($supplierIdFilter) {
            $query->where('supplier_id', $supplierIdFilter);
        }

        return $query;
    }

    public function index(Request $request)
    {
        try {
            return parent::index($request);
        } catch (\Exception $e) {
            Log::error('Error in PartController@index: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching parts.'], 500);
        }
    }
}
