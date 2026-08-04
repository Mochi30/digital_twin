<?php

namespace App\Console\Commands;

use App\Services\TelemetryIngestor;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use Throwable;

class SubscribeMqttTelemetry extends Command
{
    protected $signature = 'mqtt:subscribe {--retry-delay=5 : Seconds before reconnecting}';
    protected $description = 'Subscribe MQTT telemetry and persist device readings';

    public function handle(TelemetryIngestor $ingestor): int
    {
        $host = (string) config('mqtt.host');
        $port = (int) config('mqtt.port');
        $topic = (string) config('mqtt.topic');
        $point = (string) config('mqtt.point');
        $retryDelay = max(1, (int) $this->option('retry-delay'));

        while (true) {
            $clientId = sprintf('%s-%s-%d', config('mqtt.client_id_prefix'), gethostname(), getmypid());
            $mqtt = new MqttClient($host, $port, $clientId, MqttClient::MQTT_3_1_1);
            $settings = (new ConnectionSettings())
                ->setConnectTimeout(15)
                ->setKeepAliveInterval(30)
                ->setUseTls(false);

            $this->components->info("Connecting to mqtt://{$host}:{$port}");

            try {
                $mqtt->connect($settings, true);
                $mqtt->subscribe($topic, function (string $receivedTopic, string $message) use ($ingestor, $point): void {
                    try {
                        $payload = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
                        $reading = $ingestor->ingest($payload, $point);
                        $this->line(sprintf(
                            '[%s] %s -> %s | %.2f cm | IR %d (%s)',
                            now()->format('H:i:s'),
                            $receivedTopic,
                            $reading->device,
                            $reading->water_level,
                            $reading->risk_index,
                            strtoupper($reading->status),
                        ));
                    } catch (ValidationException $exception) {
                        $this->components->error('Payload rejected: '.collect($exception->errors())->flatten()->join(' '));
                    } catch (Throwable $exception) {
                        report($exception);
                        $this->components->error('Payload failed: '.$exception->getMessage());
                    }
                }, MqttClient::QOS_AT_LEAST_ONCE);

                $this->components->info("Subscribed to {$topic} as point {$point}. Press Ctrl+C to stop.");
                $mqtt->loop(true);
                $mqtt->disconnect();
            } catch (Throwable $exception) {
                $this->components->error('MQTT connection lost: '.$exception->getMessage());
                $this->line("Retrying in {$retryDelay} seconds...");
                sleep($retryDelay);
            }
        }
    }
}
