<?php

namespace Tests\Feature;

use App\Services\TelemetryIngestor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryIngestorTest extends TestCase
{
    use RefreshDatabase;

    public function test_mqtt_payload_is_normalized_and_persisted(): void
    {
        $payload = [
            'device' => 'Device-1',
            'fw_version' => '1.0.2',
            'connection' => 'wifi',
            'uptime_ms' => 1194640,
            'rain_mm_1H' => 0,
            'rain_mm_24H' => 0.279399991,
            'wind_ms' => 0,
            'temperature_c' => 31.89999962,
            'humidity_percent' => 50.40000153,
            'river_level_m' => 0.790000021,
            'wifi_rssi' => -65,
            'gsm_signal' => 0,
            'measured_at' => '2026-08-04T10:15:30+07:00',
        ];

        $reading = app(TelemetryIngestor::class)->ingest($payload, 'hulu');

        $this->assertSame('Device-1', $reading->device);
        $this->assertSame(79.0, $reading->water_level);
        $this->assertSame(0.0, $reading->wind_speed);
        $this->assertSame(50.4, $reading->humidity);
        $this->assertSame(-65, $reading->wifi_rssi);
        $this->assertSame('normal', $reading->status);
        $this->assertSame('2026-08-04T03:15:30+00:00', $reading->recorded_at->toIso8601String());
        $this->assertDatabaseHas('readings', ['device' => 'Device-1', 'point' => 'hulu']);
    }

    public function test_unix_milliseconds_are_accepted_as_measurement_time(): void
    {
        $payload = [
            'device' => 'Device-1', 'rain_mm_1H' => 0, 'wind_ms' => 0,
            'temperature_c' => 25, 'river_level_m' => 0.5,
            'timestamp' => 1785813330000,
        ];

        $reading = app(TelemetryIngestor::class)->ingest($payload, 'hulu');

        $this->assertSame(1785813330, $reading->recorded_at->timestamp);
    }
}
