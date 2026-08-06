@props(['title' => 'Dashboard'])

@php
    $user = auth()->user();
    $isAdmin = $user?->role?->slug === 'admin';
    $belumDibaca = $belumDibaca ?? \App\Models\Notifikasi::query()
        ->when($user?->role?->slug === 'opd', fn ($q) => $q->forOpd($user->opd_id))
        ->unread()
        ->count();
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - SIPPAKADIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800" x-data="{ sidebar: false }">

    <div class="flex min-h-full">
        {{-- Sidebar --}}
        <div
            class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full bg-brand-900 text-brand-50 transition-transform lg:static lg:translate-x-0"
            :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            x-cloak
        >
            <div class="flex h-16 items-center gap-3 border-b border-brand-800 px-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent-400 text-lg font-bold text-brand-900">
                    S
                </div>
                <div class="leading-tight">
                    <p class="font-bold">SIPPAKADIS</p>
                    <p class="text-[11px] text-brand-300">Pajak Kendaraan Dinas</p>
                </div>
            </div>

            <nav class="space-y-1 px-3 py-4 text-sm font-medium">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="grid">
                    Dashboard
                </x-nav-link>

                <x-nav-link :href="route('kendaraan.index')" :active="request()->routeIs('kendaraan.*')" icon="car">
                    Kendaraan
                </x-nav-link>

                @if ($isAdmin)
                    <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-brand-400">Administrasi</p>
                    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="users">Pengguna</x-nav-link>
                    <x-nav-link :href="route('admin.opd.index')" :active="request()->routeIs('admin.opd.*')" icon="building">OPD</x-nav-link>
                    <x-nav-link :href="route('admin.penetapan.index')" :active="request()->routeIs('admin.penetapan.*')" icon="document">Penetapan</x-nav-link>
                    <x-nav-link :href="route('admin.perubahan-status.index')" :active="request()->routeIs('admin.perubahan-status.*')" icon="refresh">Perubahan Status</x-nav-link>
                    <x-nav-link :href="route('admin.sinkronisasi.index')" :active="request()->routeIs('admin.sinkronisasi.*')" icon="sync">Sinkronisasi</x-nav-link>
                    <x-nav-link :href="route('admin.laporan.index')" :active="request()->routeIs('admin.laporan.*')" icon="report">Laporan</x-nav-link>
                    <x-nav-link :href="route('admin.audit-log.index')" :active="request()->routeIs('admin.audit-log.*')" icon="shield">Audit Log</x-nav-link>
                @else
                    <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-brand-400">Layanan OPD</p>
                    <x-nav-link :href="route('opd.pengajuan.index')" :active="request()->routeIs('opd.pengajuan.*')" icon="document">Pengajuan</x-nav-link>
                    <x-nav-link :href="route('opd.perubahan-status.create')" :active="request()->routeIs('opd.perubahan-status.*')" icon="refresh">Perubahan Status</x-nav-link>
                @endif
            </nav>

            <div class="absolute inset-x-0 bottom-0 border-t border-brand-800 p-4">
                <div class="flex items-center justify-between gap-2 text-sm">
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ $user?->name }}</p>
                        <p class="truncate text-[11px] text-brand-300">{{ $user?->opd?->nama ?? $user?->role?->nama }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg p-2 text-brand-300 transition hover:bg-brand-800 hover:text-white" title="Keluar">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Overlay mobile --}}
        <div
            class="fixed inset-0 z-30 bg-brand-900/50 lg:hidden"
            x-show="sidebar"
            x-cloak
            @click="sidebar = false"
        ></div>

        {{-- Konten --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" @click="sidebar = true" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <h1 class="truncate text-lg font-bold text-slate-900">{{ $title }}</h1>

                <div class="ml-auto flex items-center gap-2">
                    <a href="{{ route('notifikasi.index') }}" class="relative rounded-lg p-2 text-slate-500 transition hover:bg-slate-100" title="Notifikasi">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if ($belumDibaca > 0)
                            <span class="absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">
                                {{ $belumDibaca }}
                            </span>
                        @endif
                    </a>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                {{ $slot }}
            </main>

            <footer class="border-t border-slate-200 bg-white px-6 py-3 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} SIPPAKADIS &middot; Sistem Pemantauan Pajak Kendaraan Dinas
            </footer>
        </div>
    </div>

    {{-- Toast --}}
    <div x-data class="pointer-events-none fixed top-4 right-4 z-50 space-y-2">
        <template x-for="item in $store.toast.items" :key="item.id">
            <div
                class="pointer-events-auto flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg"
                :class="item.type === 'error' ? 'border-red-200 bg-red-50 text-red-700' : (item.type === 'info' ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700')"
                x-text="item.message"
            ></div>
        </template>
    </div>

    @php
        $flashes = array_filter([
            'success' => session('status'),
            'error' => session('error'),
            'info' => session('info'),
        ]);
    @endphp

    @if ($flashes)
        <script>
            document.addEventListener('alpine:init', () => {
                const toasts = @json($flashes);
                for (const [type, message] of Object.entries(toasts)) {
                    window.Alpine.store('toast').show(message, type === 'error' ? 'error' : (type === 'info' ? 'info' : 'success'));
                }
            });
        </script>
    @endif

    <style>[x-cloak] { display: none !important; }</style>

    @stack('scripts')
</body>
</html>
