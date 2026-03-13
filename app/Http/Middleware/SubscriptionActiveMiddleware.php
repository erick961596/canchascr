<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SubscriptionActiveMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user->hasActiveSubscription()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Suscripción requerida.'], 402);
            }
            return redirect()->route('owner.subscription.index')
                ->with('warning', 'Necesitás una suscripción activa para acceder a esta sección.');
        }

        return $next($request);
    }
}
