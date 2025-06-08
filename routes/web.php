<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ImageController;

Route::get('/', function () {
    return redirect()->route('folders.index');
});

// Rutas de carpetas
Route::resource('folders', FolderController::class);

// Rutas de imágenes
Route::get('folders/{folder}/upload', [ImageController::class, 'upload'])->name('images.upload');
Route::post('images/store', [ImageController::class, 'store'])->name('images.store');
Route::delete('images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');

// 🆕 NUEVAS RUTAS
Route::delete('folders/{folder}/images/all', [ImageController::class, 'destroyAll'])->name('images.destroyAll');
Route::post('images/destroy-multiple', [ImageController::class, 'destroyMultiple'])->name('images.destroyMultiple');