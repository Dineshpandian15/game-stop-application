<?php

namespace App\Enums;

enum GameType: string
{
    case Ps5 = 'ps5';
    case DrivingSimulator = 'driving_simulator';

    public function label(): string
    {
        return match ($this) {
            self::Ps5 => 'PS5',
            self::DrivingSimulator => 'Driving Simulator',
        };
    }
}
