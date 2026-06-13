<?php

namespace Database\Seeders;

use App\Enums\GameType;
use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            ['name' => 'Station 1', 'game_type' => GameType::Ps5],
            ['name' => 'Station 2', 'game_type' => GameType::Ps5],
            ['name' => 'Station 3', 'game_type' => GameType::Ps5],
            ['name' => 'Station 4', 'game_type' => GameType::DrivingSimulator],
        ];

        foreach ($stations as $station) {
            Station::updateOrCreate(
                ['name' => $station['name']],
                ['game_type' => $station['game_type'], 'is_active' => true]
            );
        }
    }
}
