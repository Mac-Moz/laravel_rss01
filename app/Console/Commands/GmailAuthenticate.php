<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleMailService;
use Illuminate\Support\Facades\Log;

class GmailAuthenticate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gmail-authenticate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gmail API 用の OAuth 認証を実行';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $service = new GoogleMailService();
            $authUrl = $service->getClient()->createAuthUrl();

            $this->info("次の URL にアクセスして認証コードを取得してください:");
            $this->info($authUrl);

            $authCode = trim($this->ask("認証コードを入力してください:"));

            if (empty($authCode)) {
                $this->error("認証コードが入力されていません。もう一度 `php artisan gmail:auth` を実行してください。");
                return;
            }

            // 認証コードを用いてアクセストークンを取得・保存
            $service->storeAccessToken($authCode);

            $this->info("アクセストークンを保存しました。Gmail API を利用できます。");
        } catch (\Exception $e) {
            Log::error("Gmail 認証中にエラーが発生しました: " . $e->getMessage());
            $this->error("認証に失敗しました: " . $e->getMessage());
        }
    }
}
