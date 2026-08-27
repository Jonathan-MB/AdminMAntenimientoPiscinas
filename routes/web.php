<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\DiarioController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\MedicionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PiscinaController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\RondaProgramadaController;
use App\Http\Controllers\SuplantacionController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

//  Acceso
Route::get('/', [AccesoController::class, 'index'])->name('login');
Route::post('/acceso', [AccesoController::class, 'iniciar'])->name('acceso.iniciar');
Route::post('/salir', [AccesoController::class, 'cerrar'])->name('acceso.cerrar');


//  Zona con sesion iniciada
Route::middleware('auth')->group(function () {

    Route::get('/panel', [PanelController::class, 'index'])->name('panel');

    //  Perfil propio: lo tiene cualquier usuario con sesion
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::patch('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::patch('/perfil/password', [PerfilController::class, 'cambiarPassword'])->name('perfil.password');

    //  Volver a la propia cuenta: no exige ser master, exige la marca en sesion
    Route::post('/volver-a-mi-cuenta', [SuplantacionController::class, 'terminar'])->name('suplantacion.terminar');

    //  Ver como otro usuario: solo el master
    Route::post('/ver-como/{usuario}', [SuplantacionController::class, 'iniciar'])
        ->middleware('rol:master')
        ->name('suplantacion.iniciar');

    //  Diario del hotel: lo ve el propio hotel, y tambien el personal de AQUALIVE
    Route::get('/diario/{hotel}', [DiarioController::class, 'index'])->name('diario.index');
    Route::get('/diario/{hotel}/dia/{fecha}', [DiarioController::class, 'dia'])->name('diario.dia');

    //  Registro de la jornada: colaborador, administrador y master
    Route::middleware('rol:master,administrador,colaborador')->group(function () {

        Route::get('/registro', [RegistroController::class, 'index'])->name('registro.index');
        Route::post('/registro', [RegistroController::class, 'store'])->name('registro.store');
        Route::get('/jornada/{jornada}', [RegistroController::class, 'show'])->name('registro.jornada');
        Route::patch('/jornada/{jornada}', [RegistroController::class, 'update'])->name('registro.jornada.update');
        Route::patch('/jornada/{jornada}/tarea/{tarea}', [RegistroController::class, 'marcarTarea'])->name('registro.tarea');

        Route::get('/jornada/{jornada}/medicion/{rondaProgramada}/{piscina}', [MedicionController::class, 'edit'])->name('registro.medicion');
        Route::post('/jornada/{jornada}/medicion/{rondaProgramada}/{piscina}', [MedicionController::class, 'store'])->name('registro.medicion.store');

    });


    //  Administracion: solo master y administrador
    Route::middleware('rol:master,administrador')->group(function () {

        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::patch('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        Route::get('/hoteles', [HotelController::class, 'index'])->name('hoteles.index');
        Route::post('/hoteles', [HotelController::class, 'store'])->name('hoteles.store');
        Route::get('/hoteles/{hotel}', [HotelController::class, 'show'])->name('hoteles.show');
        Route::patch('/hoteles/{hotel}', [HotelController::class, 'update'])->name('hoteles.update');
        Route::delete('/hoteles/{hotel}', [HotelController::class, 'destroy'])->name('hoteles.destroy');

        Route::post('/hoteles/{hotel}/piscinas', [PiscinaController::class, 'store'])->name('piscinas.store');
        Route::patch('/piscinas/{piscina}', [PiscinaController::class, 'update'])->name('piscinas.update');
        Route::delete('/piscinas/{piscina}', [PiscinaController::class, 'destroy'])->name('piscinas.destroy');

        Route::post('/hoteles/{hotel}/rondas', [RondaProgramadaController::class, 'store'])->name('rondas.store');
        Route::patch('/rondas/{rondaProgramada}', [RondaProgramadaController::class, 'update'])->name('rondas.update');
        Route::delete('/rondas/{rondaProgramada}', [RondaProgramadaController::class, 'destroy'])->name('rondas.destroy');

    });

});
