<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class PublishMqttTest extends Command
{
    protected $signature = 'mqtt:publish-test';
    protected $description = 'Publish a sample device payload to the configured MQTT topic';

    public function handle(): int
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
        ];
        $mqtt = new MqttClient(
            (string) config('mqtt.host'),
            (int) config('mqtt.port'),
            'brantas-test-'.getmypid(),
            MqttClient::MQTT_3_1_1,
        );
        $settings = (new ConnectionSettings())->setConnectTimeout(15)->setKeepAliveInterval(20);
        $mqtt->connect($settings, true);
        $mqtt->publish((string) config('mqtt.topic'), json_encode($payload, JSON_THROW_ON_ERROR), MqttClient::QOS_AT_LEAST_ONCE);
        $mqtt->disconnect();
        $this->components->info('Sample payload published.');

        return self::SUCCESS;
    }
}
