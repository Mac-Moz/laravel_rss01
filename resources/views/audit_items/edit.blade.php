@extends('layouts.app')

@section('content')
<h1>監査項目 編集</h1>

<form method="POST" action="{{ route('audit_items.update', $item->id) }}">
    @csrf

    {{-- 入力項目 --}}
    <div>
        <label>監査内容</label><br>
        <textarea name="content" rows="3" cols="80">{{ old('content', $item->content) }}</textarea>
    </div>

    <div>
        <label>確認証憑</label><br>
        <textarea name="evidence" rows="2" cols="80">{{ old('evidence', $item->evidence) }}</textarea>
    </div>

    <div>
        <label>被監査部門の意見</label><br>
        <textarea name="auditee_opinion" rows="2" cols="80">{{ old('auditee_opinion', $item->auditee_opinion) }}</textarea>
    </div>

    <div>
        <label>問題点</label><br>
        <textarea name="issue" rows="2" cols="80">{{ old('issue', $item->issue) }}</textarea>
    </div>

    <div>
        <label>改善指摘事項</label><br>
        <textarea name="recommendation" rows="2" cols="80">{{ old('recommendation', $item->recommendation) }}</textarea>
    </div>

    {{-- 🔍 追加：参考情報（AI補完） --}}
    <div>
        <label>参考情報（AI補完）</label><br>
        <textarea id="reference_info" rows="5" cols="80" readonly>{{ $item->reference_info }}</textarea><br>
        <button type="button" onclick="copyReference()">コピー</button>
    </div>

    <div style="margin-top: 15px;">
        <button type="submit">保存</button>
        <a href="{{ route('audit_items.index') }}">戻る</a>
    </div>
</form>

<script>
    function copyReference() {
        const textarea = document.getElementById('reference_info');
        textarea.select();
        document.execCommand('copy');
        alert('参考情報をコピーしました！');
    }
</script>
@endsection