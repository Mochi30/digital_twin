<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $fillable = [
        'point', 'device', 'fw_version', 'connection', 'uptime_ms', 'water_level',
        'rainfall', 'rainfall_24h', 'temperature', 'humidity', 'wind_speed',
        'wifi_rssi', 'gsm_signal', 'risk_index', 'status', 'raw_payload', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime', 'water_level' => 'float', 'rainfall' => 'float',
            'rainfall_24h' => 'float', 'temperature' => 'float', 'humidity' => 'float',
            'wind_speed' => 'float', 'risk_index' => 'integer', 'uptime_ms' => 'integer',
            'wifi_rssi' => 'integer', 'gsm_signal' => 'integer', 'raw_payload' => 'array',
        ];
    }
}
