<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActiveSubscription
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasActiveSubscription()) {
            return redirect()->route('owner.subscription.index')
                ->with('warning', 'Necesitás una suscripción activa para acceder a esta sección.');
        }

        return $next($request);
    }
}
