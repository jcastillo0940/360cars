<?php

namespace App\Jobs;

use App\Services\Seo\IndexNowService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class SubmitIndexNowUrls
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $urls
     */
    public function __construct(
        public array $urls,
    ) {
    }

    public function handle(IndexNowService $indexNowService): void
    {
        $indexNowService->submit($this->urls);
    }
}
