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
        Schema::create('audit_items', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable(); // ⚪︎などの記号
            $table->string('department')->nullable(); // 担当部署
            $table->string('category'); // 監査項目
            $table->string('legal_basis')->nullable(); // 根拠法令・根拠規程
            $table->text('content'); // 監査内容
            $table->text('evidence')->nullable(); // 確認証憑
            $table->text('auditee_opinion')->nullable(); // 被監査部門の意見
            $table->text('issue')->nullable(); // 問題点
            $table->text('recommendation')->nullable(); // 改善指摘事項
            $table->text('reference_info')->nullable(); // 外部参照情報
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_items');
    }
};
