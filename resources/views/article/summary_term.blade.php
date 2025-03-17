@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-xl font-bold mb-4">要約結果</h2>

    @if (!empty($summary))
    <div class="bg-white p-4 shadow rounded">
        @php
        // 要約結果を個別に分割
        $summaries = explode("\n\n", trim($summary));
        @endphp

        @foreach ($summaries as $summaryItem)
        <div class="mb-6 p-4 border rounded shadow">
            <p class="text-gray-700 whitespace-pre-line">{{ $summaryItem }}</p>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-gray-500">要約結果がありません。</p>
    @endif

    <div class="mt-4">
        <a href="{{ route('rss.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded">戻る</a>
    </div>
</div>
@endsection