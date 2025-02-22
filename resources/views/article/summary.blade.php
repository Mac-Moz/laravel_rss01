@extends('layouts.app')

@section('content')
<div class="container">
    <h2>記事の要約結果</h2>

    @foreach ($summaries as $article)
        <div class="border p-4 mb-4">
            <p><strong>ID:</strong> {{ $article['id'] }}</p>
            <p><strong>Date:</strong> {{ $article['date'] }}</p>
            <p><strong>Title:</strong> {{ $article['title'] }}</p>
            <p><strong>Link:</strong> <a href="{{ $article['link'] }}" target="_blank">{{ $article['link'] }}</a></p>
            <img src="{{ $article['image'] }}" alt="Article Image" class="img-fluid" style="max-width: 200px;">
            <h3>記事サマリ:</h3>
            <p>{{ nl2br(e($article['summary'])) }}</p>
        </div>
    @endforeach
</div>
@endsection
