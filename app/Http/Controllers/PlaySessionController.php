<?php

namespace App\Http\Controllers;

use App\Enums\GameType;
use App\Http\Requests\StorePlaySessionRequest;
use App\Models\PlaySession;
use App\Models\PricingPackage;
use App\Models\Station;
use App\Services\PlaySessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlaySessionController extends Controller
{
    public function __construct(
        private PlaySessionService $playSessionService,
    ) {}

    public function store(StorePlaySessionRequest $request): RedirectResponse
    {
        $this->playSessionService->start($request->validated());

        return redirect()->route('dashboard')->with('success', 'Session started successfully.');
    }

    public function pause(PlaySession $playSession): RedirectResponse
    {
        $this->playSessionService->pause($playSession);

        return back()->with('success', 'Session paused.');
    }

    public function resume(PlaySession $playSession): RedirectResponse
    {
        $this->playSessionService->resume($playSession);

        return back()->with('success', 'Session resumed.');
    }

    public function stop(PlaySession $playSession): RedirectResponse
    {
        $this->playSessionService->stop($playSession);

        return back()->with('success', 'Session ended. Amount collected: ₹'.$playSession->fresh()->amount);
    }

    public function reset(PlaySession $playSession): RedirectResponse
    {
        $this->playSessionService->reset($playSession);

        return back()->with('success', 'Session reset. Station is now available.');
    }

    public function restart(PlaySession $playSession): RedirectResponse
    {
        $this->playSessionService->restart($playSession);

        return back()->with('success', 'Session restarted.');
    }

    public function packages(Request $request): JsonResponse
    {
        $request->validate([
            'game_type' => ['required', 'in:'.implode(',', array_column(GameType::cases(), 'value'))],
            'player_count' => ['required', 'integer', 'min:1', 'max:4'],
        ]);

        $packages = PricingPackage::query()
            ->where('game_type', $request->game_type)
            ->where('player_count', $request->player_count)
            ->where('is_active', true)
            ->orderBy('duration_minutes')
            ->get()
            ->map(fn (PricingPackage $package) => [
                'id' => $package->id,
                'duration_minutes' => $package->duration_minutes,
                'duration_label' => $package->durationLabel(),
                'price' => (float) $package->price,
                'price_label' => '₹'.number_format($package->price, 0),
            ]);

        return response()->json($packages);
    }

    public function stationsState(): JsonResponse
    {
        $this->playSessionService->autoCompleteExpired();

        $stations = Station::query()
            ->with(['activeSession.customer', 'activeSession.pricingPackage'])
            ->orderBy('id')
            ->get()
            ->map(function (Station $station) {
                $session = $station->activeSession;

                return [
                    'id' => $station->id,
                    'name' => $station->name,
                    'game_type' => $station->game_type->label(),
                    'is_available' => ! $session,
                    'session' => $session?->toTimerArray(),
                ];
            });

        return response()->json(['stations' => $stations]);
    }
}
