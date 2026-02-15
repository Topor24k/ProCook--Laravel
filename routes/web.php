<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Serve React App for all routes (SPA)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

