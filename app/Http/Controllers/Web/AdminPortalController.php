<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFeatureOption;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Services\Billing\BillingService;
use App\Services\Currency\ExchangeRateService;
use App\Services\Valuation\ValuationSettingsService;
use App\Services\Valuation\VehicleValuationAiNarrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPortalController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly ValuationSettingsService $valuationSettings,
        private readonly VehicleValuationAiNarrator $valuationAiNarrator,
        private readonly BillingService $billingService,
    ) {
    }

    public function index(Request $request)
    {
        $selectedYear = (int) $request->input('year', now()->year);
        $selectedMake = $request->input('make_id');
        
        $data = $this->sharedData($request);
        $latestTransactions = Transaction::query()->with(['user', 'plan'])->latest()->take(6)->get();
        
        // Revenue Trend by Month for the selected year
        $paymentTrend = collect(range(1, 12))->map(function ($month) use ($selectedYear) {
            return [
                'label' => now()->month($month)->translatedFormat('M'),
                'value' => (float) Transaction::query()
                    ->where('status', 'paid')
                    ->whereYear('created_at', $selectedYear)
                    ->whereMonth('created_at', $month)
                    ->sum('amount'),
            ];
        });

        // Inventory Growth
        $inventoryTrend = collect(range(1, 12))->map(function ($month) use ($selectedYear, $selectedMake) {
            return [
                'label' => now()->month($month)->translatedFormat('M'),
                'value' => Vehicle::query()
                    ->whereYear('created_at', $selectedYear)
                    ->whereMonth('created_at', $month)
                    ->when($selectedMake, fn($q) => $q->where('vehicle_make_id', $selectedMake))
                    ->count(),
            ];
        });

        // Top Brands distribution
        $topBrands = Vehicle::query()
            ->selectRaw('vehicle_make_id, count(*) as count')
            ->groupBy('vehicle_make_id')
            ->with('make')
            ->orderByDesc('count')
            ->take(5)
            ->get()
            ->map(fn($v) => [
                'label' => $v->make?->name ?? 'Otros',
                'count' => $v->count,
                'percentage' => $data['vehicleCount'] > 0 ? round(($v->count / $data['vehicleCount']) * 100) : 0
            ]);

        $paymentMax = max(1, $paymentTrend->max('value'));
        $inventoryMax = max(1, $inventoryTrend->max('value'));

        return view('portal.admin.overview', $data + [
            'latestTransactions' => $latestTransactions,
            'topBrands' => $topBrands,
            'selectedYear' => $selectedYear,
            'selectedMake' => $selectedMake,
            'paymentTrendChart' => $paymentTrend->map(fn ($item) => $item + ['height' => max(1, (int) round(($item['value'] / $paymentMax) * 100))]),
            'inventoryTrendChart' => $inventoryTrend->map(fn ($item) => $item + ['height' => max(1, (int) round(($item['value'] / $inventoryMax) * 100))]),
            'availableYears' => range(now()->year, now()->year - 3),
        ]);
    }

    public function catalog(Request $request)
    {
        $search = trim($request->string('q')->toString());
        $catalogMakes = VehicleMake::query()
            ->with(['models' => fn ($query) => $query->orderBy('name')])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhereHas('models', fn ($models) => $models->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->get();

        return view('portal.admin.catalog', $this->sharedData($request) + [
            'catalogMakes' => $catalogMakes,
            'catalogStats' => [
                'makes_total' => $catalogMakes->count(),
                'makes_active' => $catalogMakes->where('is_active', true)->count(),
                'models_total' => $catalogMakes->sum(fn (VehicleMake $make) => $make->models->count()),
                'models_active' => $catalogMakes->sum(fn (VehicleMake $make) => $make->models->where('is_active', true)->count()),
            ],
            'catalogSearch' => $search,
        ]);
    }

    public function payments(Request $request)
    {
        $filters = [
            'q' => trim($request->string('q')->toString()),
            'status' => $request->string('status')->toString(),
            'provider' => $request->string('provider')->toString(),
        ];

        $latestTransactions = Transaction::query()
            ->with(['user', 'plan', 'payable'])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $query->where(function ($builder) use ($filters): void {
                    $builder->where('external_reference', 'like', '%'.$filters['q'].'%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%'.$filters['q'].'%'));
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['provider'] !== '', fn ($query) => $query->where('provider', $filters['provider']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $activeSubscriptions = Subscription::query()
            ->with(['user', 'plan'])
            ->where('status', 'active')
            ->latest()
            ->paginate(12, ['*'], 'subscriptions_page')
            ->withQueryString();

        return view('portal.admin.payments', $this->sharedData($request) + [
            'latestTransactions' => $latestTransactions,
            'activeSubscriptions' => $activeSubscriptions,
            'paymentFilters' => $filters,
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function users(Request $request)
    {
        $filters = [
            'q' => trim($request->string('q')->toString()),
            'role' => $request->string('role')->toString(),
        ];

        $latestUsers = User::query()
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $query->where(function ($builder) use ($filters): void {
                    $builder->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('email', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['role'] !== '', fn ($query) => $query->where('account_type', $filters['role']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $latestVehicles = Vehicle::query()
            ->with(['owner', 'make', 'model'])
            ->latest()
            ->paginate(12, ['*'], 'vehicles_page')
            ->withQueryString();

        return view('portal.admin.users', $this->sharedData($request) + [
            'latestUsers' => $latestUsers,
            'latestVehicles' => $latestVehicles,
            'userFilters' => $filters,
        ]);
    }

    public function settings(Request $request)
    {
        return view('portal.admin.settings', $this->sharedData($request) + [
            'paymentMethods' => $this->paymentMethods(),
        ]);
    }

    public function features(Request $request)
    {
        $featureOptions = VehicleFeatureOption::query()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('portal.admin.features', $this->sharedData($request) + [
            'featureOptions' => $featureOptions,
            'featureCategories' => $featureOptions->pluck('category')->filter()->unique()->values(),
            'featureStats' => [
                'total' => $featureOptions->count(),
                'active' => $featureOptions->where('is_active', true)->count(),
                'categories' => $featureOptions->pluck('category')->filter()->unique()->count(),
            ],
        ]);
    }

    public function plans(Request $request)
    {
        $plans = Plan::query()->orderBy('price')->get();

        return view('portal.admin.plans', $this->sharedData($request) + [
            'plans' => $plans,
            'planStats' => [
                'total' => $plans->count(),
                'active' => $plans->where('is_active', true)->count(),
                'paid' => $plans->where('price', '>', 0)->count(),
            ],
        ]);
    }

    public function news(Request $request)
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();

        $posts = NewsPost::query()
            ->with('author')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('excerpt', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('portal.admin.news.index', $this->sharedData($request) + [
            'newsPosts' => $posts,
            'newsFilters' => [
                'q' => $search,
                'status' => $status,
            ],
            'newsStats' => [
                'total' => NewsPost::query()->count(),
                'published' => NewsPost::query()->where('status', 'published')->count(),
                'featured' => NewsPost::query()->where('is_featured', true)->count(),
            ],
        ]);
    }

    public function createNews(Request $request)
    {
        return view('portal.admin.news.create', $this->sharedData($request));
    }

    public function storeNews(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:news_posts,slug'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string'],
        ]);

        $post = NewsPost::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'slug' => $this->uniqueNewsSlug($data['slug'] ?? $data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'cover_image_url' => $this->storeNewsCoverImage($request->file('cover_image')),
            'status' => $data['status'],
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'published_at' => $data['status'] === 'published' ? ($data['published_at'] ?? now()) : null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        return redirect()->to(route('admin.news.edit', $post).'#editor')->with('status', 'Articulo creado correctamente.');
    }

    public function editNews(Request $request, NewsPost $newsPost)
    {
        return view('portal.admin.news.edit', $this->sharedData($request) + [
            'newsPost' => $newsPost,
        ]);
    }

    public function updateNews(Request $request, NewsPost $newsPost): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('news_posts', 'slug')->ignore($newsPost->id)],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string'],
        ]);

        $publishedAt = $data['status'] === 'published'
            ? ($data['published_at'] ?? $newsPost->published_at ?? now())
            : null;

        $coverImageUrl = $newsPost->cover_image_url;

        if ($request->boolean('remove_cover_image')) {
            $this->deleteNewsCoverImage($coverImageUrl);
            $coverImageUrl = null;
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteNewsCoverImage($coverImageUrl);
            $coverImageUrl = $this->storeNewsCoverImage($request->file('cover_image'));
        }

        $newsPost->update([
            'title' => $data['title'],
            'slug' => $this->uniqueNewsSlug($data['slug'] ?? $data['title'], $newsPost->id),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'cover_image_url' => $coverImageUrl,
            'status' => $data['status'],
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'published_at' => $publishedAt,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        return redirect()->to(route('admin.news.edit', $newsPost).'#editor')->with('status', 'Articulo actualizado correctamente.');
    }

    public function destroyNews(NewsPost $newsPost): RedirectResponse
    {
        $this->deleteNewsCoverImage($newsPost->cover_image_url);
        $newsPost->delete();

        return redirect()->to(route('admin.news').'#news-list')->with('status', 'Articulo eliminado correctamente.');
    }


    private function storeNewsCoverImage(?UploadedFile $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $disk = Storage::disk('public');
        $directory = 'news/covers';
        $baseName = (string) Str::uuid();
        $binary = $file->get();

        if ($binary === false) {
            return null;
        }

        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $source = @imagecreatefromstring($binary);

            if ($source) {
                $width = imagesx($source);
                $height = imagesy($source);
                $targetWidth = min($width, 1600);
                $targetHeight = $width > 0 ? (int) round(($height / $width) * $targetWidth) : $height;
                $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
                imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

                ob_start();
                imagewebp($canvas, null, (int) config('media.webp_quality', 82));
                $optimized = (string) ob_get_clean();
                $path = $directory.'/'.$baseName.'.webp';
                $disk->put($path, $optimized, 'public');

                imagedestroy($source);
                imagedestroy($canvas);

                return $disk->url($path);
            }
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $disk->putFileAs($directory, $file, $baseName.'.'.$extension, 'public');

        return $path ? $disk->url($path) : null;
    }

    private function deleteNewsCoverImage(?string $coverImageUrl): void
    {
        if (! is_string($coverImageUrl) || $coverImageUrl === '') {
            return;
        }

        $storagePrefix = Storage::disk('public')->url('');
        $path = str_starts_with($coverImageUrl, $storagePrefix)
            ? ltrim(Str::after($coverImageUrl, $storagePrefix), '/')
            : ltrim($coverImageUrl, '/');

        if (str_starts_with($path, 'news/covers/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function uniqueNewsSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'articulo';
        $slug = $base;
        $suffix = 2;

        while (NewsPost::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function sharedData(Request $request): array
    {
        $catalogMakes = VehicleMake::query()
            ->with(['models' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return [
            'gmv' => (float) Transaction::query()->where('status', 'paid')->sum('amount'),
            'newUsers' => User::query()->whereDate('created_at', today())->count(),
            'pendingModeration' => Vehicle::query()->whereIn('status', ['draft', 'paused'])->count(),
            'vehicleCount' => Vehicle::query()->count(),
            'publishedVehicleCount' => Vehicle::query()->where('status', 'published')->count(),
            'leadCount' => (int) Vehicle::query()->sum('lead_count'),
            'paidTransactionsCount' => Transaction::query()->where('status', 'paid')->count(),
            'catalogMakes' => $catalogMakes,
            'exchangeQuote' => $this->exchangeRateService->latest(),
            'valuationAiEnabled' => $this->valuationSettings->valuationAiEnabled(),
            'valuationAiConfigured' => $this->valuationAiNarrator->configured(),
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
        ];
    }

    public function refreshExchangeRate(): RedirectResponse
    {
        $quote = $this->exchangeRateService->refresh();

        if (! data_get($quote, 'usd_to_crc')) {
            return redirect()->to(route('admin.settings').'#exchange-rate')->with('status', 'No fue posible actualizar el tipo de cambio en este momento.');
        }

        return redirect()->to(route('admin.settings').'#exchange-rate')->with('status', 'Tipo de cambio actualizado correctamente.');
    }

    public function storeCatalogMake(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('vehicle_makes', 'name')],
        ]);

        VehicleMake::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'is_active' => true,
        ]);

        return redirect()->to(route('admin.catalog').'#catalog-create')->with('status', 'Marca creada correctamente.');
    }

    public function storeCatalogEntry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'make_name' => ['required', 'string', 'max:100', Rule::unique('vehicle_makes', 'name')],
            'model_name' => ['nullable', 'string', 'max:100'],
        ]);

        $make = VehicleMake::create([
            'name' => $data['make_name'],
            'slug' => Str::slug($data['make_name']),
            'is_active' => true,
        ]);

        if (! empty($data['model_name'])) {
            VehicleModel::create([
                'vehicle_make_id' => $make->id,
                'name' => $data['model_name'],
                'slug' => Str::slug($data['model_name']),
                'is_active' => true,
            ]);
        }

        return redirect()->to(route('admin.catalog').'#catalog-list')->with('status', empty($data['model_name']) ? 'Marca creada correctamente.' : 'Marca y modelo creados correctamente.');
    }


    public function toggleCatalogMake(VehicleMake $vehicleMake): RedirectResponse
    {
        $nextState = ! $vehicleMake->is_active;

        $vehicleMake->update([
            'is_active' => $nextState,
        ]);

        if (! $nextState) {
            $vehicleMake->models()->update(['is_active' => false]);
        }

        return redirect()->to(route('admin.catalog').'#catalog-list')->with('status', 'Estado de la marca actualizado.');
    }

    public function storeCatalogModel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_make_id' => ['required', 'integer', Rule::exists('vehicle_makes', 'id')],
            'name' => ['required', 'string', 'max:100'],
        ]);

        $make = VehicleMake::query()->findOrFail($data['vehicle_make_id']);

        $request->validate([
            'name' => [
                Rule::unique('vehicle_models', 'name')->where(fn ($query) => $query->where('vehicle_make_id', $make->id)),
            ],
        ]);

        VehicleModel::create([
            'vehicle_make_id' => $make->id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'is_active' => (bool) $make->is_active,
        ]);

        return redirect()->to(route('admin.catalog').'#catalog-create')->with('status', 'Modelo creado correctamente.');
    }

    public function toggleCatalogModel(VehicleModel $vehicleModel): RedirectResponse
    {
        if (! $vehicleModel->is_active && ! $vehicleModel->make?->is_active) {
            return redirect()->to(route('admin.catalog').'#catalog-list')->with('status', 'Activa primero la marca antes de habilitar este modelo.');
        }

        $vehicleModel->update([
            'is_active' => ! $vehicleModel->is_active,
        ]);

        return redirect()->to(route('admin.catalog').'#catalog-list')->with('status', 'Estado del modelo actualizado.');
    }

    public function storeFeatureOption(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:160'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $slug = Str::slug($data['name']);

        $request->validate([
            'name' => [Rule::unique('vehicle_feature_options', 'name')],
        ]);

        VehicleFeatureOption::create([
            'name' => $data['name'],
            'slug' => $slug,
            'category' => Str::slug($data['category']),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->to(route('admin.features').'#features')->with('status', 'Extra configurable creado correctamente.');
    }

    public function toggleFeatureOption(VehicleFeatureOption $featureOption): RedirectResponse
    {
        $featureOption->update([
            'is_active' => ! $featureOption->is_active,
        ]);

        return redirect()->to(route('admin.features').'#features')->with('status', 'Estado del extra actualizado.');
    }

    public function updateFeatureOption(Request $request, VehicleFeatureOption $featureOption): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('vehicle_feature_options', 'name')->ignore($featureOption->id)],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:160'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $featureOption->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'category' => Str::slug($data['category']),
            'description' => $data['description'] ?: null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->to(route('admin.features').'#features')->with('status', 'CaracterÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­stica actualizada correctamente.');
    }

    public function destroyFeatureOption(VehicleFeatureOption $featureOption): RedirectResponse
    {
        if (Vehicle::query()->whereJsonContains('features', $featureOption->slug)->exists()) {
            return redirect()->to(route('admin.features').'#features')->with('status', 'No puedes eliminar est? caracterÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­stica porque ya estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ en uso en uno o mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡s vehÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­culos.');
        }

        $featureOption->delete();

        return redirect()->to(route('admin.features').'#features')->with('status', 'CaracterÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­stica eliminada correctamente.');
    }


    public function updatePaymentMethods(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'methods' => ['nullable', 'array'],
        ]);

        $methods = $this->paymentMethods();

        foreach (['offline', 'online'] as $group) {
            foreach ($methods[$group] ?? [] as $key => $method) {
                $methods[$group][$key]['enabled'] = in_array($key, $data['methods'] ?? [], true);
            }
        }

        $this->valuationSettings->put('billing.payment_methods', $methods, 'json');

        return redirect()->to(route('admin.settings').'#payment-methods')->with('status', 'Metodos de pago actualizados correctamente.');
    }

    public function approvePayment(Transaction $transaction): RedirectResponse
    {
        $this->billingService->approvePendingTransaction($transaction);

        return redirect()->to(route('admin.payments').'#transactions')->with('status', 'Pago verificado y plan activado correctamente.');
    }

    public function rejectPayment(Transaction $transaction): RedirectResponse
    {
        $this->billingService->rejectPendingTransaction($transaction);

        return redirect()->to(route('admin.payments').'#transactions')->with('status', 'Solicitud de pago rechazada.');
    }

    public function updatePublicTheme(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'public_theme' => ['required', Rule::in(['light', 'dark'])],
        ]);

        $this->valuationSettings->put('frontend.public_theme', $data['public_theme'], 'string');

        return redirect()->to(route('admin.settings').'#public-theme')->with(
            'status',
            $data['public_theme'] === 'dark'
                ? 'Tema oscuro activado para el home pÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºblico.'
                : 'Tema claro activado para el home pÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºblico.'
        );
    }

    private function paymentMethods(): array
    {
        return $this->valuationSettings->get('billing.payment_methods', [
            'offline' => [
                'cash' => ['label' => 'Efectivo', 'enabled' => true],
                'bank_transfer' => ['label' => 'Transferencia', 'enabled' => true],
                'sinpe_movil' => ['label' => 'Sinpe Movil', 'enabled' => true],
            ],
            'online' => [
                'paypal' => ['label' => 'PayPal', 'enabled' => true],
                'tilopay' => ['label' => 'Tilopay', 'enabled' => false],
            ],
        ]);
    }
}




