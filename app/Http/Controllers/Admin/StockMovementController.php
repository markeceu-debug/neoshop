<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(['user', 'stockable'])
            ->latest()
            ->paginate(50);

        return view('admin.stock.movements', compact('movements'));
    }
}