<?php

namespace App\Http\Controllers;

use App\Models\Reading;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json($this->dashboardPayload());
    }

    public function stream(Request $request): StreamedResponse
    {
        return response()->stream(function (): void {
            $lastId = null;
            $startedAt = microtime(true);
            $lastHeartbeatAt = 0.0;

            while (! connection_aborted() && microtime(true) - $startedAt < 25) {
                $latestId = Reading::query()->max('id');

                if ($latestId !== $lastId) {
                    echo "event: dashboard\n";
                    echo 'data: '.json_encode($this->dashboardPayload(), JSON_THROW_ON_ERROR)."\n\n";
                    $lastId = $latestId;
                } elseif (microtime(true) - $lastHeartbeatAt >= 10) {
                    echo ": heartbeat\n\n";
                    $lastHeartbeatAt = microtime(true);
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                usleep(250_000);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function dashboardPayload(): array
    {
        return [
            'points' => Reading::query()->latest('recorded_at')->get()->unique('point')->keyBy('point'),
            'history' => Reading::query()->latest('recorded_at')->limit(60)->get()->sortBy('recorded_at')->values(),
            'server_time' => now()->toIso8601String(),
        ];
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
