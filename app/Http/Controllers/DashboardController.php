<?php

namespace App\Http\Controllers;

use App\Models\Reading;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'points' => Reading::query()->latest('recorded_at')->get()->unique('point')->keyBy('point'),
            'history' => Reading::query()->latest('recorded_at')->limit(60)->get()->sortBy('recorded_at')->values(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function settings(): JsonResponse
    {
        return response()->json(['settings' => Setting::query()->pluck('value', 'key')]);
    }

    public function storeReading(Request $request): JsonResponse
    {
        $data = $request->validate([
            'point' => ['required', 'in:hulu,tengah,hilir'],
            'water_level' => ['required', 'numeric', 'min:0'],
            'rainfall' => ['required', 'numeric', 'min:0'],
            'temperature' => ['required', 'numeric'],
            'wind_speed' => ['required', 'numeric', 'min:0'],
            'risk_index' => ['required', 'integer', 'between:0,100'],
            'status' => ['required', 'in:normal,waspada,bahaya'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $data['recorded_at'] ??= now();

        return response()->json(Reading::create($data), 201);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'alert_threshold' => ['required', 'integer', 'between:1,99'],
            'danger_threshold' => ['required', 'integer', 'between:2,100', 'gt:alert_threshold'],
            'water_weight' => ['required', 'integer', 'between:0,100'],
            'rain_weight' => ['required', 'integer', 'between:0,100'],
            'refresh_seconds' => ['required', 'integer', 'between:2,300'],
        ]);

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return response()->json(['settings' => Setting::query()->pluck('value', 'key')]);
    }
}
