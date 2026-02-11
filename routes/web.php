<?php

use App\Http\Controllers\SmkpController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('smkp.index');
});

// Route Index (View)
Route::get('/smkp/{folder?}', [SmkpController::class, 'index'])->name('smkp.index');

// Route Upload & Create (POST)
Route::post('/smkp/upload/{folder?}', [SmkpController::class, 'upload'])->name('smkp.upload');
Route::post('/smkp/create-folder/{folder?}', [SmkpController::class, 'createFolder'])->name('smkp.create_folder');

// Route Edit & Delete Folder (PUT & DELETE) - BARU
Route::put('/smkp/folder/{id}', [SmkpController::class, 'updateFolder'])->name('smkp.update_folder');
Route::delete('/smkp/folder/{id}', [SmkpController::class, 'deleteFolder'])->name('smkp.delete_folder');

// Route Download
Route::get('/smkp/file/{id}', [SmkpController::class, 'download'])->name('smkp.download');
// Tambahkan baris ini di bagian paling bawah file routes/web.php
// Route Delete File
Route::delete('/smkp/file/{id}', [SmkpController::class, 'deleteFile'])->name('smkp.delete_file');