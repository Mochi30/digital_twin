<?php

namespace Tests\Feature;

use App\Models\Reading;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_latest_points_history_and_settings(): void
    {
        Setting::query()->create(['key' => 'refresh_seconds', 'value' => '10']);
        Reading::query()->create([
            'point' => 'hulu',
            'water_level' => 82,
            'rainfall' => 4,
            'temperature' => 24,
            'wind_speed' => 12,
            'risk_index' => 31,
            'status' => 'normal',
            'recorded_at' => now(),
        ]);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('points.hulu.water_level', 82)
            ->assertJsonMissingPath('settings');
    }

    public function test_settings_reject_an_unauthenticated_request(): void
    {
        $this->putJson('/admin/settings', [
            'alert_threshold' => 35,
            'danger_threshold' => 65,
            'water_weight' => 60,
            'rain_weight' => 40,
            'refresh_seconds' => 15,
        ])->assertUnauthorized();
    }

    public function test_authenticated_admin_can_persist_settings(): void
    {
        Setting::query()->create(['key' => 'refresh_seconds', 'value' => '10']);
        $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/settings')
            ->assertOk()
            ->assertJsonPath('settings.refresh_seconds', '10');

        $this->withSession(['admin_authenticated' => true])->putJson('/admin/settings', [
            'alert_threshold' => 35,
            'danger_threshold' => 65,
            'water_weight' => 60,
            'rain_weight' => 40,
            'refresh_seconds' => 15,
        ])->assertOk()->assertJsonPath('settings.refresh_seconds', '15');

        $this->assertDatabaseHas('settings', ['key' => 'danger_threshold', 'value' => '65']);
    }

    public function test_admin_can_login_and_logout_through_the_concealed_entry(): void
    {
        config()->set('admin.username', 'operator');
        config()->set('admin.password_hash', password_hash('secret-pass', PASSWORD_BCRYPT));

        $this->postJson('/ruang-kendali-ews/login', [
            'username' => 'operator',
            'password' => 'secret-pass',
        ])->assertOk()->assertSessionHas('admin_authenticated', true);

        $this->get('/admin')->assertOk();
        $this->postJson('/admin/logout')->assertOk();
        $this->get('/admin')->assertRedirect('/ruang-kendali-ews');
    }
}
