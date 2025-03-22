<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AuditMate')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <!-- ✅ 固定ヘッダー -->
        <header class="bg-white shadow-md p-4 flex flex-col md:flex-row items-center justify-between fixed top-0 left-0 w-full z-50 space-y-2 md:space-y-0">
            <!-- 左側：ロゴ＋リンク -->
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-blue-700 tracking-wide">
                    <a href="{{ url('/') }}">AuditMate</a>
                </h1>


            </div>

            <!-- 右側：操作類 -->
            <div class="flex flex-wrap items-center space-x-3">
                <!-- フィード一覧 -->
                <a href="{{ url('/') }}"
                    class="bg-blue-400 hover:bg-green-600 text-white px-3 py-1 rounded shadow text-sm">
                    Feed一覧
                </a>
                <!-- ✅ RSS更新ボタンを追加 -->
                <a href="{{ route('rss.fetch') }}"
                    class="bg-blue-400 hover:bg-green-600 text-white px-3 py-1 rounded shadow text-sm">
                    Feed更新
                </a>

                <!-- 監査項目一覧 -->
                <a href="{{ route('audit_items.index') }}"
                    class="bg-blue-400 hover:bg-green-600 text-white px-3 py-1 rounded shadow text-sm">
                    監査チェックリスト

                </a>

                <!-- 擬似ログイン表示 -->
                <span class="text-gray-700 text-sm ml-2">ようこそ、ゲストさん</span>
            </div>
        </header>

        <!-- ✅ メインコンテンツ -->
        <main class="mt-32">
            @yield('content')
        </main>

        <!-- ✅ フッター -->
        <footer class="text-center text-sm text-gray-500 mt-8">
            AuditMate v01.1.0
        </footer>
    </div>

    <!-- ✅ 共通スクリプト -->
    <script>
        function submitMainSummarize() {
            const form = document.getElementById('main-summary-form');
            if (form) {
                form.submit();
            } else {
                alert('記事要約フォームが見つかりません（form#main-summary-form が必要です）');
            }
        }

        // ✅ 本日の日付をセット
        function setTodayToSummarizeTerm() {
            const today = new Date().toISOString().split('T')[0];
            const startInput = document.querySelector('input[name="start_date"]');
            const endInput = document.querySelector('input[name="end_date"]');
            if (startInput && endInput) {
                startInput.value = today;
                endInput.value = today;
            }
        }
    </script>
</body>

</html>