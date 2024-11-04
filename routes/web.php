<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('v1/')->group(function () {
    Route::prefix('file')->controller(FileController::class)->group(function () {
        Route::post('upload', 'uploadFileWithEncription');
        Route::get('download/{id}', 'downloadFileWithDecription');
    });
});
