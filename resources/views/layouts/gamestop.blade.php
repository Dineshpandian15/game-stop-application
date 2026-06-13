<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Game Stop') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <nav class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center font-bold text-white shadow-lg shadow-cyan-500/20">
                                GS
                            </div>
                            <div>
                                <div class="font-bold text-lg tracking-tight">Game Stop</div>
                                <div class="text-xs text-slate-400">Play Station Shop</div>
                            </div>
                        </a>
                        <div class="hidden sm:flex gap-1">
                            <a href="{{ route('dashboard') }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Stations
                            </a>
                            <a href="{{ route('reports.index') }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('reports.*') ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                                Reports
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="hidden sm:inline text-sm text-slate-400">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm px-4 py-2 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        @if (session('success'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-slate-800 py-4 text-center text-sm text-slate-500">
            Game Stop &middot; Coimbatore &middot;
            <a href="https://instagram.com/gamestop_cbe" target="_blank" class="text-cyan-400 hover:text-cyan-300">@gamestop_cbe</a>
        </footer>
    </div>
    @stack('scripts')
</body>
</html>
