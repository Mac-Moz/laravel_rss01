<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feed_items', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name'); // RSSのタグ
            $table->text('article_title'); // 記事タイトル
            $table->dateTime('article_date'); // 公開日
            $table->string('article_link', 255)->unique(); // 記事リンク
            $table->string('article_image')->nullable(); // 記事画像
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_items');
    }
};
