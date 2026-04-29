<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Seo\IndexNowService;
use Illuminate\Http\Response;

class IndexNowController extends Controller
{
    public function __invoke(IndexNowService $indexNowService): Response
    {
        $key = $indexNowService->key();

        abort_if($key === '', 404);

        return response($key, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
