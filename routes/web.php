<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RssController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/rss/fetch', [RssController::class, 'fetch'])->name('rss.fetch');
Route::get('/rss', [RssController::class, 'index'])->name('rss.index');