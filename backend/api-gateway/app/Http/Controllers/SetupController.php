<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    public function status(): JsonResponse
    {
        $installed = User::query()->count() > 0;

        return response()->json([
            'installed' => $installed,
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        if (User::query()->count() > 0) {
            return response()->json(['message' => 'Already installed'], 400);
        }

        $validated = $request->validate([
            'dashboard_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Setting::updateOrCreate(
            ['key' => 'dashboard_name'],
            ['value' => $validated['dashboard_name']]
        );

        return response()->json([
            'status' => 'ok',
        ]);
    }
}


