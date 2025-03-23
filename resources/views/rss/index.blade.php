    @extends('layouts.app')

    @section('content')
    <script>
        function submitMainSummarize() {
            const form = document.getElementById('main-summary-form');
            if (form) {
                form.submit();
            } else {
                alert('記事要約フォーム（id=\"main-summary-form\"）が見つかりません。');
            }
        }
    </script>

    <div class="flex mt-32 h-screen">
        <!-- サイドバー -->
        <aside class="w-1/6 bg-gray-100 p-4 overflow-y-auto fixed left-0 top-0 bottom-0 z-10">
            <div class="h-full py-32">






                <!-- 監査ラベル一覧 -->
                <ul class="mb-8">
                    <h3 class="text-m font-bold divide-y divide-blue-400 text-gray-400 mb-2">AuditLabel</h3>
                    <li><a href="{{ route('rss.index') }}" class="text-gray-400 text-sm hover:underline mt-2">ALL</a></li>
                    @foreach ($labels as $label)
                    <li>
                        <a href="{{ route('rss.index', ['label' => $label]) }}"
                            class="text-gray-400 text-sm hover:underline mt-2">
                            {{ $label }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                <!-- タグ一覧 -->
                <h3 class="text-m font-bold text-gray-400 mb-2">Souce</h3>
                <ul>
                    <li><a href="{{ route('rss.index') }}" class="text-gray-400 text-sm hover:underline mt-2">ALL</a></li>
                    @foreach ($tags as $tag)
                    <li>
                        <a href="{{ route('rss.index', ['tag' => $tag]) }}"
                            class="text-gray-400 text-sm hover:underline mt-2">
                            {{ $tag }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </aside>


        <!-- ✅ メイン -->
        <main class="w-3/4 p-4 overflow-y-auto h-screen ml-auto">
            <h2 class="text-m font-bold text-gray-400 mb-4">Feed一覧</h2>

            @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
            @endif

            <form id="main-summary-form" action="{{ route('article.summarize') }}" method="POST" class="mt-4">
                @csrf
                @if ($feedItems->isNotEmpty())

                <div class="grid grid-cols-2 gap-3">
                    @foreach ($feedItems as $item)
                    <div class="border p-4 rounded-3xl shadow bg-white">
                        <a href="{{ $item->article_link }}" target="_blank" class="flex items-center text-gray-400 hover:underline text-m font-bold">
                            <img
                                src="{{ $item->article_image ? $item->article_image : asset('images/'.$randomImage) }}"
                                alt="Image"
                                class="m-2 w-24 h-24 object-contain rounded-full bg-gray-100 border border-gray-200 p-1">
                            <p class="ml-3">{{ $item->article_title }}</p>
                        </a>
                        <p class="text-gray-400 text-sm break-words whitespace-pre-wrap mt-2">AuditLabel：{{ $item->label_audit }}</p>
                        <p class="text-gray-400 text-sm">Souce：{{ $item->tag_name }}</p>
                        <p class="text-sm text-gray-400">Date：{{ \Carbon\Carbon::parse($item->article_date)->format('Y-m-d H:i') }}</p>

                        <p class="text-gray-400 text-sm break-words whitespace-pre-wrap mt-2">Summary：<br>{{ $item->article_summary }}</br></p>


                    </div>
                    @endforeach
                </div>


                @else
                <p class="text-gray-500">該当するRSSフィードがありません。</p>
                @endif
            </form>
        </main>
    </div>
    @endsection