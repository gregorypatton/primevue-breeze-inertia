<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkOrderController extends Controller
{
    public function create()
    {
        return Inertia::render('WorkOrders/Create');
    }

    public function modify()
    {
        return Inertia::render('WorkOrders/Modify');
    }
}
