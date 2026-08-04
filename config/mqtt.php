<?php

return [
    'host' => env('MQTT_HOST', 'test.mosquitto.org'),
    'port' => (int) env('MQTT_PORT', 1883),
    'topic' => env('MQTT_TOPIC', 'digitaltwin/lokasi1/data'),
    'point' => env('MQTT_POINT', 'hulu'),
    'client_id_prefix' => env('MQTT_CLIENT_ID_PREFIX', 'brantas-backend'),
];
