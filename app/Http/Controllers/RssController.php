<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedItem;
use App\Services\RssService;
use App\Services\GoogleAlertRssService;
use App\Services\CrawlService;
use App\Services\DatabaseTag; // 追加
// use App\Services\GoogleMailService;
use function Laravel\Prompts\alert;

class RssController extends Controller
{
    protected $rssService;
    protected $GoogleAlertRssService;
    protected $CrawlService;
    protected $databaseTag; // 追加

    public function __construct(
        RssService $rssService,
        GoogleAlertRssService $GoogleAlertRssService,
        CrawlService $CrawlService,
        DatabaseTag $databaseTag // 追加
        // GoogleMailService $GoogleMailService
    ) {
        $this->rssService = $rssService;
        $this->GoogleAlertRssService = $GoogleAlertRssService;
        $this->CrawlService = $CrawlService;
        $this->databaseTag = $databaseTag; // 追加
        // $this->GoogleMailService = $GoogleMailService;
    }

    public function fetch()
    {
        // 取得するRSSフィードのリスト
        $feeds = [
            ['url' => 'https://www.fsa.go.jp/fsaNewsListAll_rss2.xml', 'tag' => '金融庁'],
            ['url' => 'https://note.com/yoruo_hanada/rss', 'tag' => '花田宏造税理士事務所'],
            ['url' => 'https://note.com/kazuaki_mizuchi/rss', 'tag' => 'note_水地一彰'],
            ['url' => 'https://chatgpt-lab.com/rss', 'tag' => 'note_ChatGPT研究所'],
            ['url' => 'https://note.com/ndot_man/rss', 'tag' => 'note_N経営者のための生成AI']
        ];

        foreach ($feeds as $feed) {
            $this->rssService->fetchAndStore($feed['url'], $feed['tag']);
        }

        // 取得するRSSフィードのリスト
        $feeds_GAlert = [
            ['url' => 'https://www.google.co.jp/alerts/feeds/04686804727264430900/9134664085846354044', 'tag' => '新規上場'],
            ['url' => 'https://www.google.co.jp/alerts/feeds/04686804727264430900/4453997366422881941', 'tag' => 'サステナビリティ'],
            ['url' => 'https://www.google.co.jp/alerts/feeds/04686804727264430900/8692913570947220025', 'tag' => '内部監査']
        ];

        foreach ($feeds_GAlert as $feed_GAlert) {
            $this->GoogleAlertRssService->fetchAndStore($feed_GAlert['url'], $feed_GAlert['tag']);
        }

        // 取得するRSSフィードのリスト
        $feeds_Crawl = [
            ['tag' => 'luup']
        ];

        foreach ($feeds_Crawl as $feed_Crawl) {
            $this->CrawlService->fetchAndStore($feed_Crawl['tag']);
        }

        // DatabaseTagサービスを実行し、label_audit を自動分類
        $this->databaseTag->classifyAndStoreLabels();

        return redirect()->route('rss.index')->with('success', 'RSSフィードの更新が完了しました');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tag = $request->query('tag');

        // 全てのタグ一覧を取得
        $tags = FeedItem::distinct()->pluck('tag_name');

        // 選択されたタグの記事のみ取得
        $feedItems = FeedItem::when($tag, function ($query, $tag) {
            return $query->where('tag_name', $tag);
        })->orderBy('article_date', 'desc')->paginate(50);

        return view('rss.index', compact('feedItems', 'tags'));
    }
}
