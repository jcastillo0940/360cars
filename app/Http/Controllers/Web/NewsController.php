<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Services\Valuation\ValuationSettingsService;
use App\Services\Seo\SeoService;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function __construct(
        private readonly SeoService $seoService,
        private readonly ValuationSettingsService $valuationSettings,
    ) {
    }

    public function index(): View
    {
        $posts = NewsPost::query()
            ->with('author')
            ->published()
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(9)
            ->through(fn (NewsPost $post) => $this->mapPost($post));

        return view('news.index', [
            'newsProps' => $this->baseProps() + [
                'posts' => [
                    'data' => $posts->items(),
                    'meta' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'total' => $posts->total(),
                    ],
                ],
            ],
            'seoData' => $this->seoService->forNewsIndex(request()),
        ]);
    }

    public function show(NewsPost $newsPost): View
    {
        abort_unless($newsPost->status === 'published' && $newsPost->published_at && $newsPost->published_at->lte(now()), 404);

        $related = NewsPost::query()
            ->published()
            ->whereKeyNot($newsPost->getKey())
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->take(3)
            ->get()
            ->map(fn (NewsPost $post) => $this->mapPost($post))
            ->values();

        return view('news.show', [
            'newsProps' => $this->baseProps() + [
                'post' => $this->mapPost($newsPost->loadMissing('author'), true),
                'relatedPosts' => $related,
            ],
            'seoData' => $this->seoService->forNewsPost($newsPost, request()),
        ]);
    }

    private function baseProps(): array
    {
        $accountUrl = auth()->check()
            ? (auth()->user()->hasRole('admin')
                ? route('admin.dashboard')
                : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')))
            : route('login');

        $sellUrl = route('seller.onboarding.create');

        return [
            'homeUrl' => route('home'),
            'catalogUrl' => route('catalog.index'),
            'brandsUrl' => route('brands.index'),
            'newsUrl' => route('news.index'),
            'valuationUrl' => route('valuation.index'),
            'sellUrl' => $sellUrl,
            'accountUrl' => $accountUrl,
            'loginUrl' => route('login'),
            'authUser' => auth()->check() ? [
                'authenticated' => true,
                'firstName' => trim(strtok((string) auth()->user()->name, ' ')) ?: 'Cuenta',
                'dashboardUrl' => $accountUrl,
                'buyerUrl' => route('buyer.dashboard'),
            ] : [
                'authenticated' => false,
            ],
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
            'footerLinks' => [
                'termsUrl' => route('legal.terms'),
                'privacyUrl' => route('legal.privacy'),
                'cookiesUrl' => route('legal.cookies'),
            ],
        ];
    }

    private function mapPost(NewsPost $post, bool $full = false): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $full ? $post->content : null,
            'content_html' => $full ? nl2br(e($post->content)) : null,
            'cover_image_url' => $post->cover_image_url,
            'is_featured' => (bool) $post->is_featured,
            'published_label' => optional($post->published_at)->diffForHumans() ?? 'Próximamente',
            'published_at' => optional($post->published_at)?->format('d/m/Y'),
            'author_name' => $post->author?->name ?: 'Equipo Movikaa',
            'url' => route('news.show', $post->slug),
        ];
    }
}
