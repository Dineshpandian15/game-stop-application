<?php

namespace App\Http\Controllers;

use App\Services\RevenueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private RevenueService $revenueService,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'daily');
        $date = $request->get('date', now()->toDateString());
        $month = $request->get('month', now()->format('Y-m'));

        $daily = $this->revenueService->dailySummary(Carbon::parse($date));

        [$year, $monthNum] = explode('-', $month);
        $monthly = $this->revenueService->monthlySummary((int) $year, (int) $monthNum);

        return view('reports.index', [
            'tab' => $tab,
            'daily' => $daily,
            'monthly' => $monthly,
            'selectedDate' => $date,
            'selectedMonth' => $month,
        ]);
    }

    public function exportDaily(Request $request): StreamedResponse
    {
        $date = Carbon::parse($request->get('date', now()->toDateString()));
        $summary = $this->revenueService->dailySummary($date);
        $filename = 'game-stop-daily-'.$date->toDateString().'.csv';

        return response()->streamDownload(function () use ($summary) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Station', 'Customer', 'Phone', 'Game', 'Players', 'Amount', 'Birthday Offer']);

            foreach ($summary['sessions'] as $session) {
                fputcsv($handle, [
                    $session->ended_at?->toDateTimeString(),
                    $session->station->name,
                    $session->customer->name,
                    $session->customer->phone,
                    $session->game_type->label(),
                    $session->player_count,
                    $session->amount,
                    $session->is_birthday_offer ? 'Yes' : 'No',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Total Revenue', $summary['total_revenue']]);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
