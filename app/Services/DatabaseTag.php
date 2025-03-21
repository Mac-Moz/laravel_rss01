<?php

namespace App\Services;

use App\Models\FeedItem;
use OpenAI;

class DatabaseTag
{
    private $openai;

    public function __construct()
    {
        $apiKey = config('services.openai.key'); // .env からAPIキーを取得
        $this->openai = OpenAI::client($apiKey); // OpenAIクライアントを作成
    }

    public function classifyAndStoreLabels()
    {
        $feedItems = FeedItem::whereNull('label_audit')->get();

        foreach ($feedItems as $feedItem) {
            $label = $this->getLabelFromChatGPT($feedItem->article_link);

            if ($label) {
                $feedItem->label_audit = $label;
                $feedItem->save();
            }
        }
    }

    private function getLabelFromChatGPT($articleLink)
    {
        $categories = [
            '財務関連資料',
            '業務プロセス資料',
            'コンプライアンス資料',
            'ガバナンス・経営資料',
            '人事・労務資料',
            'IT・AI・情報セキュリティ資料',
            '外部監査・第三者評価資料',
            'ESG・サステナビリティ',
            'その他参考資料'
        ];

        $prompt = "次のURLの内容を推測し、以下の分類ラベルの中から最も適切なものに分類してください。\n\n"
            . "【分類ラベル一覧】\n" . implode("\n", $categories) . "\n\n"
            . "【記事URL】\n{$articleLink}\n\n"
            . "最も適切なラベルのみを出力してください。"
            . "分類ができない場合は「その他参考資料」としてください。";

        $response = $this->openai->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'あなたは文章を分類するAIです。'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 10,
            'temperature' => 0.2,
        ]);

        return trim($response['choices'][0]['message']['content']);
    }
}
