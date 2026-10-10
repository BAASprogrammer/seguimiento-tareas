<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Tarea extends ModeloBase
{
    public const ESTADOS = ['pendiente', 'en progreso', 'completada'];

    //Tabla a la que pertenece el modelo
    protected $table = "tareas";

    //Campos que se pueden asignar de forma masiva
    protected $fillable = [
        'titulo',
        'descripcion',
        'categoria_id',
        'estado',
        'fecha_completada',
    ];

    protected function casts(): array
    {
        return [
            'fecha_completada' => 'datetime',
        ];
    }

    //Relación con Categoria
    public function categoria(): BelongsTo
    {
        //Indica que una tarea pertenece a una categoría
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    //Relación con Usuario
    public function usuario(): BelongsTo
    {
        //Indica que una tarea pertenece a un usuario
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
