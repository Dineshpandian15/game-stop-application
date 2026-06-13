<div x-show="showStartModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
     @keydown.escape.window="showStartModal = false">
    <div class="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl" @click.outside="showStartModal = false">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">
                Start Session — <span x-text="selectedStation?.name"></span>
            </h3>
            <button @click="showStartModal = false" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form method="POST" action="{{ route('sessions.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="station_id" x-model="form.station_id">

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Customer Name</label>
                <input type="text" name="customer_name" x-model="form.customer_name" required
                       class="w-full rounded-lg bg-slate-800 border-slate-600 text-white focus:border-cyan-500 focus:ring-cyan-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Phone Number</label>
                <input type="tel" name="customer_phone" x-model="form.customer_phone" required
                       class="w-full rounded-lg bg-slate-800 border-slate-600 text-white focus:border-cyan-500 focus:ring-cyan-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Date of Birth (optional)</label>
                <input type="date" name="date_of_birth" x-model="form.date_of_birth"
                       class="w-full rounded-lg bg-slate-800 border-slate-600 text-white focus:border-cyan-500 focus:ring-cyan-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Game Type</label>
                    <select name="game_type" x-model="form.game_type" @change="fetchPackages()"
                            class="w-full rounded-lg bg-slate-800 border-slate-600 text-white focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="ps5">PS5</option>
                        <option value="driving_simulator">Driving Simulator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Players</label>
                    <select name="player_count" x-model="form.player_count" @change="fetchPackages()"
                            class="w-full rounded-lg bg-slate-800 border-slate-600 text-white focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="1">1 Player</option>
                        <option value="2">2 Players</option>
                        <option value="3" x-show="form.game_type === 'ps5'">3 Players</option>
                        <option value="4" x-show="form.game_type === 'ps5'">4 Players</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_birthday_offer" id="is_birthday_offer" value="1"
                       x-model="form.is_birthday_offer" @change="fetchPackages()"
                       class="rounded bg-slate-800 border-slate-600 text-cyan-500 focus:ring-cyan-500">
                <label for="is_birthday_offer" class="text-sm text-slate-300">
                    Birthday Offer (1 hr 10 min free — valid ID required)
                </label>
            </div>

            <template x-if="!form.is_birthday_offer">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Package</label>
                    <select name="pricing_package_id" x-model="form.pricing_package_id" required
                            class="w-full rounded-lg bg-slate-800 border-slate-600 text-white focus:border-cyan-500 focus:ring-cyan-500">
                        <template x-for="pkg in packages" :key="pkg.id">
                            <option :value="pkg.id" x-text="`${pkg.duration_label} — ${pkg.price_label}`"></option>
                        </template>
                    </select>
                    <p x-show="loadingPackages" class="text-xs text-slate-500 mt-1">Loading packages...</p>
                </div>
            </template>

            <template x-if="form.is_birthday_offer">
                <div class="rounded-lg bg-amber-500/10 border border-amber-500/30 p-3 text-sm text-amber-300">
                    Birthday session: 1 hour 10 minutes free. Customer DOB must match today.
                </div>
            </template>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="showStartModal = false"
                        class="flex-1 px-4 py-2 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:from-cyan-400 hover:to-blue-500 transition">
                    Start Timer
                </button>
            </div>
        </form>
    </div>
</div>
