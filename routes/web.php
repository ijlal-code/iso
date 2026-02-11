<?php

use App\Http\Controllers\SmkpController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('smkp.index');
});

// Route Utama SMKP
Route::get('/smkp/{folder?}', [SmkpController::class, 'index'])->name('smkp.index');

// Upload File (Parameter di tengah tidak masalah karena wajib/bukan opsional di sini, tapi kita rapikan juga)
Route::post('/smkp/upload/{folder}', [SmkpController::class, 'upload'])->name('smkp.upload');

// PERBAIKAN: Parameter {folder?} dipindah ke belakang agar tidak 404 saat null (Root)
Route::post('/smkp/create-folder/{folder?}', [SmkpController::class, 'createFolder'])->name('smkp.create_folder');

Route::get('/smkp/file/{id}', [SmkpController::class, 'download'])->name('smkp.download');