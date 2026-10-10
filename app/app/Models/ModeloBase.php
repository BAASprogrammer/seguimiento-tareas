<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//Se crea una clase abstracta para evitar repetir el código de los timestamps
//Todas las tablas tendrán esta clase como padre
abstract class ModeloBase extends Model
{
    //Se indica que las tablas tendrán esta columna como fecha de creación
    const CREATED_AT = 'creado_en';
    //Se indica que las tablas tendrán esta columna como fecha de actualización
    const UPDATED_AT = 'actualizado_en';
}