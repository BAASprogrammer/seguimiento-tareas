<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TareaController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->tareas()->with('categoria')->orderByDesc('creado_en')->get();
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'categoria_id' => [
                'nullable',
                Rule::exists('categorias', 'id')
                    ->where(fn ($query) => $query->where('usuario_id', $request->user()->id)),
            ],
            'estado' => ['sometimes', 'required', Rule::in(Tarea::ESTADOS)],
        ]);

        $validado['estado'] = $validado['estado'] ?? 'pendiente';
        if ($validado['estado'] === 'completada') {
            $validado['fecha_completada'] = now();
        }

        return $request->user()->tareas()->create($validado)->load('categoria');
    }

    public function show(Request $request, Tarea $tarea)
    {
        $this->authorizeOwnership($request, $tarea);

        return $tarea->load('categoria');
    }

    public function update(Request $request, Tarea $tarea)
    {
        $this->authorizeOwnership($request, $tarea);

        $validado = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'categoria_id' => [
                'nullable',
                Rule::exists('categorias', 'id')
                    ->where(fn ($query) => $query->where('usuario_id', $request->user()->id)),
            ],
            'estado' => ['required', Rule::in(Tarea::ESTADOS)],
        ]);

        if ($validado['estado'] === 'completada' && $tarea->estado !== 'completada') {
            $validado['fecha_completada'] = now();
        } elseif ($validado['estado'] !== 'completada') {
            $validado['fecha_completada'] = null;
        }

        $tarea->update($validado);

        return $tarea->load('categoria');
    }

    public function destroy(Request $request, Tarea $tarea)
    {
        $this->authorizeOwnership($request, $tarea);

        $tarea->delete();
        return response()->noContent();
    }

    private function authorizeOwnership(Request $request, Tarea $tarea): void
    {
        abort_unless((int) $tarea->usuario_id === (int) $request->user()->id, 404);
    }
}
