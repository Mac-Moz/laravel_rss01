<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FeedItem;
use SimpleXMLElement;
use Exception;
use Illuminate\Database\QueryException;

class GoogleAlertRssService
{
    public function fetchAndStore(string $url, string $tagName): void
    {
        try {
            Log::info("Fetching Atom feed from URL: {$url}");

            $response = Http::get($url);
            if ($response->failed()) {
                Log::error("Failed to fetch Atom feed from {$url}. HTTP Status: " . $response->status());
                return;
            }

            $xmlString = $response->body();
            Log::info("Received XML Response (First 500 chars): " . substr($xmlString, 0, 500));

            // XMLを安全に解析
            $xml = @simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
            if ($xml === false) {
                Log::error("Failed to parse Atom XML", ['url' => $url]);
                return;
            }

            Log::info("XML parsed successfully");

            // Atomの `entry` 要素を取得
            $items = $xml->entry ?? [];

            if (empty($items)) {
                Log::error("No valid Atom entries found", ['url' => $url, 'xml' => $xmlString]);
                return;
            }

            // Atom エントリの処理
            foreach ($items as $item) {
                Log::info("Processing Atom entry", ['item' => json_encode($item)]);

                $title = isset($item->title) ? trim((string) $item->title) : 'タイトルなし';
                $link = isset($item->link['href']) ? trim((string) $item->link['href']) : 'リンクなし';
                $date = isset($item->published) ? (string) $item->published : null;
                $dateFormatted = $date ? date('Y-m-d H:i:s', strtotime($date)) : '1970-01-01 00:00:00';
                $content = isset($item->content) ? strip_tags((string) $item->content) : '';

                // データの確認ログ
                Log::debug("Extracted Data", compact('title', 'link', 'dateFormatted', 'content'));

                if (empty($title) || empty($link)) {
                    Log::warning("Skipping entry due to missing title or link", compact('title', 'link'));
                    continue;
                }

                if (FeedItem::where('article_link', $link)->exists()) {
                    Log::info("Skipping duplicate entry: " . $title);
                    continue;
                }

                $data = [
                    'tag_name' => $tagName,
                    'article_title' => $title,
                    'article_date' => $dateFormatted,
                    'article_link' => $link,
                    'article_content' => $content,
                ];
                Log::debug("Saving data to database", $data);

                try {
                    $feedItem = FeedItem::create($data);
                    if ($feedItem) {
                        Log::info("Successfully saved Atom entry: " . $title);
                    } else {
                        Log::error("Failed to save Atom entry: " . $title);
                    }
                } catch (QueryException $qe) {
                    Log::error("Database error while saving Atom entry: " . $qe->getMessage(), [
                        'query' => $qe->getSql(),
                        'bindings' => $qe->getBindings(),
                    ]);
                }
            }

            Log::info("Atom feed processing completed successfully");

        } catch (Exception $e) {
            Log::error("Error while fetching and processing Atom feed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
