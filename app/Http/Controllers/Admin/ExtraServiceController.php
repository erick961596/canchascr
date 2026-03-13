<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtraService;

class ExtraServiceController extends Controller
{
    public function index()
    {
        $services = ExtraService::with('venue.owner')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('pages.admin.extra-services.index', compact('services'));
    }
}
