<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Categoria extends ModeloBase
{
    //Se indica el nombre de la tabla
    protected $table = "categorias";

    //Se indican los campos que se pueden asignar de forma masiva
    protected $fillable = [
        'nombre',
        'color',
    ];
    //Relación con Tarea
    public function tareas(): HasMany
    {
        //Indica que una categoría puede tener muchas tareas
        return $this->hasMany(Tarea::class);
    }
    //Relación con Usuario
    public function usuario(): BelongsTo
    {
        //Indica que una categoría pertenece a un usuario
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
