<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:50'],
            'request_note' => ['required', 'string', 'max:500'],
        ]);

        $user = User::where('nik', trim($validated['nik']))->where('is_active', true)->first();

        if ($user) {
            $alreadyPending = DB::table('password_reset_requests')
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->exists();

            if (! $alreadyPending) {
                DB::table('password_reset_requests')->insert([
                    'user_id' => $user->id,
                    'request_note' => $validated['request_note'],
                    'status' => 'pending',
                    'requested_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('status', 'Permintaan reset password sudah dicatat dan akan diproses administrator.');
    }
}
