<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel App')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <header class="bg-white shadow p-4">
            <h1 class="text-xl font-bold"><a href="{{ url('/') }}">Laravel RSS Reader</a></h1>
        </header>
        <main class="mt-4">
            @yield('content')
        </main>
        <footer class="text-center text-sm text-gray-500 mt-4">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
        </footer>
    </div>
</body>

</html>