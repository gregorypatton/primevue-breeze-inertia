<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use App\Models\Part;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\RelationController;
use Orion\Http\Requests\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class SupplierPartsController extends RelationController
{
    protected $model = Supplier::class;
    protected $relation = 'parts';

    use DisableAuthorization;

    public function includes(): array
    {
        return ['bill_of_material'];
    }

    public function filterableBy(): array
    {
        return [
            'name',
            'description',
            'price',
        ];
    }

    public function sortableBy(): array
    {
        return [
            'id',
            'name',
            'price',
            'created_at',
            'updated_at',
        ];
    }

    protected function beforeIndex(Request $request, Model $parentEntity): void
    {
        Log::info('SupplierPartsController@beforeIndex called', [
            'request' => $request->all(),
            'supplierId' => $parentEntity->id
        ]);
    }

    protected function afterIndex(Request $request, Model $parentEntity, $entities): void
    {
        Log::info('SupplierPartsController@afterIndex called', [
            'supplierId' => $parentEntity->id,
            'count' => $entities->count()
        ]);
    }
}
