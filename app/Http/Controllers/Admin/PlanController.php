<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')->orderBy('price')->get();
        return view('pages.admin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'price'       => 'required|numeric|min:0',
            'court_limit' => 'required|integer|min:1',
        ]);

        Plan::create($data + ['active' => true]);

        return response()->json(['message' => 'Plan creado correctamente.']);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'price'       => 'required|numeric|min:0',
            'court_limit' => 'required|integer|min:1',
            'active'      => 'boolean',
        ]);

        $plan->update($data);

        return response()->json(['message' => 'Plan actualizado.']);
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->whereIn('status', ['active','pending'])->exists()) {
            return response()->json(['message' => 'No se puede eliminar: tiene suscripciones activas.'], 422);
        }

        $plan->delete();
        return response()->json(['message' => 'Plan eliminado.']);
    }

    public function toggle(Plan $plan)
    {
        $plan->update(['active' => !$plan->active]);
        return response()->json([
            'message' => $plan->active ? 'Plan activado.' : 'Plan desactivado.',
            'active'  => $plan->active,
        ]);
    }
}
