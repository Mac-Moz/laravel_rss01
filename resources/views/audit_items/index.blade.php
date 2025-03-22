@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="container mx-auto px-4 py-6" x-data="{ activeModal: null, modalContent: '' }">
    <h1 class="text-m font-bold text-gray-400 mb-6">Audit Checklist</h1>

    @if(session('success'))
    <div class="text-green-600 mb-4">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border text-gray-400 border-gray-300 text-sm">
            <thead class="bg-gray-100 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">監査項目</th>
                    <th class="px-4 py-2 border">監査内容</th>
                    <th class="px-4 py-2 border">確認証憑</th>
                    <th class="px-4 py-2 border">被監査部門の意見</th>
                    <th class="px-4 py-2 border">問題点</th>
                    <th class="px-4 py-2 border">改善指摘事項</th>
                    <th class="px-4 py-2 border">参考外部情報</th>
                    <th class="px-4 py-2 border">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                <tr class="hover:bg-gray-50 align-top">
                    <td class="px-2 py-2 border">{{ $item->id }}</td>
                    <td class="px-2 py-2 border">{{ $item->category }}</td>

                    @if(request('edit') == $item->id)
                    <!-- 編集モード -->
                    <form method="POST" action="{{ route('audit_items.update', $item->id) }}">
                        @csrf
                        <td class="px-2 py-2 border">
                            <textarea name="content" rows="3" class="w-full border rounded p-1">{{ old('content', $item->content) }}</textarea>
                        </td>
                        <td class="px-2 py-2 border">
                            <textarea name="evidence" rows="2" class="w-full border rounded p-1">{{ old('evidence', $item->evidence) }}</textarea>
                        </td>
                        <td class="px-2 py-2 border">
                            <textarea name="auditee_opinion" rows="2" class="w-full border rounded p-1">{{ old('auditee_opinion', $item->auditee_opinion) }}</textarea>
                        </td>
                        <td class="px-2 py-2 border">
                            <textarea name="issue" rows="2" class="w-full border rounded p-1">{{ old('issue', $item->issue) }}</textarea>
                        </td>
                        <td class="px-2 py-2 border">
                            <textarea name="recommendation" rows="2" class="w-full border rounded p-1">{{ old('recommendation', $item->recommendation) }}</textarea>
                        </td>
                        <td class="px-2 py-2 border">
                            <textarea readonly class="w-full h-32 border rounded bg-gray-50 p-1 text-sm resize-none overflow-y-auto cursor-pointer"
                                @click="modalContent = `{{ $item->reference_info }}`; activeModal = 'modal'">{{ $item->reference_info }}</textarea>
                        </td>
                        <td class="px-2 py-2 border space-y-1 w-36">
                            <button type="submit" class="bg-blue-400 text-white px-2 py-1 rounded w-full hover:bg-green-600">保存</button>
                            <button type="submit" class="bg-blue-400 text-white px-2 py-1 rounded w-full hover:bg-green-600">
                                <a href="{{ route('audit_items.index') }}" >キャンセル</a>
                            </button>
                        </td>
                    </form>
                    @else
                    <!-- 表示モード -->
                    <td class="px-2 py-2 border">
                        <textarea readonly
                            class="w-full h-24 p-1 border rounded bg-gray-50 text-sm resize-none overflow-y-auto cursor-pointer"
                            @click="modalContent = `{{ $item->content }}`; activeModal = 'modal'">{{ $item->content }}</textarea>
                    </td>
                    <td class="px-2 py-2 border">{{ Str::limit($item->evidence, 30) }}</td>
                    <td class="px-2 py-2 border">{{ Str::limit($item->auditee_opinion, 30) }}</td>
                    <td class="px-2 py-2 border">{{ Str::limit($item->issue, 30) }}</td>
                    <td class="px-2 py-2 border">{{ Str::limit($item->recommendation, 30) }}</td>
                    <td class="px-2 py-2 border">
                        @if($item->reference_info)
                        <textarea readonly
                            class="w-full h-32 p-1 border rounded bg-gray-50 text-sm resize-none overflow-y-auto cursor-pointer"
                            @click="modalContent = `{{ $item->reference_info }}`; activeModal = 'modal'">{{ $item->reference_info }}</textarea>
                        @else
                        <em class="text-gray-500">未生成</em>
                        @endif
                    </td>
                    <td class="px-2 py-2 border space-y-1 w-36">
                        <button type="submit" class="bg-blue-400 text-white px-2 py-1 rounded w-full hover:bg-green-600">

                            <a href="{{ route('audit_items.index', ['edit' => $item->id]) }}">編集</a>
                        </button>
                        <form action="{{ route('audit_items.regenerate', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-400 text-white px-2 py-1 rounded w-full hover:bg-green-600">
                                サマリ更新
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- モーダルポップアップ（監査内容・参考情報共通） -->
    <div
        x-show="activeModal === 'modal'"
        x-transition
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @keydown.escape.window="activeModal = null"
        @click.outside="activeModal = null">
        <div class="bg-white text-gray-700 p-6 rounded-2xl shadow-lg max-w-2xl w-full relative">
            <h3 class="text-lg font-bold mb-2">全文表示</h3>
            <div class="text-sm whitespace-pre-wrap overflow-y-auto max-h-[60vh]">
                <p x-text="modalContent"></p>
            </div>
            <button
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
                @click="activeModal = null">✕</button>
        </div>
    </div>

    <div class="mt-4">
        {{ $items->appends(request()->except('page'))->links() }}
    </div>
</div>
@endsection