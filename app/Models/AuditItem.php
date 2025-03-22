<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditItem extends Model
{
    // テーブル名（Laravelは複数形のテーブル名を自動推測するので明示不要）
    protected $table = 'audit_items';

    // ホワイトリスト：更新可能なカラム（fillable）
    protected $fillable = [
        'no',
        'department',
        'category',
        'legal_basis',
        'content',
        'evidence',
        'auditee_opinion',
        'issue',
        'recommendation',
        'reference_info',
    ];
}
