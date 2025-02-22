<?php

namespace App\Services;

use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Models\FeedItem;

class GoogleMailService
{
    protected $client;

    public function getClient()
    {
        return $this->client;
    }

    
    protected $service;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('Laravel Gmail API');
        $this->client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
        $this->client->setAuthConfig(storage_path('app/client_secret_164118436686-0ud6ae43l929h6fmdti6g74o03oms6v5.apps.googleusercontent.com.json'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        $this->loadAccessToken();
        $this->service = new Google_Service_Gmail($this->client);
    }

    public function createAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    private function loadAccessToken()
    {
        $tokenPath = storage_path('app/token.json');

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        } else {
            Log::error("token.json が存在しません。`php artisan gmail:auth` を実行してください。");
            throw new \Exception("token.json が見つかりません。`php artisan gmail:auth` を実行してください。");
        }

        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                Log::info("アクセストークンの期限切れ。リフレッシュトークンを使用して更新中...");
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());

                if (!isset($newToken['access_token'])) {
                    throw new \Exception("リフレッシュトークンが無効です。再認証が必要です。`php artisan gmail:auth` を実行してください。");
                }

                file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
                Log::info("アクセストークンを更新しました。");
            } else {
                Log::error("リフレッシュトークンがありません。`php artisan gmail:auth` を再実行してください。");
                throw new \Exception("OAuth2 認証が必要です。`php artisan gmail:auth` を実行してください。");
            }
        }
    }

    public function storeAccessToken($authCode)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

        if (isset($accessToken['error'])) {
            throw new \Exception("認証エラー: " . $accessToken['error_description']);
        }

        // **リフレッシュトークンが取得できているか確認**
        if (!isset($accessToken['refresh_token'])) {
            throw new \Exception("リフレッシュトークンが取得できませんでした。Google Cloud Console で「デスクトップアプリ」用の OAuth クライアントを作成してください。");
        }

        $tokenPath = storage_path('app/token.json');

        // **`token.json` を作成する**
        file_put_contents($tokenPath, json_encode($accessToken, JSON_PRETTY_PRINT));

        Log::info("アクセストークンを保存しました: " . json_encode($accessToken));
    }

    public function fetchAndStoreMessages($query = 'is:unread')
    {
        $user = 'me';
        $messages = $this->service->users_messages->listUsersMessages($user, ['q' => $query]);

        if (!$messages->getMessages()) {
            Log::info("No new messages found.");
            return;
        }

        foreach ($messages->getMessages() as $message) {
            $emailData = $this->service->users_messages->get($user, $message->getId());
            $formattedMessage = $this->formatMessage($emailData);

            Log::debug("Extracted Data", $formattedMessage);

            if (FeedItem::where('article_link', $formattedMessage['id'])->exists()) {
                Log::info("Skipping duplicate entry: " . $formattedMessage['subject']);
                continue;
            }

            $data = [
                'tag_name' => 'Gmail',
                'article_title' => $formattedMessage['subject'],
                'article_date' => $formattedMessage['date'],
                'article_link' => $formattedMessage['id'],
                'article_content' => $formattedMessage['body'],
            ];

            Log::debug("Saving data to database", $data);

            try {
                $feedItem = FeedItem::create($data);
                if ($feedItem) {
                    Log::info("Successfully saved Gmail entry: " . $formattedMessage['subject']);
                } else {
                    Log::error("Failed to save Gmail entry: " . $formattedMessage['subject']);
                }
            } catch (QueryException $qe) {
                Log::error("Database error while saving Gmail entry: " . $qe->getMessage(), [
                    'query' => $qe->getSql(),
                    'bindings' => $qe->getBindings(),
                ]);
            }
        }

        Log::info("Gmail processing completed successfully");
    }

    private function formatMessage(Google_Service_Gmail_Message $message)
    {
        $headers = $message->getPayload()->getHeaders();
        $body = $message->getPayload()->getBody();

        $formattedMessage = [
            'id' => $message->getId(),
            'snippet' => $message->getSnippet(),
            'subject' => $this->getHeader($headers, 'Subject'),
            'from' => $this->getHeader($headers, 'From'),
            'date' => $this->getHeader($headers, 'Date'),
            'body' => base64_decode(strtr($body->getData() ?? '', '-_', '+/')),
        ];

        return $formattedMessage;
    }

    private function getHeader($headers, $name)
    {
        foreach ($headers as $header) {
            if ($header->getName() === $name) {
                return $header->getValue();
            }
        }
        return null;
    }
}
