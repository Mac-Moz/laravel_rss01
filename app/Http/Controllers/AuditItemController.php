<?php

namespace App\Http\Controllers;

use App\Models\AuditItem;
use Illuminate\Http\Request;
use App\Services\ReferenceInfoGenerator;

class AuditItemController extends Controller
{
    // 一覧表示
    public function index(Request $request)
    {
        $items = AuditItem::orderBy('id')->paginate(10);
        return view('audit_items.index', compact('items'));
    }

    // 編集画面表示
    public function edit($id)
    {
        $item = AuditItem::findOrFail($id);
        return view('audit_items.edit', compact('item'));
    }

    // 更新処理
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'nullable|string',
            'evidence' => 'nullable|string',
            'auditee_opinion' => 'nullable|string',
            'issue' => 'nullable|string',
            'recommendation' => 'nullable|string',
        ]);

        $item = AuditItem::findOrFail($id);
        $item->update($request->only([
            'content',
            'evidence',
            'auditee_opinion',
            'issue',
            'recommendation'
        ]));

        return redirect()->route('audit_items.index')->with('success', '更新しました');
    }

    // 🔍 追加：reference_info 再生成処理
    public function regenerate($id, ReferenceInfoGenerator $generator)
    {
        $item = AuditItem::findOrFail($id);
        $reference = $generator->generate($item);

        if ($reference) {
            $item->reference_info = $reference;
            $item->save();
            return back()->with('success', "ID {$item->id} の参考情報を再生成しました。");
        }

        return back()->with('error', "ID {$item->id} の再生成に失敗しました。");
    }


}
