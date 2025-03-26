@extends('layouts.app')

@section('content')
<div class="container mt-32">
    <h2 class="font-bold text-lg mb-4">Feed URL管理</h2>
    <a href="{{ route('feed_urls.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded">新規登録</a>

    @if(session('success'))
    <div class="mt-4 bg-green-100 text-green-800 p-2 rounded">
        {{ session('success') }}
    </div>
    @endif

    <table class="table-auto w-full mt-4">
        <thead>
            <tr class="bg-gray-200">
                <th class="border px-4 py-2">URL</th>
                <th class="border px-4 py-2">タグ</th>
                <th class="border px-4 py-2">タイプ</th>
                <th class="border px-4 py-2">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feedUrls as $feedUrl)
            <tr>
                <td class="border px-4 py-2">{{ $feedUrl->url }}</td>
                <td class="border px-4 py-2">{{ $feedUrl->tag }}</td>
                <td class="border px-4 py-2">{{ $feedUrl->type }}</td>
                <td class="border px-4 py-2">
                    <a href="{{ route('feed_urls.edit', $feedUrl) }}" class="text-blue-500">編集</a>
                    <form action="{{ route('feed_urls.destroy', $feedUrl) }}" method="POST" class="inline-block">
                        @csrf @method('DELETE')
                        <button class="text-red-500 ml-2">削除</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection