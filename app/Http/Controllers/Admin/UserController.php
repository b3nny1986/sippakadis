<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Opd;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('role', 'opd')
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', $request->integer('role_id')))
            ->when($request->filled('opd_id'), fn ($q) => $q->where('opd_id', $request->integer('opd_id')))
            ->when($request->filled('cari'), fn ($q) => $q->where('name', 'ilike', '%' . $request->string('cari') . '%')
                ->orWhere('email', 'ilike', '%' . $request->string('cari') . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(),
            'daftarOpd' => Opd::orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        $opdRoleId = (int) Role::query()->where('slug', 'opd')->value('id');

        return view('admin.users.create', [
            'user' => new User,
            'roles' => Role::orderBy('id')->get(),
            'opds' => Opd::orderBy('nama')->get(),
            'opdRoleId' => $opdRoleId,
        ]);
    }

    public function store(UserRequest $request, AuditLogService $audit): RedirectResponse
    {
        // Password di-hash otomatis oleh model cast 'hashed'.
        $user = User::create($request->safe()->except('password') + [
            'password' => $request->input('password'),
            'is_active' => true,
        ]);

        $audit->log('user.create', 'User', $user->id, "Tambah user {$user->email}");

        return redirect()->route('admin.users.index')
            ->with('status', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $opdRoleId = (int) Role::query()->where('slug', 'opd')->value('id');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
            'opds' => Opd::orderBy('nama')->get(),
            'opdRoleId' => $opdRoleId,
        ]);
    }

    public function update(UserRequest $request, User $user, AuditLogService $audit): RedirectResponse
    {
        $data = $request->safe()->except(['password']);

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        $user->update($data);

        $audit->log('user.update', 'User', $user->id, "Update user {$user->email}");

        return redirect()->route('admin.users.index')
            ->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user, AuditLogService $audit): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');

        $audit->log('user.delete', 'User', $user->id, "Hapus user {$user->email}");

        $user->delete();

        return back()->with('status', 'User berhasil dihapus.');
    }
}
