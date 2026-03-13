<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::with('venue.owner')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('pages.admin.promotions.index', compact('promotions'));
    }
}
