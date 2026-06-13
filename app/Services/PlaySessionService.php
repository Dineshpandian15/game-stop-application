<?php

namespace App\Services;

use App\Enums\GameType;
use App\Enums\PlaySessionStatus;
use App\Models\Customer;
use App\Models\PlaySession;
use App\Models\PricingPackage;
use App\Models\Station;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaySessionService
{
    public function start(array $data): PlaySession
    {
        return DB::transaction(function () use ($data) {
            $station = Station::query()->lockForUpdate()->findOrFail($data['station_id']);

            if ($station->activeSession()->exists()) {
                throw ValidationException::withMessages([
                    'station_id' => 'This station is already in use.',
                ]);
            }

            $customer = Customer::updateOrCreate(
                ['phone' => $data['customer_phone']],
                [
                    'name' => $data['customer_name'],
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                ]
            );

            $gameType = GameType::from($data['game_type']);
            $playerCount = (int) $data['player_count'];
            $isBirthdayOffer = (bool) ($data['is_birthday_offer'] ?? false);

            if ($isBirthdayOffer) {
                if (! $customer->date_of_birth?->isBirthday()) {
                    throw ValidationException::withMessages([
                        'is_birthday_offer' => 'Customer date of birth must match today for birthday offer.',
                    ]);
                }

                return PlaySession::startSession(
                    $station,
                    $customer,
                    $gameType,
                    $playerCount,
                    PlaySession::BIRTHDAY_DURATION_MINUTES,
                    null,
                    true,
                );
            }

            $package = PricingPackage::query()
                ->where('id', $data['pricing_package_id'])
                ->where('game_type', $gameType)
                ->where('player_count', $playerCount)
                ->where('is_active', true)
                ->firstOrFail();

            return PlaySession::startSession(
                $station,
                $customer,
                $gameType,
                $playerCount,
                $package->duration_minutes,
                $package,
                false,
            );
        });
    }

    public function pause(PlaySession $session): PlaySession
    {
        $session->pause();

        return $session->fresh(['customer', 'pricingPackage', 'station']);
    }

    public function resume(PlaySession $session): PlaySession
    {
        $session->resume();

        return $session->fresh(['customer', 'pricingPackage', 'station']);
    }

    public function stop(PlaySession $session): PlaySession
    {
        $session->stop();

        return $session->fresh(['customer', 'pricingPackage', 'station']);
    }

    public function reset(PlaySession $session): PlaySession
    {
        $session->reset();

        return $session->fresh(['customer', 'pricingPackage', 'station']);
    }

    public function restart(PlaySession $session): PlaySession
    {
        return DB::transaction(function () use ($session) {
            $station = Station::query()->lockForUpdate()->findOrFail($session->station_id);

            if ($session->isInProgress()) {
                $session->stop();
            }

            if ($station->activeSession()->where('id', '!=', $session->id)->exists()) {
                throw ValidationException::withMessages([
                    'station_id' => 'This station is already in use.',
                ]);
            }

            return $session->fresh(['customer', 'pricingPackage', 'station'])->restart();
        });
    }

    public function autoCompleteExpired(): void
    {
        PlaySession::query()
            ->where('status', PlaySessionStatus::Active)
            ->where('ends_at', '<=', now())
            ->each(function (PlaySession $session) {
                $session->stop();
            });
    }
}
