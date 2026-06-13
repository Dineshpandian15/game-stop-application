<?php

namespace App\Models;

use App\Enums\GameType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Station extends Model
{
    protected $fillable = [
        'name',
        'game_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'game_type' => GameType::class,
            'is_active' => 'boolean',
        ];
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(PlaySession::class)
            ->whereIn('status', ['active', 'paused'])
            ->latestOfMany();
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ! $this->activeSession()->exists();
    }
}
