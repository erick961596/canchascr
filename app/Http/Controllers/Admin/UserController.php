<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('subscription.plan')->latest()->paginate(20);
        return view('pages.admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|in:admin,owner,user',
            'name' => 'required|string|max:100',
        ]);
        $user->update($data);
        return response()->json(['message' => 'Usuario actualizado.']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'No podés eliminarte a vos mismo.'], 422);
        }
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado.']);
    }
}
