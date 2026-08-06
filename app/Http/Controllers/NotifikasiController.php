<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Notifikasi::query()
            ->with('kendaraan', 'opd')
            ->when($user->role?->slug === 'opd', fn ($q) => $q->forOpd($user->opd_id));

        return view('notifikasi.index', [
            'notifikasi' => $query->latest()->paginate(20)->withQueryString(),
            'belumDibaca' => $query->clone()->unread()->count(),
        ]);
    }

    public function markRead(Notifikasi $notifikasi): RedirectResponse
    {
        $this->authorizeNotifikasi($notifikasi);

        $notifikasi->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back();
    }

    private function authorizeNotifikasi(Notifikasi $notifikasi): void
    {
        $user = auth()->user();

        abort_if(
            $user->role?->slug === 'opd'
            && $notifikasi->opd_id !== null
            && $notifikasi->opd_id !== $user->opd_id,
            403
        );
    }
}
