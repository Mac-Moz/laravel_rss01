<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedItem;
use App\Services\RssService;
use App\Services\GoogleAlertRssService;
use App\Services\CrawlService;
use App\Services\DatabaseTag;
use App\Services\ArticleSummary; // 追加

class RssController extends Controller
{
    protected $rssService;
    protected $GoogleAlertRssService;
    protected $CrawlService;
    protected $databaseTag;
    protected $articleSummary; // 追加

    public function __construct(
        RssService $rssService,
        GoogleAlertRssService $GoogleAlertRssService,
        CrawlService $CrawlService,
        DatabaseTag $databaseTag,
        ArticleSummary $articleSummary // 追加
    ) {
        $this->rssService = $rssService;
        $this->GoogleAlertRssService = $GoogleAlertRssService;
        $this->CrawlService = $CrawlService;
        $this->databaseTag = $databaseTag;
        $this->articleSummary = $articleSummary; // 追加
    }

    public function fetch()
    {
        $feeds = [
            ['url' => 'https://www.fsa.go.jp/fsaNewsListAll_rss2.xml', 'tag' => '金融庁'],
            ['url' => 'https://note.com/yoruo_hanada/rss', 'tag' => '花田宏造税理士事務所'],
            ['url' => 'https://note.com/kazuaki_mizuchi/rss', 'tag' => 'note_水地一彰'],
            ['url' => 'https://chatgpt-lab.com/rss', 'tag' => 'note_ChatGPT研究所'],
            ['url' => 'https://note.com/brainy_orchid962/rss', 'tag' => 'note_香坂コーポレートガバナンス・コンサルティング'],
            ['url' => 'https://note.com/like_kalmia1752/rss', 'tag' => 'note_加藤裕則＿経済ジャーナリスト'],
            ['url' => 'https://note.com/ndot_man/rss', 'tag' => 'note_N経営者のための生成AI']
        ];

        foreach ($feeds as $feed) {
            $this->rssService->fetchAndStore($feed['url'], $feed['tag']);
        }

        $feeds_GAlert = [
            ['url' => 'https://www.google.co.jp/alerts/feeds/04686804727264430900/9134664085846354044', 'tag' => '新規上場'],
            ['url' => 'https://www.google.co.jp/alerts/feeds/04686804727264430900/4453997366422881941', 'tag' => 'サステナビリティ'],
            ['url' => 'https://www.google.co.jp/alerts/feeds/04686804727264430900/8692913570947220025', 'tag' => '内部監査']
        ];

        foreach ($feeds_GAlert as $feed_GAlert) {
            $this->GoogleAlertRssService->fetchAndStore($feed_GAlert['url'], $feed_GAlert['tag']);
        }

        $feeds_Crawl = [
            ['tag' => 'luup']
        ];

        foreach ($feeds_Crawl as $feed_Crawl) {
            $this->CrawlService->fetchAndStore($feed_Crawl['tag']);
        }

        // 既存のDatabaseTagサービスによる処理
        $this->databaseTag->classifyAndStoreLabels();

        // 新規追加：記事要約処理
        $this->summarizeArticles();

        return redirect()->route('rss.index')->with('success', 'RSSフィードの更新が完了しました');
    }

    /**
     * 未要約の記事を取得し、要約を追加する処理
     */
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

    /**
     * Display a listing of the resource.
     */
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
