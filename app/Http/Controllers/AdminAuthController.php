<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $validUsername = hash_equals((string) config('admin.username'), $credentials['username']);
        $validPassword = Hash::check($credentials['password'], (string) config('admin.password_hash'));

        if (! $validUsername || ! $validPassword) {
            return response()->json(['message' => 'Identitas admin tidak dikenali.'], 422);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);

        return response()->json(['redirect' => route('admin.dashboard')]);
    }

    public function session(): JsonResponse
    {
        return response()->json(['authenticated' => true]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['redirect' => route('admin.login')]);
    }
}
