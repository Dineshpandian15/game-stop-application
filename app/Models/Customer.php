<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'date_of_birth',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    public function isBirthdayToday(): bool
    {
        if (! $this->date_of_birth) {
            return false;
        }

        return $this->date_of_birth->isBirthday();
    }
}
