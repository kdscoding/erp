<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', [
            'nik' => $user->nik,
            'request_note' => 'Lupa password dan butuh reset.',
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_duplicate_reset_password_request_will_not_create_duplicate_pending_rows(): void
    {
        $user = User::factory()->create();

        $payload = [
            'nik' => $user->nik,
            'request_note' => 'Permintaan reset pertama.',
        ];

        $this->post('/forgot-password', $payload)->assertSessionHas('status');
        $this->post('/forgot-password', $payload)->assertSessionHas('status');

        $this->assertSame(1, DB::table('password_reset_requests')->where('user_id', $user->id)->where('status', 'pending')->count());
    }
}
