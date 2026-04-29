<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Models\Vehicle;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        return response()
            ->view('sitemaps.index', [
                'items' => [
                    route('sitemap.vehicles'),
                    route('sitemap.news'),
                    route('home'),
                    route('catalog.index'),
                    route('brands.index'),
                    route('valuation.index'),
                    route('seller.onboarding.create'),
                ],
            ])
            ->header('Content-Type', 'application/xml');
    }

    public function vehicles(): Response
    {
        $vehicles = Vehicle::query()
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->latest('updated_at')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemaps.vehicles', [
                'items' => $vehicles,
            ])
            ->header('Content-Type', 'application/xml');
    }

    public function news(): Response
    {
        $posts = NewsPost::query()
            ->published()
            ->latest('updated_at')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemaps.news', [
                'items' => $posts,
            ])
            ->header('Content-Type', 'application/xml');
    }
}
