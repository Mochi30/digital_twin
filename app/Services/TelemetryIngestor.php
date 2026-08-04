<?php

namespace App\Services;

use App\Models\Reading;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;

class TelemetryIngestor
{
    public function ingest(array $payload, string $point = 'hulu'): Reading
    {
        $measuredAt = $this->measurementTime($payload);

        $data = Validator::make($payload, [
            'device' => ['required', 'string', 'max:100'],
            'fw_version' => ['nullable', 'string', 'max:30'],
            'connection' => ['nullable', 'in:wifi,gsm,ethernet'],
            'uptime_ms' => ['nullable', 'integer', 'min:0'],
            'rain_mm_1H' => ['required', 'numeric', 'min:0'],
            'rain_mm_24H' => ['nullable', 'numeric', 'min:0'],
            'wind_ms' => ['required', 'numeric', 'min:0'],
            'temperature_c' => ['required', 'numeric', 'between:-50,100'],
            'humidity_percent' => ['nullable', 'numeric', 'between:0,100'],
            'river_level_m' => ['required', 'numeric', 'min:0'],
            'wifi_rssi' => ['nullable', 'integer', 'between:-150,0'],
            'gsm_signal' => ['nullable', 'integer', 'min:0'],
        ])->validate();

        $settings = Setting::query()->pluck('value', 'key');
        $waterLevelCm = round((float) $data['river_level_m'] * 100, 2);
        $rainfall = round((float) $data['rain_mm_1H'], 4);
        $waterWeight = ((int) ($settings['water_weight'] ?? 60)) / 100;
        $rainWeight = ((int) ($settings['rain_weight'] ?? 40)) / 100;
        $risk = min(100, max(0, (int) round($waterLevelCm * $waterWeight * 0.55 + $rainfall * $rainWeight * 2.8)));
        $alertThreshold = (int) ($settings['alert_threshold'] ?? 35);
        $dangerThreshold = (int) ($settings['danger_threshold'] ?? 65);
        $status = $risk >= $dangerThreshold ? 'bahaya' : ($risk >= $alertThreshold ? 'waspada' : 'normal');

        return Reading::query()->create([
            'point' => $point,
            'device' => $data['device'],
            'fw_version' => $data['fw_version'] ?? null,
            'connection' => $data['connection'] ?? null,
            'uptime_ms' => $data['uptime_ms'] ?? null,
            'water_level' => $waterLevelCm,
            'rainfall' => $rainfall,
            'rainfall_24h' => isset($data['rain_mm_24H']) ? round((float) $data['rain_mm_24H'], 4) : null,
            'temperature' => round((float) $data['temperature_c'], 2),
            'humidity' => isset($data['humidity_percent']) ? round((float) $data['humidity_percent'], 2) : null,
            'wind_speed' => round((float) $data['wind_ms'] * 3.6, 2),
            'wifi_rssi' => $data['wifi_rssi'] ?? null,
            'gsm_signal' => $data['gsm_signal'] ?? null,
            'risk_index' => $risk,
            'status' => $status,
            'raw_payload' => $payload,
            // recorded_at is the time measured by the device. created_at remains
            // the server receipt time, so transport delay can still be audited.
            'recorded_at' => $measuredAt,
        ]);
    }

    private function measurementTime(array $payload): CarbonImmutable
    {
        $value = $payload['measured_at'] ?? $payload['recorded_at'] ?? $payload['timestamp'] ?? null;

        if ($value === null || $value === '') {
            return CarbonImmutable::now();
        }

        if (is_numeric($value)) {
            $timestamp = (float) $value;
            if ($timestamp > 10_000_000_000) {
                $timestamp /= 1000;
            }

            return CarbonImmutable::createFromTimestampUTC($timestamp);
        }

        return CarbonImmutable::parse((string) $value);
    }
}
