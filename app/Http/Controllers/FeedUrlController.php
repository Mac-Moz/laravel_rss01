<?php

namespace App\Http\Controllers;

use App\Models\FeedUrl;
use Illuminate\Http\Request;

class FeedUrlController extends Controller
{
    public function index()
    {
        $feedUrls = FeedUrl::all();
        return view('feed_urls.index', compact('feedUrls'));
    }

    public function create()
    {
        return view('feed_urls.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'tag' => 'nullable|string',
            'type' => 'required|in:rss,google_alert,crawl',
        ]);

        FeedUrl::create($request->only(['url', 'tag', 'type']));

        return redirect()->route('feed_urls.index')->with('success', 'Feed URLを追加しました。');
    }

    public function edit(FeedUrl $feedUrl)
    {
        return view('feed_urls.edit', compact('feedUrl'));
    }

    public function update(Request $request, FeedUrl $feedUrl)
    {
        $request->validate([
            'url' => 'required|url',
            'tag' => 'nullable|string',
            'type' => 'required|in:rss,google_alert,crawl',
        ]);

        $feedUrl->update($request->only(['url', 'tag', 'type']));

        return redirect()->route('feed_urls.index')->with('success', 'Feed URLを更新しました。');
    }

    public function destroy(FeedUrl $feedUrl)
    {
        $feedUrl->delete();

        return redirect()->route('feed_urls.index')->with('success', 'Feed URLを削除しました。');
    }
}
