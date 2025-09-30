<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('invoice/{sale}', [\App\Http\Controllers\InvoiceController::class, 'generate'])
    ->name('generate.invoice')
    ->middleware(['auth']);
