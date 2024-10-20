<?php

namespace App\Http\Controllers\Api;

use App\Models\Location;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Requests\Request;

class LocationController extends Controller
{
    use DisableAuthorization;

    protected $model = Location::class;

    public function includes(): array
    {
        return ['addresses'];
    }

    public function filterableBy(): array
    {
        return ['id', 'name', 'type'];
    }

    public function sortableBy(): array
    {
        return ['id', 'name', 'type'];
    }

    public function searchableBy(): array
    {
        return ['name'];
    }

    public function index(Request $request)
    {
        return parent::index($request);
    }
}
