<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActiveSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (!$user || !$user->hasActiveSubscription()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Suscripción requerida.'], 403);
            }
            return redirect()->route('owner.subscription.index')
                ->with('warning', 'Necesitás una suscripción activa para acceder.');
        }
        return $next($request);
    }
}
