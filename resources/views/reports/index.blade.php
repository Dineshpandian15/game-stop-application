@extends('layouts.gamestop')

@section('title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6">Revenue Reports</h1>

    <div class="flex gap-2 mb-6">
        <a href="{{ route('reports.index', ['tab' => 'daily', 'date' => $selectedDate, 'month' => $selectedMonth]) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab === 'daily' ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800' }}">
            Daily
        </a>
        <a href="{{ route('reports.index', ['tab' => 'monthly', 'date' => $selectedDate, 'month' => $selectedMonth]) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab === 'monthly' ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800' }}">
            Monthly
        </a>
    </div>

    @if ($tab === 'daily')
        <div class="rounded-2xl border border-slate-700 bg-slate-900 p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end mb-6">
                <input type="hidden" name="tab" value="daily">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Date</label>
                    <input type="date" name="date" value="{{ $selectedDate }}"
                           class="rounded-lg bg-slate-800 border-slate-600 text-white">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 hover:bg-cyan-500/30">
                    View
                </button>
                <a href="{{ route('reports.export.daily', ['date' => $selectedDate]) }}"
                   class="px-4 py-2 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800">
                    Export CSV
                </a>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="rounded-xl bg-slate-800 p-4">
                    <div class="text-sm text-slate-400">Total Revenue</div>
                    <div class="text-2xl font-bold text-emerald-400">₹{{ number_format($daily['total_revenue'], 0) }}</div>
                </div>
                <div class="rounded-xl bg-slate-800 p-4">
                    <div class="text-sm text-slate-400">Sessions</div>
                    <div class="text-2xl font-bold text-cyan-400">{{ $daily['total_sessions'] }}</div>
                </div>
                <div class="rounded-xl bg-slate-800 p-4">
                    <div class="text-sm text-slate-400">Birthday Offers</div>
                    <div class="text-2xl font-bold text-amber-400">{{ $daily['birthday_sessions'] }}</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-700">
                            <th class="text-left py-3 px-2">Time</th>
                            <th class="text-left py-3 px-2">Station</th>
                            <th class="text-left py-3 px-2">Customer</th>
                            <th class="text-left py-3 px-2">Game</th>
                            <th class="text-right py-3 px-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daily['sessions'] as $session)
                            <tr class="border-b border-slate-800 hover:bg-slate-800/50">
                                <td class="py-3 px-2 text-slate-300">{{ $session->ended_at?->format('H:i') }}</td>
                                <td class="py-3 px-2">{{ $session->station->name }}</td>
                                <td class="py-3 px-2">{{ $session->customer->name }}</td>
                                <td class="py-3 px-2">{{ $session->game_type->label() }} ({{ $session->player_count }}p)</td>
                                <td class="py-3 px-2 text-right font-medium text-emerald-400">
                                    @if ($session->is_birthday_offer)
                                        <span class="text-amber-400">Free</span>
                                    @else
                                        ₹{{ number_format($session->amount, 0) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">No sessions on this date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-slate-700 bg-slate-900 p-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end mb-6">
                <input type="hidden" name="tab" value="monthly">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Month</label>
                    <input type="month" name="month" value="{{ $selectedMonth }}"
                           class="rounded-lg bg-slate-800 border-slate-600 text-white">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 hover:bg-cyan-500/30">
                    View
                </button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="rounded-xl bg-slate-800 p-4">
                    <div class="text-sm text-slate-400">{{ $monthly['month_label'] }} Revenue</div>
                    <div class="text-2xl font-bold text-emerald-400">₹{{ number_format($monthly['total_revenue'], 0) }}</div>
                </div>
                <div class="rounded-xl bg-slate-800 p-4">
                    <div class="text-sm text-slate-400">Total Sessions</div>
                    <div class="text-2xl font-bold text-cyan-400">{{ $monthly['total_sessions'] }}</div>
                </div>
                <div class="rounded-xl bg-slate-800 p-4">
                    <div class="text-sm text-slate-400">Birthday Offers</div>
                    <div class="text-2xl font-bold text-amber-400">{{ $monthly['birthday_sessions'] }}</div>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-white mb-4">Daily Breakdown</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-700">
                            <th class="text-left py-3 px-2">Date</th>
                            <th class="text-right py-3 px-2">Sessions</th>
                            <th class="text-right py-3 px-2">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monthly['daily_breakdown'] as $day)
                            <tr class="border-b border-slate-800 hover:bg-slate-800/50">
                                <td class="py-3 px-2">
                                    <a href="{{ route('reports.index', ['tab' => 'daily', 'date' => $day['date']]) }}"
                                       class="text-cyan-400 hover:text-cyan-300">
                                        {{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}
                                    </a>
                                </td>
                                <td class="py-3 px-2 text-right">{{ $day['sessions'] }}</td>
                                <td class="py-3 px-2 text-right font-medium text-emerald-400">₹{{ number_format($day['revenue'], 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-500">No sessions this month.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
