<?php

namespace Database\Seeders;

use App\Enums\GameType;
use App\Models\PricingPackage;
use Illuminate\Database\Seeder;

class PricingPackageSeeder extends Seeder
{
    public function run(): void
    {
        $ps5Rates = [
            1 => [60 => 110, 120 => 190, 180 => 260],
            2 => [60 => 170, 120 => 280, 180 => 380],
            3 => [60 => 240, 120 => 500, 180 => 550],
            4 => [60 => 300, 120 => 520, 180 => 720],
        ];

        foreach ($ps5Rates as $players => $durations) {
            foreach ($durations as $minutes => $price) {
                PricingPackage::updateOrCreate(
                    [
                        'game_type' => GameType::Ps5,
                        'player_count' => $players,
                        'duration_minutes' => $minutes,
                    ],
                    ['price' => $price, 'is_active' => true]
                );
            }
        }

        $drivingRates = [
            1 => [60 => 130],
            2 => [60 => 170],
        ];

        foreach ($drivingRates as $players => $durations) {
            foreach ($durations as $minutes => $price) {
                PricingPackage::updateOrCreate(
                    [
                        'game_type' => GameType::DrivingSimulator,
                        'player_count' => $players,
                        'duration_minutes' => $minutes,
                    ],
                    ['price' => $price, 'is_active' => true]
                );
            }
        }
    }
}
