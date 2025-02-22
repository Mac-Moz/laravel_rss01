<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ArticleSummary
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key'); // 環境変数からAPIキーを取得
    }

    public function summarize($title, $url)
    {
        $prompt = "以下のサイトから {$title} に関する内容の要約を作成してください。要約した記事のタイトル、記事内容のサマリ、記事のキーワードを記載してください: {$url}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'] ?? 'エラー: 要約を取得できませんでした。';
        }

        return 'エラー: APIリクエストが失敗しました。';
    }
}
