@php
    $session = $station->activeSession;
    $gameTypeLabel = $station->game_type->label();
@endphp

<div class="rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-800/80 to-slate-900 overflow-hidden shadow-xl"
     x-data="{ stationId: {{ $station->id }} }">
    <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold text-white">{{ $station->name }}</h3>
            <p class="text-sm text-slate-400">{{ $gameTypeLabel }}</p>
        </div>
        <template x-if="!getStation({{ $station->id }})?.session">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                Available
            </span>
        </template>
        <template x-if="getStation({{ $station->id }})?.session?.status === 'active'">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">
                Playing
            </span>
        </template>
        <template x-if="getStation({{ $station->id }})?.session?.status === 'paused'">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                Paused
            </span>
        </template>
    </div>

    <div class="p-6">
        {{-- Available state --}}
        <template x-if="!getStation({{ $station->id }})?.session">
            <div class="text-center py-8">
                <div class="text-6xl mb-4 opacity-30">🎮</div>
                <p class="text-slate-400 mb-6">Station ready — start a new session</p>
                <button type="button"
                        @click="openStartModal({ id: {{ $station->id }}, name: '{{ $station->name }}', game_type: '{{ $station->game_type->value }}' })"
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:from-cyan-400 hover:to-blue-500 transition shadow-lg shadow-cyan-500/25">
                    Start Session
                </button>
            </div>
        </template>

        {{-- Active / Paused state --}}
        <template x-if="getStation({{ $station->id }})?.session">
            <div>
                <div class="text-center mb-6">
                    <div class="text-5xl sm:text-6xl font-mono font-bold tracking-wider"
                         :class="timerClass(getStation({{ $station->id }}).session)"
                         x-text="formatTime(getStation({{ $station->id }}).session.remaining_seconds)">
                    </div>
                    <p class="text-slate-400 mt-2 text-sm" x-show="getStation({{ $station->id }}).session.status === 'paused'">
                        Timer paused
                    </p>
                </div>

                <div class="space-y-2 text-sm mb-6 bg-slate-800/50 rounded-xl p-4">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Customer</span>
                        <span class="font-medium" x-text="getStation({{ $station->id }}).session.customer_name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Players</span>
                        <span x-text="getStation({{ $station->id }}).session.player_count"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Package</span>
                        <span x-text="getStation({{ $station->id }}).session.package_label"></span>
                    </div>
                    <template x-if="getStation({{ $station->id }}).session.is_birthday_offer">
                        <div class="text-center text-amber-400 font-medium pt-1">🎂 Birthday Offer</div>
                    </template>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <template x-if="getStation({{ $station->id }}).session.status === 'active'">
                        <form method="POST" x-bind:action="'/sessions/' + getStation({{ $station->id }}).session.id + '/pause'">
                            @csrf
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-amber-500/20 text-amber-400 border border-amber-500/30 text-sm font-medium hover:bg-amber-500/30 transition">
                                Pause
                            </button>
                        </form>
                    </template>

                    <template x-if="getStation({{ $station->id }}).session.status === 'paused'">
                        <form method="POST" x-bind:action="'/sessions/' + getStation({{ $station->id }}).session.id + '/resume'">
                            @csrf
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 text-sm font-medium hover:bg-cyan-500/30 transition">
                                Resume
                            </button>
                        </form>
                    </template>

                    <form method="POST" x-bind:action="'/sessions/' + getStation({{ $station->id }}).session.id + '/stop'">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-sm font-medium hover:bg-emerald-500/30 transition"
                                onclick="return confirm('End session and collect payment?')">
                            Stop
                        </button>
                    </form>

                    <form method="POST" x-bind:action="'/sessions/' + getStation({{ $station->id }}).session.id + '/reset'">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-500/20 text-red-400 border border-red-500/30 text-sm font-medium hover:bg-red-500/30 transition"
                                onclick="return confirm('Reset session without charge?')">
                            Reset
                        </button>
                    </form>

                    <form method="POST" x-bind:action="'/sessions/' + getStation({{ $station->id }}).session.id + '/restart'">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 rounded-lg bg-violet-500/20 text-violet-400 border border-violet-500/30 text-sm font-medium hover:bg-violet-500/30 transition"
                                onclick="return confirm('Restart session with same customer and package?')">
                            Restart
                        </button>
                    </form>
                </div>
            </div>
        </template>
    </div>
</div>
