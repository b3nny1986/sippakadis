<x-layout title="Notifikasi">
    <div class="mx-auto max-w-3xl space-y-4">
        @forelse ($notifikasi as $n)
            <div class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm {{ $n->is_read ? 'opacity-75' : 'border-brand-200' }}">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <x-badge :value="$n->tipe">{{ $n->tipe }}</x-badge>
                        @if (! $n->is_read)
                            <span class="h-2 w-2 rounded-full bg-brand-600" title="Belum dibaca"></span>
                        @endif
                    </div>
                    <p class="mt-1 font-semibold text-slate-800">{{ $n->judul }}</p>
                    <p class="text-sm text-slate-600">{{ $n->pesan }}</p>
                    @if ($n->kendaraan)
                        <a href="{{ route('kendaraan.show', $n->kendaraan) }}" class="mt-1 inline-block text-xs font-semibold text-brand-600 hover:underline">
                            {{ $n->kendaraan->nopol }}
                        </a>
                    @endif
                    <p class="mt-1 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
                </div>
                @if (! $n->is_read)
                    <form method="POST" action="{{ route('notifikasi.read', $n) }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                            Tandai dibaca
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-400 shadow-sm">
                Tidak ada notifikasi.
            </div>
        @endforelse

        <div>{{ $notifikasi->links() }}</div>
    </div>
</x-layout>
