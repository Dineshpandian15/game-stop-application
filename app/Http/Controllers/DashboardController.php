<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Services\PlaySessionService;
use App\Services\RevenueService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private PlaySessionService $playSessionService,
        private RevenueService $revenueService,
    ) {}

    public function index(): View
    {
        $this->playSessionService->autoCompleteExpired();

        $stations = Station::query()
            ->with(['activeSession.customer', 'activeSession.pricingPackage'])
            ->orderBy('id')
            ->get();

        $today = $this->revenueService->todaySummary();

        return view('dashboard', [
            'stations' => $stations,
            'todayRevenue' => $today['total_revenue'],
            'todaySessions' => $today['total_sessions'],
        ]);
    }
}
