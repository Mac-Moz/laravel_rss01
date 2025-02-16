<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FeedItem;
use SimpleXMLElement;
use Exception;
use Illuminate\Database\QueryException;

class CrawlService
{
    public function fetchAndStore(string $tagName): void
    {
        try {
            // 環境変数からAPI情報を取得
            $apiKey = env('SCRAPY_API_KEY');
            $projectId = env('SCRAPY_PROJECT_ID');
            $jobId = env('SCRAPY_JOB_ID');

            // APIエンドポイント
            $url = "https://storage.scrapinghub.com/items/{$projectId}/{$jobId}";

            Log::info("Fetching data from Scrapy Cloud API: {$url}");

            // HTTPリクエスト
            $response = Http::withBasicAuth($apiKey, '')
                ->timeout(30)
                ->get($url);

            if ($response->failed()) {
                Log::error("Failed to fetch data. HTTP Code: " . $response->status());
                return;
            }

            $rawData = $response->body();

            // スペース区切りの文字列をJSON配列形式に変換
            $jsonString = '[' . str_replace('} {', '}, {', $rawData) . ']';
            $jsonString = str_replace('}', '},', $jsonString);
            $jsonString = preg_replace('/,\s*\]/', ']', $jsonString);
            $dataArray = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON decode error: " . json_last_error_msg());
                return;
            }

            Log::info("Data successfully fetched and decoded", ['data' => $dataArray]);

            // データをデータベースに保存
            foreach ($dataArray as $item) {
                $this->storeData($tagName, $item);
            }

            Log::info("Data insertion completed.");

        } catch (Exception $e) {
            Log::error("Error in fetchAndStore: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function storeData(string $tagName, array $item): void
    {
        try {
            // 重複チェック
            if (FeedItem::where('article_link', $item['link'])->exists()) {
                Log::info("Skipping duplicate item: " . $item['title']);
                return;
            }

            // データ保存
            FeedItem::create([
                'tag_name' => $tagName ?? null,
                'article_title' => $item['title'] ?? null,
                'article_date' => $item['date'] ?? null,
                'article_link' => $item['link'] ?? null,
                'article_image' => $item['image'] ?? null,
            ]);

            Log::info("Saved item: " . $item['title']);

        } catch (Exception $e) {
            Log::error("Database error while saving item: " . $e->getMessage());
        }
    }
}
