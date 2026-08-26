<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

//  Acceso
Route::get('/', [AccesoController::class, 'index'])->name('login');
Route::post('/acceso', [AccesoController::class, 'iniciar'])->name('acceso.iniciar');
Route::post('/salir', [AccesoController::class, 'cerrar'])->name('acceso.cerrar');


//  Zona con sesion iniciada
Route::middleware('auth')->group(function () {

    Route::get('/panel', [PanelController::class, 'index'])->name('panel');

    //  Administracion de usuarios: solo master y administrador
    Route::middleware('rol:master,administrador')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::patch('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

});
