<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_all_users()
    {
        // Создание тестовых данных
        User::factory()->count(3)->create();

        // Отправка GET запроса к /api/users
        $response = $this->getJson('/api/users');

        // Проверка статуса и структуры ответа
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    /** @test */
    public function it_can_create_a_new_user()
    {
        // Данные для создания пользователя
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ];

        // Отправка POST запроса к /api/register
        $response = $this->postJson('/api/register', $userData);

        // Проверка статуса и структуры ответа
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'user' => [
                         'id', 'name', 'email', 'created_at', 'updated_at'
                     ],
                     'token'
                 ]);
    }
}
