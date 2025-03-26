<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedItem;
use App\Models\FeedUrl;
use App\Services\RssService;
use App\Services\GoogleAlertRssService;
use App\Services\CrawlService;
use App\Services\DatabaseTag;
use App\Services\ArticleSummary;

class RssController extends Controller
{
    protected $rssService;
    protected $googleAlertRssService;
    protected $crawlService;
    protected $databaseTag;
    protected $articleSummary;

    public function __construct(
        RssService $rssService,
        GoogleAlertRssService $googleAlertRssService,
        CrawlService $crawlService,
        DatabaseTag $databaseTag,
        ArticleSummary $articleSummary
    ) {
        $this->rssService = $rssService;
        $this->googleAlertRssService = $googleAlertRssService;
        $this->crawlService = $crawlService;
        $this->databaseTag = $databaseTag;
        $this->articleSummary = $articleSummary;
    }

    public function fetch()
    {
        $feeds = FeedUrl::where('type', 'rss')->get();
        foreach ($feeds as $feed) {
            $this->rssService->fetchAndStore($feed->url, $feed->tag);
        }

        $feeds_GAlert = FeedUrl::where('type', 'google_alert')->get();
        foreach ($feeds_GAlert as $feed_GAlert) {
            $this->googleAlertRssService->fetchAndStore($feed_GAlert->url, $feed_GAlert->tag);
        }

        $feeds_Crawl = FeedUrl::where('type', 'crawl')->get();
        foreach ($feeds_Crawl as $feed_Crawl) {
            $this->crawlService->fetchAndStore($feed_Crawl->tag);
        }

        // 既存のDatabaseTagサービスによる処理
        $this->databaseTag->classifyAndStoreLabels();

        // 記事要約処理
        $this->summarizeArticles();

        return redirect()->route('rss.index')->with('success', 'RSSフィードの更新が完了しました');
    }

    protected function summarizeArticles()
    {
        $unSummarizedItems = FeedItem::whereNull('article_summary')->get();

        foreach ($unSummarizedItems as $item) {
            $summary = $this->articleSummary->summarize($item->article_title, $item->article_link);

            if ($summary) {
                $item->article_summary = $summary;
                $item->save();
            }
        }
    }

    public function index(Request $request)
    {
        $tag = $request->query('tag');
        $label = $request->query('label');

        $query = FeedItem::query();

        if ($tag) {
            $query->where('tag_name', $tag);
        }

        if ($label) {
            $query->where('label_audit', $label);
        }

        $feedItems = $query->orderBy('article_date', 'desc')->paginate(50);

        $tags = FeedItem::distinct()->pluck('tag_name')->filter()->unique();
        $labels = FeedItem::distinct()->pluck('label_audit')->filter()->unique();

        $imagesPath = public_path('images');
        $images = glob($imagesPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);

        $randomImage = !empty($images)
            ? basename($images[array_rand($images)])
            : 'AuditMate01.png';

        return view('rss.index', compact('feedItems', 'tags', 'labels', 'randomImage'));
    }
}
