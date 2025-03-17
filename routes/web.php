<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RssController;
use App\Http\Controllers\ArticleController;

Route::get('/',

[RssController::class, 'index'])->name('rss.index');

Route::get('/rss/fetch', [RssController::class, 'fetch'])->name('rss.fetch');
Route::get('/rss', [RssController::class, 'index'])->name('rss.index');
Route::post('/article/summarize', [ArticleController::class, 'summarize'])->name('article.summarize');

Route::post('/article/summarizeByTerm', [ArticleController::class, 'summarizeByTerm'])
    ->name('article.summarizeByTerm');