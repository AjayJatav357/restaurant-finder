<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RestaurantController;

Route::get('/', [RestaurantController::class, 'index']);
Route::post('/restaurants', [RestaurantController::class, 'fetchRestaurants'])->name('restaurants.fetch');
