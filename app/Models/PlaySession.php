<?php

namespace App\Models;

use App\Enums\GameType;
use App\Enums\PlaySessionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaySession extends Model
{
    public const BIRTHDAY_DURATION_MINUTES = 70;

    protected $fillable = [
        'station_id',
        'customer_id',
        'pricing_package_id',
        'game_type',
        'player_count',
        'planned_duration_minutes',
        'started_at',
        'ends_at',
        'ended_at',
        'paused_at',
        'total_paused_seconds',
        'status',
        'amount',
        'is_birthday_offer',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'game_type' => GameType::class,
            'status' => PlaySessionStatus::class,
            'player_count' => 'integer',
            'planned_duration_minutes' => 'integer',
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'ended_at' => 'datetime',
            'paused_at' => 'datetime',
            'total_paused_seconds' => 'integer',
            'amount' => 'decimal:2',
            'is_birthday_offer' => 'boolean',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pricingPackage(): BelongsTo
    {
        return $this->belongsTo(PricingPackage::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', PlaySessionStatus::Completed);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PlaySessionStatus::Active,
            PlaySessionStatus::Paused,
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === PlaySessionStatus::Active;
    }

    public function isPaused(): bool
    {
        return $this->status === PlaySessionStatus::Paused;
    }

    public function isInProgress(): bool
    {
        return $this->isActive() || $this->isPaused();
    }

    public function remainingSeconds(): int
    {
        if (! $this->ends_at) {
            return 0;
        }

        $reference = ($this->isPaused() && $this->paused_at) ? $this->paused_at : now();

        return max(0, $this->ends_at->getTimestamp() - $reference->getTimestamp());
    }

    public function pause(): void
    {
        if (! $this->isActive()) {
            return;
        }

        $this->update([
            'status' => PlaySessionStatus::Paused,
            'paused_at' => now(),
        ]);
    }

    public function resume(): void
    {
        if (! $this->isPaused() || ! $this->paused_at) {
            return;
        }

        $pausedSeconds = (int) $this->paused_at->diffInSeconds(now());

        $this->update([
            'status' => PlaySessionStatus::Active,
            'total_paused_seconds' => $this->total_paused_seconds + $pausedSeconds,
            'ends_at' => $this->ends_at?->addSeconds($pausedSeconds),
            'paused_at' => null,
        ]);
    }

    public function stop(): void
    {
        if (! $this->isInProgress()) {
            return;
        }

        $amount = $this->is_birthday_offer
            ? 0
            : ($this->pricingPackage?->price ?? 0);

        $this->update([
            'status' => PlaySessionStatus::Completed,
            'amount' => $amount,
            'ended_at' => now(),
            'paused_at' => null,
        ]);
    }

    public function reset(): void
    {
        if (! $this->isInProgress()) {
            return;
        }

        $this->update([
            'status' => PlaySessionStatus::Cancelled,
            'amount' => 0,
            'ended_at' => now(),
            'paused_at' => null,
        ]);
    }

    public static function startSession(
        Station $station,
        Customer $customer,
        GameType $gameType,
        int $playerCount,
        int $durationMinutes,
        ?PricingPackage $package = null,
        bool $isBirthdayOffer = false,
    ): self {
        $now = now();
        $endsAt = $now->copy()->addMinutes($durationMinutes);

        return self::create([
            'station_id' => $station->id,
            'customer_id' => $customer->id,
            'pricing_package_id' => $package?->id,
            'game_type' => $gameType,
            'player_count' => $playerCount,
            'planned_duration_minutes' => $durationMinutes,
            'started_at' => $now,
            'ends_at' => $endsAt,
            'status' => PlaySessionStatus::Active,
            'amount' => 0,
            'is_birthday_offer' => $isBirthdayOffer,
        ]);
    }

    public function restart(): self
    {
        $this->stop();

        return self::startSession(
            $this->station,
            $this->customer,
            $this->game_type,
            $this->player_count,
            $this->planned_duration_minutes,
            $this->pricingPackage,
            $this->is_birthday_offer,
        );
    }

    public function toTimerArray(): array
    {
        return [
            'id' => $this->id,
            'station_id' => $this->station_id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'remaining_seconds' => $this->remainingSeconds(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_paused' => $this->isPaused(),
            'customer_name' => $this->customer->name,
            'player_count' => $this->player_count,
            'game_type' => $this->game_type->label(),
            'planned_duration_minutes' => $this->planned_duration_minutes,
            'is_birthday_offer' => $this->is_birthday_offer,
            'amount' => (float) $this->amount,
            'package_label' => $this->is_birthday_offer
                ? 'Birthday Offer (1 hr 10 min free)'
                : ($this->pricingPackage?->durationLabel() ?? $this->planned_duration_minutes.' min'),
        ];
    }
}
