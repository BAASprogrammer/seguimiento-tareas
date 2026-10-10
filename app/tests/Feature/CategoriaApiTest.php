<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoriaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_category_and_its_tasks(): void
    {
        $usuario = $this->crearUsuario('propietario');
        $categoria = $usuario->categorias()->create([
            'nombre' => 'Trabajo',
            'color' => '#4f46e5',
        ]);
        $usuario->tareas()->create([
            'categoria_id' => $categoria->id,
            'titulo' => 'Preparar informe',
            'descripcion' => 'Informe semanal',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->getJson("/api/categorias/{$categoria->id}")
            ->assertOk()
            ->assertJsonPath('nombre', 'Trabajo')
            ->assertJsonCount(1, 'tareas');
    }

    public function test_user_cannot_view_update_or_delete_another_users_category(): void
    {
        $propietario = $this->crearUsuario('propietario');
        $otroUsuario = $this->crearUsuario('otro');
        $categoria = $propietario->categorias()->create([
            'nombre' => 'Privada',
            'color' => '#4f46e5',
        ]);

        $this->actingAs($otroUsuario, 'sanctum')
            ->getJson("/api/categorias/{$categoria->id}")
            ->assertNotFound();

        $this->actingAs($otroUsuario, 'sanctum')
            ->putJson("/api/categorias/{$categoria->id}", [
                'nombre' => 'Modificada',
                'color' => '#ff0000',
            ])
            ->assertNotFound();

        $this->actingAs($otroUsuario, 'sanctum')
            ->deleteJson("/api/categorias/{$categoria->id}")
            ->assertNotFound();

        $this->assertSame('Privada', $categoria->fresh()->nombre);
    }

    public function test_category_color_must_be_a_six_digit_hex_color(): void
    {
        $usuario = $this->crearUsuario('propietario');

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/categorias', [
                'nombre' => 'Trabajo',
                'color' => 'red',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('color');
    }

    private function crearUsuario(string $sufijo): Usuario
    {
        return Usuario::create([
            'nombre' => "Usuario {$sufijo}",
            'email' => "{$sufijo}@example.test",
            'password' => Hash::make('password-segura'),
        ]);
    }
}
