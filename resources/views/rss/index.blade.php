@extends('layouts.app')

@section('content')
<header class="fixed top-0 left-0 w-full bg-white shadow-md p-4 flex justify-between items-center z-10">
    @if (Route::has('login'))
    <nav class="-mx-3 flex flex-1 justify-end">
        @auth
        <a href="{{ url('/dashboard') }}" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
            Dashboard
        </a>
        @else
        <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
            Log in
        </a>
        @if (Route::has('register'))
        <a href="{{ route('register') }}" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
            Register
        </a>
        @endif
        @endauth
    </nav>
    @endif
    <div class="flex items-center space-x-4">
        <a href="{{ route('rss.fetch') }}" class="bg-blue-500 text-white px-4 py-2 rounded">RSS更新</a>

        <!-- 🔍 追加：監査項目ページへのリンク -->
        <a href="{{ route('audit_items.index') }}" class="bg-purple-500 text-white px-4 py-2 rounded">
            監査項目一覧
        </a>


        <!-- 期間指定用フォーム -->
        <form id="summary-form" action="{{ route('article.summarizeByTerm') }}" method="POST" class="flex items-center space-x-2">
            @csrf
            <label for="start_date">開始日:</label>
            <input type="date" id="start_date" name="start_date" class="border px-2 py-1 rounded">

            <label for="end_date">終了日:</label>
            <input type="date" id="end_date" name="end_date" class="border px-2 py-1 rounded">

            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">
                期間指定で要約
            </button>
        </form>
    </div>
</header>

<div class="flex mt-16 h-screen">
    <!-- サイドバー -->
    <aside class="w-1/6 bg-gray-100 p-4 overflow-y-auto h-screen fixed  left-0 z-10">
        <h3 class="text-lg font-bold mb-2">タグ一覧</h3>
        <ul>
            <li><a href="{{ route('rss.index') }}" class="text-blue-600 hover:underline">すべて表示</a></li>
            @foreach ($tags as $tag)
            <li>
                <a href="{{ route('rss.index', ['tag' => $tag]) }}" class="text-blue-600 hover:underline">
                    {{ $tag }}
                </a>
            </li>
            @endforeach
        </ul>
    </aside>

    <!-- メインコンテンツ -->
    <main class="w-3/4 p-4 overflow-y-auto h-screen ml-auto">
        <h2 class="text-xl font-bold">RSSフィード一覧</h2>
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('article.summarize') }}" method="POST" class="mt-4">
            @csrf
            @if ($feedItems->isNotEmpty())
            <div class="grid grid-cols-2 gap-4">
                @foreach ($feedItems as $item)
                <div class="border p-4 rounded shadow">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="articles[]"
                            value="{{ json_encode(['id' => $item->id, 'title' => $item->article_title, 'link' => $item->article_link, 'label_audit' => $item->label_audit,'image' => $item->article_image, 'date' => $item->article_date]) }}">
                        <span>選択</span>
                    </label>

                    <a href="{{ $item->article_link }}" target="_blank" class="text-blue-500 hover:text-blue-700 text-lg">
                        {{ $item->article_title }}
                    </a>
                    <p class="text-gray-500">記事タグ：{{ $item->tag_name }}</p>
                    <p class="text-gray-500">内部監査ラベル：{{ $item->label_audit }}</p>
                    <p class="text-sm">日付：{{ \Carbon\Carbon::parse($item->article_date)->format('Y-m-d H:i') }}</p>

                    @if($item->article_image)
                    <img src="{{ $item->article_image }}" alt="Image" class="mt-2 w-full max-w-xs">
                    @endif
                </div>
                @endforeach
            </div>

            <button type="submit" id="main-summarize-btn" class="mt-3 bg-green-500 text-white px-4 py-2 rounded">選択した記事を要約</button>
            @else
            <p class="text-gray-500">該当するRSSフィードがありません。</p>
            @endif
        </form>
    </main>
</div>
@endsection