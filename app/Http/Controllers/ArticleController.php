<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ArticleSummary;

class ArticleController extends Controller
{
    private $articleSummary;

    public function __construct(ArticleSummary $articleSummary)
    {
        $this->articleSummary = $articleSummary;
    }

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
}
