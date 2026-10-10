<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Tarea;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TareaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_a_task_and_the_completion_date_is_saved(): void
    {
        $usuario = $this->crearUsuario('propietario');
        $tarea = $usuario->tareas()->create([
            'titulo' => 'Preparar informe',
            'descripcion' => 'Cerrar el informe semanal',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->putJson("/api/tareas/{$tarea->id}", [
                'titulo' => $tarea->titulo,
                'descripcion' => $tarea->descripcion,
                'categoria_id' => null,
                'estado' => 'completada',
            ])
            ->assertOk()
            ->assertJsonPath('estado', 'completada')
            ->assertJsonStructure(['fecha_completada']);

        $this->assertNotNull($tarea->fresh()->fecha_completada);
    }

    public function test_user_can_reopen_a_completed_task_and_clear_its_completion_date(): void
    {
        $usuario = $this->crearUsuario('propietario');
        $tarea = $usuario->tareas()->create([
            'titulo' => 'Preparar informe',
            'descripcion' => 'Cerrar el informe semanal',
            'estado' => 'completada',
            'fecha_completada' => now(),
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->putJson("/api/tareas/{$tarea->id}", [
                'titulo' => $tarea->titulo,
                'descripcion' => $tarea->descripcion,
                'categoria_id' => null,
                'estado' => 'pendiente',
            ])
            ->assertOk()
            ->assertJsonPath('estado', 'pendiente')
            ->assertJsonPath('fecha_completada', null);

        $this->assertNull($tarea->fresh()->fecha_completada);
    }

    public function test_user_cannot_access_another_users_task(): void
    {
        $propietario = $this->crearUsuario('propietario');
        $otroUsuario = $this->crearUsuario('otro');
        $tarea = $propietario->tareas()->create([
            'titulo' => 'Privada',
            'descripcion' => 'No debe ser accesible',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($otroUsuario, 'sanctum')
            ->getJson("/api/tareas/{$tarea->id}")
            ->assertNotFound();

        $this->actingAs($otroUsuario, 'sanctum')
            ->putJson("/api/tareas/{$tarea->id}", [
                'titulo' => 'Modificada',
                'descripcion' => 'Intento de modificación',
                'estado' => 'completada',
            ])
            ->assertNotFound();

        $this->actingAs($otroUsuario, 'sanctum')
            ->deleteJson("/api/tareas/{$tarea->id}")
            ->assertNotFound();

        $this->assertSame('Privada', $tarea->fresh()->titulo);
    }

    public function test_user_cannot_assign_another_users_category_to_a_task(): void
    {
        $usuario = $this->crearUsuario('propietario');
        $otroUsuario = $this->crearUsuario('otro');
        $categoria = $otroUsuario->categorias()->create([
            'nombre' => 'Privada',
            'color' => '#4f46e5',
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/tareas', [
                'titulo' => 'Tarea',
                'descripcion' => 'Descripción',
                'categoria_id' => $categoria->id,
                'estado' => 'pendiente',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('categoria_id');

        $this->assertSame(0, Tarea::count());
    }

    public function test_task_list_uses_the_custom_creation_timestamp(): void
    {
        $usuario = $this->crearUsuario('propietario');
        $usuario->tareas()->create([
            'titulo' => 'Tarea reciente',
            'descripcion' => 'Descripción',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/tareas')
            ->assertOk()
            ->assertJsonCount(1);
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
