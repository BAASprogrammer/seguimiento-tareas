<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutenticacionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_passwords_that_do_not_match_the_frontend_policy(): void
    {
        $this->postJson('/api/registrar', [
            'nombre' => 'Usuario de prueba',
            'email' => 'usuario@example.test',
            'password' => 'password1',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertDatabaseMissing('usuarios', ['email' => 'usuario@example.test']);
    }

    public function test_registration_hashes_a_valid_password_and_returns_the_user(): void
    {
        $response = $this->postJson('/api/registrar', [
            'nombre' => 'Usuario de prueba',
            'email' => 'usuario@example.test',
            'password' => 'Seguro123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('usuario.nombre', 'Usuario de prueba')
            ->assertJsonMissingPath('usuario.password')
            ->assertJsonStructure(['token']);

        $usuario = Usuario::where('email', 'usuario@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Seguro123!', $usuario->password));
    }

    public function test_login_is_rate_limited_after_five_attempts_per_minute(): void
    {
        for ($intento = 0; $intento < 5; $intento++) {
            $this->postJson('/api/iniciar-sesion', [
                'email' => 'usuario@example.test',
                'password' => 'incorrecta',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/iniciar-sesion', [
            'email' => 'usuario@example.test',
            'password' => 'incorrecta',
        ])->assertTooManyRequests();
    }
}
