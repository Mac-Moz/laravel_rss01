@extends('layouts.app')
@section('content')
<div class="container mt-32">
    <h2 class="font-bold mb-4">Feed URL編集</h2>
    <form action="{{ route('feed_urls.update', $feedUrl) }}" method="POST">
        @csrf @method('PUT')
        @include('feed_urls.form')
        <button class="px-4 py-2 bg-green-500 text-white rounded">更新</button>
    </form>
</div>
@endsection