@extends('layouts.app')
@section('content')
<div class="container mt-32">
    <h2 class="font-bold mb-4">新規Feed URL登録</h2>
    <form action="{{ route('feed_urls.store') }}" method="POST">
        @csrf
        @include('feed_urls.form')
        <button class="px-4 py-2 bg-blue-500 text-white rounded">登録</button>
    </form>
</div>
@endsection