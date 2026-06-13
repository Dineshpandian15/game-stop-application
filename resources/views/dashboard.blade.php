@extends('layouts.gamestop')

@section('title', 'Stations')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="dashboardApp()" x-init="init()">
    {{-- Today's summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 p-6">
            <div class="text-sm text-slate-400 mb-1">Today's Collection</div>
            <div class="text-3xl font-bold text-emerald-400">₹{{ number_format($todayRevenue, 0) }}</div>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 p-6">
            <div class="text-sm text-slate-400 mb-1">Sessions Today</div>
            <div class="text-3xl font-bold text-cyan-400">{{ $todaySessions }}</div>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 p-6">
            <div class="text-sm text-slate-400 mb-1">Active Stations</div>
            <div class="text-3xl font-bold text-white">
                {{ $stations->filter(fn($s) => $s->activeSession)->count() }} / {{ $stations->count() }}
            </div>
        </div>
    </div>

    {{-- Station grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($stations as $station)
            @include('stations.partials.card', ['station' => $station])
        @endforeach
    </div>

    {{-- Start session modal --}}
    @include('stations.partials.start-modal')
</div>
@endsection

@push('scripts')
<script>
    function dashboardApp() {
        return {
            showStartModal: false,
            selectedStation: null,
            packages: [],
            loadingPackages: false,
            form: {
                station_id: '',
                customer_name: '',
                customer_phone: '',
                date_of_birth: '',
                game_type: 'ps5',
                player_count: 1,
                pricing_package_id: '',
                is_birthday_offer: false,
            },
            stations: @json($stationsState),

            init() {
                this.tickTimers();
                setInterval(() => this.tickTimers(), 1000);
                setInterval(() => this.syncWithServer(), 10000);
            },

            tickTimers() {
                this.stations.forEach(station => {
                    if (station.session && station.session.status === 'active' && station.session.remaining_seconds > 0) {
                        station.session.remaining_seconds--;
                    }
                });
            },

            async syncWithServer() {
                try {
                    const response = await fetch('{{ route('api.stations.state') }}');
                    const data = await response.json();
                    this.stations = data.stations.map(s => ({
                        id: s.id,
                        name: s.name,
                        game_type: s.session ? null : null,
                        session: s.session,
                    }));
                } catch (e) {
                    console.error('Sync failed', e);
                }
            },

            openStartModal(station) {
                this.selectedStation = station;
                this.form.station_id = station.id;
                this.form.game_type = station.game_type || 'ps5';
                this.form.player_count = 1;
                this.form.pricing_package_id = '';
                this.form.is_birthday_offer = false;
                this.showStartModal = true;
                this.fetchPackages();
            },

            async fetchPackages() {
                if (this.form.is_birthday_offer) {
                    this.packages = [];
                    return;
                }
                this.loadingPackages = true;
                try {
                    const params = new URLSearchParams({
                        game_type: this.form.game_type,
                        player_count: this.form.player_count,
                    });
                    const response = await fetch(`{{ route('api.packages') }}?${params}`);
                    this.packages = await response.json();
                    if (this.packages.length && !this.packages.find(p => p.id == this.form.pricing_package_id)) {
                        this.form.pricing_package_id = this.packages[0].id;
                    }
                } finally {
                    this.loadingPackages = false;
                }
            },

            formatTime(seconds) {
                const m = Math.floor(seconds / 60);
                const s = seconds % 60;
                return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            },

            getStation(id) {
                return this.stations.find(s => s.id === id);
            },

            timerClass(session) {
                if (!session) return '';
                if (session.status === 'paused') return 'text-amber-400';
                if (session.remaining_seconds <= 300) return 'text-red-400 animate-pulse';
                return 'text-cyan-400';
            },
        };
    }
</script>
@endpush
