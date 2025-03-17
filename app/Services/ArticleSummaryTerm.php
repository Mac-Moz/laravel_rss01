<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\FeedItem;
use Carbon\Carbon;

class ArticleSummaryTerm
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key'); // 環境変数からAPIキーを取得
    }

    /**
     * 指定した期間のフィードアイテムを取得し、要約を作成
     *
     * @param string $startDate 開始日 (YYYY-MM-DD)
     * @param string $endDate 終了日 (YYYY-MM-DD)
     * @return string
     */
    public function summarizeByTerm($startDate, $endDate)
    {
        // 指定期間のデータを取得
        $feedItems = FeedItem::whereBetween('article_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ])->get();

        if ($feedItems->isEmpty()) {
            return '指定された期間のフィードアイテムはありません。';
        }

        // 各記事のタイトルとURLを使用してプロンプトを生成
        $prompts = $feedItems->map(function ($item) {
            return "以下のサイトの記事を要約してください。\n"
                . "記事のタイトル: {$item->article_title}\n"
                . "記事のURL: {$item->article_link}\n"
                . "要約する際には、記事のタイトルとURLを含めた形式で出力してください。\n"
                . "要約には、記事の概要、重要なポイント、およびキーワードを記載してください。";
        })->implode("\n\n");

        // OpenAI APIリクエスト
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompts]],
            'max_tokens' => 1500,
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            // GPTのレスポンスを取得し、記事ごとにフォーマット
            $summaryContent = $response->json()['choices'][0]['message']['content'] ?? null;
            if (!$summaryContent) {
                return 'エラー: 要約を取得できませんでした。';
            }

            // 記事ごとのタイトル・URLを含めたフォーマットで整形
            $formattedSummaries = $feedItems->map(function ($item, $index) use ($summaryContent) {
                return "【記事タイトル】 {$item->article_title}\n"
                    . "【URL】 {$item->article_link}\n"
                    . "【要約】\n" . $summaryContent;
            })->implode("\n\n");

            return $formattedSummaries;
        }

        return 'エラー: APIリクエストが失敗しました。';
    }
}
