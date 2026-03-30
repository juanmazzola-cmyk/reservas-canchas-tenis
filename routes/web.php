<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MercadoPagoController;

Route::get('/login', \App\Livewire\Login::class)->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/registro', \App\Livewire\Registro::class)->name('registro')->middleware('guest');
Route::get('/olvide-password', \App\Livewire\OlvidePassword::class)->name('olvide-password')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('agenda'));
    Route::get('/agenda', \App\Livewire\Agenda::class)->name('agenda');
    Route::get('/mis-turnos', \App\Livewire\MisTurnos::class)->name('mis-turnos');
    Route::get('/pago/{reserva}', \App\Livewire\Pago::class)->name('pago');
    Route::get('/pago/mp/iniciar/{reserva}', [MercadoPagoController::class, 'iniciarPago'])->name('mp.iniciar');
    Route::get('/pago/mp/success', [MercadoPagoController::class, 'success'])->name('mp.success');
    Route::get('/pago/mp/failure', [MercadoPagoController::class, 'failure'])->name('mp.failure');
    Route::get('/pago/mp/pending', [MercadoPagoController::class, 'pending'])->name('mp.pending');
    Route::get('/perfil', \App\Livewire\Perfil::class)->name('perfil');

    // Admin
    Route::get('/admin/usuarios', \App\Livewire\Admin\Usuarios::class)->name('admin.usuarios');
    Route::get('/admin/configuracion', \App\Livewire\Admin\Configuracion::class)->name('admin.configuracion');
    Route::get('/admin/estadisticas', \App\Livewire\Admin\Estadisticas::class)->name('admin.estadisticas');
    Route::get('/admin/prueba-comprobante', \App\Livewire\Admin\PruebaComprobante::class)->name('admin.prueba-comprobante');
});
