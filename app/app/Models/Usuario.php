<?php

namespace App\Models;

use App\Models\Categoria;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // Definimos las fechas que se deben tener en cuenta para la tabla usuarios
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    // Definimos la tabla
    protected $table = 'usuarios';
    // Definimos que campos se pueden rellenar
    protected $fillable = ['nombre', 'email', 'password'];
    // Definimos los campos que no se pueden mostrar
    protected $hidden = ['password', 'remember_token'];

    // Se definen las propiedades que se deben tener en cuenta
    protected function casts(): array
    {
        return [
            'email_verificado_en' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tareas(): HasMany
    {
        // Indica que un usuario tiene muchas tareas
        return $this->hasMany(Tarea::class, 'usuario_id');
    }

    public function categorias(): HasMany
    {
        // Indica que un usuario tiene muchas categorías
        return $this->hasMany(Categoria::class, 'usuario_id');
    }
}
