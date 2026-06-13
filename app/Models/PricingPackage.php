<?php

namespace App\Models;

use App\Enums\GameType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingPackage extends Model
{
    protected $fillable = [
        'game_type',
        'player_count',
        'duration_minutes',
        'price',
        'label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'game_type' => GameType::class,
            'player_count' => 'integer',
            'duration_minutes' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    public function durationLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($minutes === 0) {
            return $hours.' hr'.($hours > 1 ? 's' : '');
        }

        return $hours.' hr '.$minutes.' min';
    }
}
