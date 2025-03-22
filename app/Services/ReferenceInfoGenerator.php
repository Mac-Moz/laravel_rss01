<?php

namespace App\Services;

use App\Models\AuditItem;
use App\Models\FeedItem;
use OpenAI;

class ReferenceInfoGenerator
{
    public function generate(AuditItem $item): ?string
    {
        // 最新のFeedItemを取得（title, link, label）
        $feedItems = FeedItem::orderBy('updated_at', 'desc')
            ->limit(20)
            ->get(['article_title', 'article_link', 'label_audit']);

        // コンテキストを整形
        $context = $feedItems->map(function ($feed) {
            return "■ タイトル: {$feed->article_title}\n"
                . "リンク: {$feed->article_link}\n"
                . "ラベル: {$feed->label_audit}";
        })->implode("\n---\n");

        // プロンプト生成
        $prompt = "以下は企業に関する最近の記事情報です。\n\n"
            . "監査内容: 「{$item->content}」に最も関連する情報を最大3つ探し、"
            . "内部監査に有用な観点で要約してください。\n"
            . "該当なしとは回答しないでください\n"
            . "【記事一覧】\n" . $context;

        // OpenAI API 呼び出し
        $client = OpenAI::client(config('services.openai.key'));

        $response = $client->chat()->create([
            'model' => 'gpt-4-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'あなたは企業の内部監査を支援するAIです。与えられた監査項目に関連する情報があれば要約し、なければ「該当なし」と答えてください。'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.4,
            'max_tokens' => 500,
        ]);

        return trim($response['choices'][0]['message']['content'] ?? '');
    }
}
