<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TasksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('auth.logout');

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TasksController::class, 'index'])->name('tasks.index');
        Route::get('/{id}', [TasksController::class, 'show'])->name('tasks.show');
        Route::post('/', [TasksController::class, 'store'])->name('tasks.store');
        Route::put('/{id}', [TasksController::class, 'update'])->name('tasks.update');
        Route::delete('/{id}', [TasksController::class, 'destroy'])->name('tasks.destroy');
    });
});