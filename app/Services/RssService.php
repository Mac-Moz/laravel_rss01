<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FeedItem;
use SimpleXMLElement;
use Exception;
use Illuminate\Database\QueryException;

class RssService
{
    public function fetchAndStore(string $url, string $tagName): void
    {
        try {
            Log::info("Fetching RSS feed from URL: {$url}");

            $response = Http::get($url);
            if ($response->failed()) {
                Log::error("Failed to fetch RSS feed from {$url}. HTTP Status: " . $response->status());
                return;
            }

            $xmlString = $response->body();
            Log::info("Received XML Response (First 500 chars): " . substr($xmlString, 0, 500));

            $xml = @simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
            if ($xml === false) {
                Log::error("Failed to parse RSS XML", ['url' => $url]);
                return;
            }

            Log::info("XML parsed successfully");

            $namespaces = $xml->getNamespaces(true);
            $isRss1 = isset($namespaces['rdf']);
            $isRss2 = isset($xml->channel);

            if ($isRss1) {
                $items = $xml->item;
            } elseif ($isRss2) {
                $items = $xml->channel->item;
            } else {
                Log::error("Unsupported RSS feed format", ['url' => $url]);
                return;
            }

            foreach ($items as $item) {
                Log::info("Processing RSS item", ['item' => json_encode($item)]);

                $title = isset($item->title) ? trim((string) $item->title) : 'タイトルなし';
                $link = isset($item->link) ? trim((string) $item->link) : 'リンクなし';

                $date = null;
                if ($isRss1 && isset($namespaces['dc'])) {
                    $dc = $item->children($namespaces['dc']);
                    $date = isset($dc->date) ? (string) $dc->date : null;
                } elseif ($isRss2 && isset($item->pubDate)) {
                    $date = (string) $item->pubDate;
                }
                $dateFormatted = $date ? date('Y-m-d H:i:s', strtotime($date)) : null;

                // ✅ イメージ取得拡張: media:thumbnail, enclosure, note:creatorImage に対応
                $image = null;
                if (isset($item->enclosure) && isset($item->enclosure['url'])) {
                    $image = (string) $item->enclosure['url'];
                }

                if (empty($image) && isset($namespaces['media'])) {
                    $media = $item->children($namespaces['media']);
                    if (isset($media->thumbnail) && isset($media->thumbnail['url'])) {
                        $image = (string) $media->thumbnail['url'];
                    } elseif (isset($media->thumbnail)) {
                        $image = (string) $media->thumbnail;
                    }
                }

                if (empty($image) && isset($namespaces['note'])) {
                    $note = $item->children($namespaces['note']);
                    if (isset($note->creatorImage)) {
                        $image = (string) $note->creatorImage;
                    }
                }

                Log::debug("Extracted Data", compact('title', 'link', 'dateFormatted', 'image'));

                if (empty($title) || empty($link)) {
                    Log::warning("Skipping item due to missing title or link", compact('title', 'link'));
                    continue;
                }

                if (FeedItem::where('article_link', $link)->exists()) {
                    Log::info("Skipping duplicate item: " . $title);
                    continue;
                }

                $data = [
                    'tag_name' => $tagName,
                    'article_title' => $title ?? 'タイトルなし',
                    'article_date' => $dateFormatted,
                    'article_link' => $link,
                    'article_image' => $image,
                ];
                Log::debug("Saving data to database", $data);

                try {
                    $feedItem = FeedItem::create($data);

                    if ($feedItem) {
                        Log::info("Successfully saved feed item: " . $title);
                    } else {
                        Log::error("Failed to save feed item: " . $title);
                    }
                } catch (QueryException $qe) {
                    Log::error("Database error while saving feed item: " . $qe->getMessage(), [
                        'query' => $qe->getSql(),
                        'bindings' => $qe->getBindings(),
                    ]);
                }
            }

            Log::info("RSS feed processing completed successfully");
        } catch (Exception $e) {
            Log::error("Error while fetching and processing RSS: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
