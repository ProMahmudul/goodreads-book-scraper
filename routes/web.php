<?php

use App\Http\Controllers\ScraperController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/scrape/{isbn}', [ScraperController::class, 'scrape']);