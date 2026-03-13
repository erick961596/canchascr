<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin() { return view('pages.auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            LogService::auth('login_success', "Login exitoso: {$user->email}", [
                'role' => $user->role,
            ], $user->id);

            return match($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'owner' => redirect()->route('owner.subscription.index'),
                default => redirect()->route('player.home'),
            };
        }

        LogService::auth('login_failed', "Login fallido: {$request->email}", [
            'email' => $request->email,
        ]);

        return back()->withErrors(['email' => 'Credenciales incorrectas.'])->withInput();
    }

    public function logout(Request $request)
    {
        $user = auth()->user();
        LogService::auth('logout', "Logout: {$user->email}", [], $user->id);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
