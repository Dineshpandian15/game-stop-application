<?php

namespace App\Services;

use App\Models\PlaySession;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueService
{
    public function todaySummary(): array
    {
        return $this->dailySummary(now());
    }

    public function dailySummary(Carbon $date): array
    {
        $sessions = PlaySession::query()
            ->completed()
            ->whereDate('ended_at', $date)
            ->with(['customer', 'station', 'pricingPackage'])
            ->orderByDesc('ended_at')
            ->get();

        return [
            'date' => $date->toDateString(),
            'total_sessions' => $sessions->count(),
            'total_revenue' => $sessions->sum('amount'),
            'birthday_sessions' => $sessions->where('is_birthday_offer', true)->count(),
            'sessions' => $sessions,
        ];
    }

    public function monthlySummary(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $sessions = PlaySession::query()
            ->completed()
            ->whereBetween('ended_at', [$start, $end])
            ->get();

        $dailyBreakdown = $sessions
            ->groupBy(fn (PlaySession $session) => $session->ended_at->toDateString())
            ->map(fn (Collection $daySessions, string $date) => [
                'date' => $date,
                'sessions' => $daySessions->count(),
                'revenue' => $daySessions->sum('amount'),
            ])
            ->sortKeysDesc()
            ->values();

        return [
            'year' => $year,
            'month' => $month,
            'month_label' => $start->format('F Y'),
            'total_sessions' => $sessions->count(),
            'total_revenue' => $sessions->sum('amount'),
            'birthday_sessions' => $sessions->where('is_birthday_offer', true)->count(),
            'daily_breakdown' => $dailyBreakdown,
            'sessions' => $sessions->sortByDesc('ended_at')->values(),
        ];
    }
}
