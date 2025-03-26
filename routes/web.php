<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RssController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuditItemController;
use App\Http\Controllers\FeedUrlController;

// トップページ → RSS 一覧
Route::get('/', [RssController::class, 'index'])->name('rss.index');

// RSS 関連ルート
Route::get('/rss/fetch', [RssController::class, 'fetch'])->name('rss.fetch');
Route::get('/rss', [RssController::class, 'index'])->name('rss.index');

// 記事要約関連
Route::post('/article/summarize', [ArticleController::class, 'summarize'])->name('article.summarize');
Route::post('/article/summarizeByTerm', [ArticleController::class, 'summarizeByTerm'])->name('article.summarizeByTerm');

// 🔍 追加：AuditItem 関連ルート（一覧・編集・更新）
Route::get('/audit-items', [AuditItemController::class, 'index'])->name('audit_items.index');
Route::get('/audit-items/{id}/edit', [AuditItemController::class, 'edit'])->name('audit_items.edit');
Route::post('/audit-items/{id}', [AuditItemController::class, 'update'])->name('audit_items.update');
Route::post('/audit-items/{id}/regenerate', [AuditItemController::class, 'regenerate'])->name('audit_items.regenerate');


// 🔍 追加：FeedUrl 関連ルート（一覧・新規作成・編集・更新・削除）
Route::resource('feed_urls', FeedUrlController::class);