<?php

namespace Database\Seeders;

use App\Models\Reading;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaults = ['alert_threshold' => 35, 'danger_threshold' => 65, 'water_weight' => 60, 'rain_weight' => 40, 'refresh_seconds' => 10];
        foreach ($defaults as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $points = [
            'hulu' => [82, 4, 24, 12, 31],
            'tengah' => [61, 2, 25, 9, 22],
            'hilir' => [45, 1, 26, 7, 15],
        ];

        foreach ($points as $point => [$level, $rain, $temp, $wind, $risk]) {
            for ($i = 11; $i >= 0; $i--) {
                Reading::query()->create([
                    'point' => $point,
                    'water_level' => $level + random_int(-4, 4),
                    'rainfall' => max(0, $rain + random_int(-10, 10) / 10),
                    'temperature' => $temp + random_int(-5, 5) / 10,
                    'wind_speed' => $wind + random_int(-2, 2),
                    'risk_index' => max(0, $risk + random_int(-3, 3)),
                    'status' => $risk >= 65 ? 'bahaya' : ($risk >= 35 ? 'waspada' : 'normal'),
                    'recorded_at' => now()->subMinutes($i * 5),
                ]);
            }
        }
    }
}
