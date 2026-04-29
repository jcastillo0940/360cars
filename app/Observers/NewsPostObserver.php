<?php

namespace App\Observers;

use App\Jobs\SubmitIndexNowUrls;
use App\Models\NewsPost;

class NewsPostObserver
{
    public function saved(NewsPost $newsPost): void
    {
        if (! $this->isIndexable($newsPost)) {
            return;
        }

        SubmitIndexNowUrls::dispatchAfterResponse([
            route('news.show', $newsPost->slug),
        ]);
    }

    public function deleted(NewsPost $newsPost): void
    {
        if (! $newsPost->slug) {
            return;
        }

        SubmitIndexNowUrls::dispatchAfterResponse([
            route('news.show', $newsPost->slug),
        ]);
    }

    private function isIndexable(NewsPost $newsPost): bool
    {
        return $newsPost->status === 'published'
            && $newsPost->published_at !== null
            && $newsPost->published_at->lte(now());
    }
}
