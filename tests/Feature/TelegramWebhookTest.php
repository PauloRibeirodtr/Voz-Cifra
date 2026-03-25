<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_webhook_start_token_binds_chat_id_to_user()
    {
        // cria usuário
        $user = User::factory()->create([
            'email' => 'tguser@example.com',
        ]);

        // gera token
        $token = $user->generateTelegramToken();

        $payload = [
            'message' => [
                'text' => '/start ' . $token,
                'chat' => ['id' => 987654321],
            ],
        ];

        $this->postJson('/telegram/webhook', $payload)
             ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'telegram_chat_id' => '987654321',
        ]);
    }
}
