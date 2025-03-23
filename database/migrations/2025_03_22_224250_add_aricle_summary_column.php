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
        Schema::table('feed_items', function (Blueprint $table) {
            $table->text('article_summary')->after('article_image')->nullable()->comment('記事サマリ');
        });
    }

    public function down(): void
    {
        Schema::table('feed_items', function (Blueprint $table) {
            $table->dropColumn('article_summary');
        });
    }
};
