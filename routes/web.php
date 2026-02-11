<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmkpController;

Route::get('/', function () {
    return redirect()->route('smkp.index');
});

// Route Utama SMKP
Route::get('/smkp/{folder?}', [SmkpController::class, 'index'])->name('smkp.index');
Route::post('/smkp/{folder}/upload', [SmkpController::class, 'upload'])->name('smkp.upload');
Route::get('/smkp/file/{id}', [SmkpController::class, 'download'])->name('smkp.download');