<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->categorias()->get();
    }

    public function show(Request $request, Categoria $categoria)
    {
        $this->authorizeOwnership($request, $categoria);

        return $categoria->load('tareas');
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $categoria = $request->user()->categorias()->create($validado);

        return $categoria;
    }

    public function update(Request $request, Categoria $categoria)
    {
        $this->authorizeOwnership($request, $categoria);

        $validado = $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $categoria->update($validado);

        return $categoria;
    }

    public function destroy(Request $request, Categoria $categoria)
    {
        $this->authorizeOwnership($request, $categoria);

        $categoria->delete();
        return response()->noContent();
    }

    private function authorizeOwnership(Request $request, Categoria $categoria): void
    {
        abort_unless((int) $categoria->usuario_id === (int) $request->user()->id, 404);
    }
}