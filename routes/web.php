<?php

use App\Http\Controllers\SmkpController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('smkp.index');
});

// Route Utama SMKP
Route::get('/smkp/{folder?}', [SmkpController::class, 'index'])->name('smkp.index');
Route::post('/smkp/{folder}/upload', [SmkpController::class, 'upload'])->name('smkp.upload');
Route::post('/smkp/{folder?}/create-folder', [SmkpController::class, 'createFolder'])->name('smkp.create_folder'); // Route Baru
Route::get('/smkp/file/{id}', [SmkpController::class, 'download'])->name('smkp.download');