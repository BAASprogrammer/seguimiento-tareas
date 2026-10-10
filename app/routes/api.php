<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TareaController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\AutenticacionController;

Route::post('/registrar', [AutenticacionController::class, 'registrar'])->middleware('throttle:5,1');
Route::post('/iniciar-sesion', [AutenticacionController::class, 'iniciarSesion'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cerrar-sesion', [AutenticacionController::class, 'cerrarSesion']);
    Route::apiResource('tareas', TareaController::class);
    Route::apiResource('categorias', CategoriaController::class);
});