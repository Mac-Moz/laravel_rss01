<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ArticleSummary;
use App\Services\ArticleSummaryTerm;

class ArticleController extends Controller
{
    private $articleSummary;
    private $summaryService;

    public function __construct(ArticleSummary $articleSummary, ArticleSummaryTerm $summaryService)
    {
        $this->articleSummary = $articleSummary;
        $this->summaryService = $summaryService;
    }

    /**
     * 選択した記事を個別に要約
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function summarize(Request $request)
    {
        $request->validate([
            'articles' => 'required|array',
        ]);

        $articles = collect($request->input('articles'))->map(function ($articleJson) {
            return json_decode($articleJson, true);
        });

        $summaries = $articles->map(function ($article) {
            return [
                'id' => $article['id'],
                'date' => $article['date'],
                'link' => $article['link'],
                'image' => $article['image'],
                'title' => $article['title'],
                'summary' => $this->articleSummary->summarize($article['title'], $article['link']),
            ];
        });

        return view('article.summary', ['summaries' => $summaries]);
    }

    /**
     * 指定した期間のフィードアイテムを取得し、要約を実行
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function summarizeByTerm(Request $request)
    {
        // バリデーション
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // 要約実行
        $summary = $this->summaryService->summarizeByTerm($request->start_date, $request->end_date);

        // 要約結果を表示
        return view('article.summary_term', ['summary' => $summary]);
    }
}
