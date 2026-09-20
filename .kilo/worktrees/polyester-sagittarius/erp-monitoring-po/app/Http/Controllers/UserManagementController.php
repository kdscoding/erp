<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const ROLE_SLUGS = ['administrator', 'staff', 'supervisor'];

    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('settings.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = $this->roles();

        return view('settings.users.create', compact('roles'));
    }

    public function edit(User $user): View
    {
        $user->load('roles');
        $roles = $this->roles();
        $pendingResetRequest = DB::table('password_reset_requests')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('requested_at')
            ->first();

        return view('settings.users.edit', compact('user', 'roles', 'pendingResetRequest'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:50', Rule::unique('users', 'nik')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role_slug' => ['required', Rule::in(self::ROLE_SLUGS)],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'nik' => trim($validated['nik']),
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            $roleId = Role::where('slug', $validated['role_slug'])->value('id');
            $user->roles()->sync([$roleId]);
        });

        return redirect()->route('users.index')->with('success', 'User baru berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:50', Rule::unique('users', 'nik')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_slug' => ['required', Rule::in(self::ROLE_SLUGS)],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'name' => $validated['name'],
                'nik' => trim($validated['nik']),
                'email' => strtolower($validated['email']),
            ]);

            $roleId = Role::where('slug', $validated['role_slug'])->value('id');
            $user->roles()->sync([$roleId]);
        });

        return redirect()->route('users.edit', $user)->with('success', 'Data user berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $pendingRequest = DB::table('password_reset_requests')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('requested_at')
            ->first();

        if (! $pendingRequest) {
            return redirect()->route('users.edit', $user)->with('error', 'Tidak ada request reset password yang pending untuk user ini.');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'admin_note' => ['required', 'string', 'max:500'],
        ], [
            'admin_note.required' => 'Catatan tindak lanjut admin wajib diisi.',
        ]);

        DB::transaction(function () use ($user, $validated, $pendingRequest, $request) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            DB::table('password_reset_requests')->where('id', $pendingRequest->id)->update([
                'status' => 'processed',
                'processed_by' => optional($request->user())->id,
                'processed_at' => now(),
                'admin_note' => $validated['admin_note'],
                'updated_at' => now(),
            ]);

            DB::table('audit_logs')->insert([
                'module' => 'users',
                'record_id' => $user->id,
                'action' => 'password_reset_by_admin',
                'old_values' => null,
                'new_values' => json_encode([
                    'password_reset_request_id' => $pendingRequest->id,
                    'admin_note' => $validated['admin_note'],
                    'reset_by' => optional($request->user())->id,
                ]),
                'user_id' => optional($request->user())->id,
                'ip_address' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('users.edit', $user)->with('success', 'Password user berhasil direset sesuai permintaan user.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return redirect()->route('users.edit', $user)->with(
            'success',
            $user->is_active ? 'User berhasil diaktifkan kembali.' : 'User berhasil dinonaktifkan.'
        );
    }

    private function roles()
    {
        return Role::query()
            ->whereIn('slug', self::ROLE_SLUGS)
            ->orderBy('id')
            ->get();
    }
}
