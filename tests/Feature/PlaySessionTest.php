<?php

namespace Tests\Feature;

use App\Enums\GameType;
use App\Enums\PlaySessionStatus;
use App\Models\Customer;
use App\Models\PlaySession;
use App\Models\PricingPackage;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaySessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_staff_can_start_and_stop_a_session(): void
    {
        $user = User::where('email', 'admin@gamestop.local')->first();
        $station = Station::first();
        $package = PricingPackage::where('game_type', GameType::Ps5)->first();

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'station_id' => $station->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '9876543210',
            'game_type' => GameType::Ps5->value,
            'player_count' => 1,
            'pricing_package_id' => $package->id,
        ]);

        $response->assertRedirect(route('dashboard'));

        $session = PlaySession::first();
        $this->assertEquals(PlaySessionStatus::Active, $session->status);

        $this->actingAs($user)->post(route('sessions.stop', $session))
            ->assertRedirect();

        $session->refresh();
        $this->assertEquals(PlaySessionStatus::Completed, $session->status);
        $this->assertEquals($package->price, $session->amount);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_birthday_offer_session_is_free(): void
    {
        $user = User::where('email', 'admin@gamestop.local')->first();
        $station = Station::first();

        $this->actingAs($user)->post(route('sessions.store'), [
            'station_id' => $station->id,
            'customer_name' => 'Birthday Kid',
            'customer_phone' => '9000000001',
            'date_of_birth' => now()->toDateString(),
            'game_type' => GameType::Ps5->value,
            'player_count' => 1,
            'is_birthday_offer' => true,
        ])->assertRedirect(route('dashboard'));

        $session = PlaySession::first();
        $this->assertTrue($session->is_birthday_offer);
        $this->assertEquals(70, $session->planned_duration_minutes);

        $this->actingAs($user)->post(route('sessions.stop', $session));

        $session->refresh();
        $this->assertEquals(0, $session->amount);
    }
}
