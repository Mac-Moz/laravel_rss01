<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedItem;
use App\Services\RssService;
use App\Services\GoogleAlertRssService;
use function Laravel\Prompts\alert;

class RssController extends Controller
{
    protected $rssService;
    protected $GoogleAlertRssService;
    public function __construct(RssService $rssService, GoogleAlertRssService $GoogleAlertRssService)
    {
        $this->rssService = $rssService;
        $this->GoogleAlertRssService = $GoogleAlertRssService;
    }

   

    public function fetch()
    {
        // 取得するRSSフィードのリスト
        $feeds = [
            ['url' => 'https://www.fsa.go.jp/fsaNewsListAll_rss2.xml', 'tag' => '金融庁'],
            ['url' => 'https://www.nhk.or.jp/rss/news/cat0.xml', 'tag' => 'NHK主要ニュース'],
            ['url' => 'https://assets.wor.jp/rss/rdf/nikkei/news.rdf', 'tag' => '日経新聞'],
        ];

        foreach ($feeds as $feed) {
            $this->rssService->fetchAndStore($feed['url'], $feed['tag']);
        }

        // 取得するRSSフィードのリスト
        $feeds_GAlert = [
            ['url' => 'https://www.google.co.jp/alerts/feeds/04686804727264430900/18124849360240036542', 'tag' => 'えび']

        ];

        foreach ($feeds_GAlert as $feed_GAlert) {
            $this->GoogleAlertRssService->fetchAndStore($feed_GAlert['url'], $feed_GAlert['tag']);
        }


        alert('RSSフィードを更新しました') ;
        return redirect()->route('rss.index')->with('success', );

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feedItems = FeedItem::orderBy('article_date', 'desc')->paginate(10);
        return view('rss.index', compact('feedItems'));
        //
    }
}
