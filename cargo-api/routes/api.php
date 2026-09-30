<?php

use App\Http\Controllers\PriceController;
use Illuminate\Support\Facades\Route;

Route::post('/', [PriceController::class, 'store']);
Route::get('/', [PriceController::class, 'index']);
