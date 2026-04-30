<?php

namespace App\Http\Controllers\Web;



use App\Http\Controllers\Controller;
use App\Enums\AccountType;
use App\Models\NewsPost;
use App\Models\Plan;
use App\Models\Redirect;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFeatureOption;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Services\Billing\BillingService;
use App\Services\Currency\ExchangeRateService;
use App\Services\Seo\SeoService;
use App\Services\Valuation\ValuationSettingsService;
use App\Services\Valuation\VehicleValuationAiNarrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;



class AdminPortalController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly SeoService $seoService,
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
        $userFilters = [
            'q' => trim($request->string('q')->toString()),
            'role' => $request->string('role')->toString(),
            'status' => $request->string('status')->toString(),
        ];



        $vehicleFilters = [
            'q' => trim($request->string('vehicle_q')->toString()),
            'status' => $request->string('vehicle_status')->toString(),
        ];



        $latestUsers = User::query()
            ->when($userFilters['q'] !== '', function ($query) use ($userFilters): void {
                $query->where(function ($builder) use ($userFilters): void {
                    $builder->where('name', 'like', '%'.$userFilters['q'].'%')
                        ->orWhere('email', 'like', '%'.$userFilters['q'].'%')
                        ->orWhere('phone', 'like', '%'.$userFilters['q'].'%');
                });
            })
            ->when($userFilters['role'] !== '', fn ($query) => $query->where('account_type', $userFilters['role']))
            ->when($userFilters['status'] !== '', fn ($query) => $query->where('is_active', $userFilters['status'] === 'active'))
            ->latest()
            ->paginate(15)
            ->withQueryString();



        $latestVehicles = Vehicle::query()
            ->with(['owner', 'make', 'model'])
            ->when($vehicleFilters['q'] !== '', function ($query) use ($vehicleFilters): void {
                $query->where(function ($builder) use ($vehicleFilters): void {
                    $builder->where('title', 'like', '%'.$vehicleFilters['q'].'%')
                        ->orWhere('city', 'like', '%'.$vehicleFilters['q'].'%')
                        ->orWhere('plate_number', 'like', '%'.$vehicleFilters['q'].'%')
                        ->orWhereHas('owner', fn ($ownerQuery) => $ownerQuery->where('email', 'like', '%'.$vehicleFilters['q'].'%'));
                });
            })
            ->when($vehicleFilters['status'] !== '', fn ($query) => $query->where('status', $vehicleFilters['status']))
            ->latest()
            ->paginate(12, ['*'], 'vehicles_page')
            ->withQueryString();



        $editingUser = $request->integer('edit_user')
            ? User::query()->find($request->integer('edit_user'))
            : null;



        $editingVehicle = $request->integer('edit_vehicle')
            ? Vehicle::query()->with(['owner', 'make', 'model'])->find($request->integer('edit_vehicle'))
            : null;



        return view('portal.admin.users', $this->sharedData($request) + [
            'latestUsers' => $latestUsers,
            'latestVehicles' => $latestVehicles,
            'userFilters' => $userFilters,
            'vehicleFilters' => $vehicleFilters,
            'editingUser' => $editingUser,
            'editingVehicle' => $editingVehicle,
            'userRoleOptions' => AccountType::values(),
            'vehicleStatusOptions' => ['draft', 'published', 'paused', 'sold', 'archived'],
            'vehicleTierOptions' => ['basic', 'estándar', 'premium', 'agencia', 'agencia-pro'],
            'vehicleOwners' => User::query()
                ->whereIn('account_type', [AccountType::Seller->value, AccountType::Dealer->value, AccountType::Admin->value])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }



    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'account_type' => ['required', Rule::in(AccountType::values())],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_phone' => ['nullable', 'string', 'max:30'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'agency_name' => ['nullable', 'string', 'max:255'],
            'is_verified' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);



        User::query()->create([
            'name' => $data['name'],
            'email' => strtolower((string) $data['email']),
            'password' => Hash::make($data['password']),
            'account_type' => $data['account_type'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_phone' => $data['whatsapp_phone'] ?? ($data['phone'] ?? null),
            'country_code' => strtoupper((string) ($data['country_code'] ?? 'CR')),
            'company_name' => $data['company_name'] ?? null,
            'agency_name' => $data['agency_name'] ?? null,
            'is_verified' => (bool) ($data['is_verified'] ?? false),
            'verified_at' => ! empty($data['is_verified']) ? now() : null,
            'is_active' => ! array_key_exists('is_active', $data) || (bool) $data['is_active'],
            'deactivated_at' => array_key_exists('is_active', $data) && ! $data['is_active'] ? now() : null,
            'last_seen_at' => now(),
        ]);



        return redirect()->to(route('admin.users').'#users-list')->with('status', 'Usuario creado correctamente.');
    }



    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'account_type' => ['required', Rule::in(AccountType::values())],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_phone' => ['nullable', 'string', 'max:30'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'agency_name' => ['nullable', 'string', 'max:255'],
            'is_verified' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);



        if ($user->id === $request->user()->id && array_key_exists('is_active', $data) && ! $data['is_active']) {
            return redirect()->to(route('admin.users', ['edit_user' => $user->id]).'#user-form')->with('status', 'No puedes desactivar tu propia cuenta.');
        }



        $payload = [
            'name' => $data['name'],
            'email' => strtolower((string) $data['email']),
            'account_type' => $data['account_type'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_phone' => $data['whatsapp_phone'] ?? ($data['phone'] ?? null),
            'country_code' => strtoupper((string) ($data['country_code'] ?? 'CR')),
            'company_name' => $data['company_name'] ?? null,
            'agency_name' => $data['agency_name'] ?? null,
            'is_verified' => (bool) ($data['is_verified'] ?? false),
            'verified_at' => ! empty($data['is_verified']) ? ($user->verified_at ?? now()) : null,
            'is_active' => ! array_key_exists('is_active', $data) || (bool) $data['is_active'],
            'deactivated_at' => array_key_exists('is_active', $data) && ! $data['is_active'] ? ($user->deactivated_at ?? now()) : null,
        ];



        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }



        $user->update($payload);



        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->tokens()->delete();
        }



        return redirect()->to(route('admin.users').'#users-list')->with('status', 'Usuario actualizado correctamente.');
    }



    public function toggleUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->to(route('admin.users').'#users-list')->with('status', 'No puedes desactivar tu propia cuenta.');
        }



        $nextState = ! $user->is_active;



        $user->update([
            'is_active' => $nextState,
            'deactivated_at' => $nextState ? null : now(),
        ]);



        if (! $nextState) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->tokens()->delete();
        }



        return redirect()->to(route('admin.users').'#users-list')->with('status', $nextState ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
    }



    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->to(route('admin.users').'#users-list')->with('status', 'No puedes eliminar tu propia cuenta.');
        }



        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->tokens()->delete();
        $user->delete();



        return redirect()->to(route('admin.users').'#users-list')->with('status', 'Usuario eliminado correctamente.');
    }



    public function storeVehicle(Request $request): RedirectResponse
    {
        $data = $this->validateAdminVehicle($request);
        $vehicle = Vehicle::query()->create($this->adminVehiclePayload($data));



        return redirect()->to(route('admin.users', ['edit_vehicle' => $vehicle->id]).'#vehicle-form')->with('status', 'Vehículo creado correctamente.');
    }



    public function updateVehicle(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $this->validateAdminVehicle($request, $vehicle);
        $vehicle->update($this->adminVehiclePayload($data, $vehicle));



        return redirect()->to(route('admin.users').'#vehicles')->with('status', 'Vehículo actualizado correctamente.');
    }



    public function toggleVehicle(Vehicle $vehicle): RedirectResponse
    {
        $nextStatus = $vehicle->status === 'published' ? 'paused' : 'published';



        $vehicle->update([
            'status' => $nextStatus,
            'published_at' => $nextStatus === 'published' ? ($vehicle->published_at ?? now()) : $vehicle->published_at,
        ]);



        return redirect()->to(route('admin.users').'#vehicles')->with('status', $nextStatus === 'published' ? 'Vehículo publicado correctamente.' : 'Vehículo desactivado correctamente.');
    }



    public function destroyVehicle(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();



        return redirect()->to(route('admin.users').'#vehicles')->with('status', 'Vehículo eliminado correctamente.');
    }
    public function settings(Request $request)
    {
        return view('portal.admin.settings', $this->sharedData($request) + [
            'paymentMethods' => $this->paymentMethods(),
            'seoSettings' => $this->seoService->settingsSnapshot(),
            'seoRedirects' => Redirect::query()->latest()->get(),
            'securitySettings' => $this->securitySettingsSnapshot(),
        ]);
    }



    public function updateSeoSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_title' => ['required', 'string', 'max:180'],
            'default_description' => ['required', 'string', 'max:255'],
            'title_suffix' => ['required', 'string', 'max:120'],
            'default_og_image' => ['nullable', 'string', 'max:2048'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'index_filtered_inventory' => ['nullable', 'boolean'],
            'indexnow_enabled' => ['nullable', 'boolean'],
            'indexnow_key' => ['nullable', 'string', 'max:120'],
            'indexnow_endpoint' => ['nullable', 'string', 'max:2048'],
        ]);



        $this->valuationSettings->put('seo.default_title', $data['default_title'], 'string');
        $this->valuationSettings->put('seo.default_description', $data['default_description'], 'string');
        $this->valuationSettings->put('seo.title_suffix', $data['title_suffix'], 'string');
        $this->valuationSettings->put('seo.default_og_image', $data['default_og_image'] ?? '', 'string');
        $this->valuationSettings->put('seo.google_site_verification', $data['google_site_verification'] ?? '', 'string');
        $this->valuationSettings->put('seo.index_filtered_inventory', (bool) ($data['index_filtered_inventory'] ?? false), 'boolean');
        $this->valuationSettings->put('seo.indexnow_enabled', (bool) ($data['indexnow_enabled'] ?? false), 'boolean');
        $this->valuationSettings->put('seo.indexnow_key', trim((string) ($data['indexnow_key'] ?? '')), 'string');
        $this->valuationSettings->put('seo.indexnow_endpoint', trim((string) ($data['indexnow_endpoint'] ?? 'https://api.indexnow.org/indexnow')), 'string');



        return redirect()->to(route('admin.settings').'#seo-settings')->with('status', 'Configuración SEO actualizada correctamente.');
    }

    public function updateSecuritySettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'honeypot_enabled' => ['nullable', 'boolean'],
            'honeypot_randomize' => ['nullable', 'boolean'],
            'honeypot_seconds' => ['required', 'integer', 'min:1', 'max:10'],
            'clamav_skip_validation' => ['nullable', 'boolean'],
            'clamav_preferred_socket' => ['required', Rule::in(['tcp_socket', 'unix_socket'])],
            'clamav_tcp_socket' => ['nullable', 'string', 'max:255'],
            'clamav_unix_socket' => ['nullable', 'string', 'max:255'],
            'clamav_socket_connect_timeout' => ['required', 'integer', 'min:1', 'max:30'],
            'clamav_socket_read_timeout' => ['required', 'integer', 'min:1', 'max:120'],
            'blocked_ips' => ['nullable', 'string', 'max:5000'],
            'allowed_ips' => ['nullable', 'string', 'max:5000'],
            'blocked_user_agents' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->valuationSettings->put('security.honeypot.enabled', (bool) ($data['honeypot_enabled'] ?? false), 'boolean');
        $this->valuationSettings->put('security.honeypot.randomize_name_field_name', (bool) ($data['honeypot_randomize'] ?? false), 'boolean');
        $this->valuationSettings->put('security.honeypot.amount_of_seconds', (int) $data['honeypot_seconds'], 'integer');
        $this->valuationSettings->put('security.clamav.skip_validation', (bool) ($data['clamav_skip_validation'] ?? false), 'boolean');
        $this->valuationSettings->put('security.clamav.preferred_socket', $data['clamav_preferred_socket'], 'string');
        $this->valuationSettings->put('security.clamav.tcp_socket', trim((string) ($data['clamav_tcp_socket'] ?? '')), 'string');
        $this->valuationSettings->put('security.clamav.unix_socket', trim((string) ($data['clamav_unix_socket'] ?? '')), 'string');
        $this->valuationSettings->put('security.clamav.socket_connect_timeout', (int) $data['clamav_socket_connect_timeout'], 'integer');
        $this->valuationSettings->put('security.clamav.socket_read_timeout', (int) $data['clamav_socket_read_timeout'], 'integer');
        $this->valuationSettings->put('security.request_filters.blocked_ips', $this->splitTextareaList((string) ($data['blocked_ips'] ?? '')), 'json');
        $this->valuationSettings->put('security.request_filters.allowed_ips', $this->splitTextareaList((string) ($data['allowed_ips'] ?? '')), 'json');
        $this->valuationSettings->put('security.request_filters.blocked_user_agents', $this->splitTextareaList((string) ($data['blocked_user_agents'] ?? '')), 'json');

        return redirect()->to(route('admin.settings').'#security-center')->with('status', 'Configuracion de seguridad actualizada correctamente.');
    }

    public function testClamavConnection(): RedirectResponse
    {
        $clamav = $this->clamavStatusSnapshot(forceProbe: true);
        $message = $clamav['reachable'] === true
            ? 'Conexion ClamAV verificada correctamente.'
            : 'No se pudo conectar con ClamAV. '.$clamav['status'];

        return redirect()->to(route('admin.settings').'#security-center')->with('status', $message);
    }



    public function storeRedirect(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:255', 'unique:redirects,from_path'],
            'to_url' => ['required', 'string', 'max:2048'],
            'status_code' => ['required', Rule::in([301, 302])],
            'is_active' => ['nullable', 'boolean'],
        ]);



        Redirect::query()->create([
            'from_path' => $this->normalizeRedirectPath($data['from_path']),
            'to_url' => $data['to_url'],
            'status_code' => (int) $data['status_code'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        Cache::forget('seo-redirect:'.$this->normalizeRedirectPath($data['from_path']));



        return redirect()->to(route('admin.settings').'#seo-redirects')->with('status', 'Redirección SEO creada correctamente.');
    }



    public function updateRedirect(Request $request, Redirect $redirect): RedirectResponse
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:255', Rule::unique('redirects', 'from_path')->ignore($redirect->id)],
            'to_url' => ['required', 'string', 'max:2048'],
            'status_code' => ['required', Rule::in([301, 302])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $previousPath = $redirect->from_path;



        $redirect->update([
            'from_path' => $this->normalizeRedirectPath($data['from_path']),
            'to_url' => $data['to_url'],
            'status_code' => (int) $data['status_code'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        Cache::forget('seo-redirect:'.$previousPath);
        Cache::forget('seo-redirect:'.$this->normalizeRedirectPath($data['from_path']));



        return redirect()->to(route('admin.settings').'#seo-redirects')->with('status', 'Redirección SEO actualizada correctamente.');
    }



    public function destroyRedirect(Redirect $redirect): RedirectResponse
    {
        Cache::forget('seo-redirect:'.$redirect->from_path);
        $redirect->delete();



        return redirect()->to(route('admin.settings').'#seo-redirects')->with('status', 'Redirección SEO eliminada correctamente.');
    }



    public function mailTest(Request $request)
    {
        return view('portal.admin.mail-test', $this->sharedData($request) + [
            'mailConfig' => $this->mailConfigSnapshot(),
            'defaultRecipient' => (string) ($request->user()?->email ?? ''),
        ]);
    }



    public function sendMailTest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'subject' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);



        $recipient = strtolower((string) $data['email']);
        $subject = trim((string) ($data['subject'] ?? '')) ?: 'Prueba SMTP Movikaa';
        $message = trim((string) ($data['message'] ?? '')) ?: 'Este es un correo de prueba enviado desde el panel admin de Movikaa.';
        $mailConfig = $this->mailConfigSnapshot();



        try {
            Mail::html(
                view('portal.admin.partials.mail-test-message', [
                    'messageBody' => $message,
                    'mailConfig' => $mailConfig,
                    'sentAt' => now(),
                    'senderName' => (string) ($request->user()?->name ?? config('app.name')),
                ])->render(),
                function ($mail) use ($recipient, $subject): void {
                    $mail->to($recipient)->subject($subject);
                }
            );
        } catch (Throwable $exception) {
            return redirect()
                ->to(route('admin.mail-test').'#mail-test-form')
                ->withErrors(['email' => 'No se pudo enviar el correo de prueba: '.$exception->getMessage()])
                ->withInput();
        }



        $statusMessage = $mailConfig['default_mailer'] === 'smtp'
            ? 'Correo de prueba enviado. Revisa la bandeja del destinatario.'
            : 'Correo de prueba procesado, pero el mailer activo no es SMTP. Revisa el log y la configuración antes de usar producción.';



        return redirect()
            ->to(route('admin.mail-test').'#mail-test-form')
            ->with('status', $statusMessage);
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



        return redirect()->to(route('admin.news.edit', $post).'#editor')->with('status', 'Artículo creado correctamente.');
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



        return redirect()->to(route('admin.news.edit', $newsPost).'#editor')->with('status', 'Artículo actualizado correctamente.');
    }



    public function destroyNews(NewsPost $newsPost): RedirectResponse
    {
        $this->deleteNewsCoverImage($newsPost->cover_image_url);
        $newsPost->delete();



        return redirect()->to(route('admin.news').'#news-list')->with('status', 'Artículo eliminado correctamente.');
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
            'supportIntegrityAlert' => $this->supportIntegrityAlert(),
        ];
    }

    private function supportIntegrityAlert(): ?array
    {
        $footerFile = resource_path('js/public-shell.jsx');

        if (! File::exists($footerFile)) {
            return [
                'title' => 'Aplicacion con cambios no permitidos',
                'message' => 'No fue posible validar el footer publico porque el archivo fuente del sistema no existe en su ubicacion esperada. Esta instalacion queda marcada como modificada y fuera de soporte.',
            ];
        }

        $contents = File::get($footerFile);
        $hasDeveloperLabel = str_contains($contents, 'Desarrollado por');
        $hasPixelProLink = str_contains($contents, 'https://pixelprocr.com');
        $hasPixelProName = str_contains($contents, 'PixelPRO');

        if ($hasDeveloperLabel && $hasPixelProLink && $hasPixelProName) {
            return null;
        }

        return [
            'title' => 'Aplicacion con cambios no permitidos',
            'message' => 'Se detectaron cambios en el footer o en el codigo de autoria del sistema. Esta instalacion fue modificada fuera del canal autorizado y ya no cuenta con soporte tecnico de PixelPRO.',
        ];
    }

    private function securitySettingsSnapshot(): array
    {
        $protectedForms = [
            'Inicio de sesion',
            'Registro',
            'Recuperacion de clave',
            'Cambio de clave',
            'Onboarding de vendedor',
            'Tasador publico',
        ];

        return [
            'honeypot' => [
                'enabled' => (bool) $this->valuationSettings->get('security.honeypot.enabled', config('honeypot.enabled')),
                'randomize' => (bool) $this->valuationSettings->get('security.honeypot.randomize_name_field_name', config('honeypot.randomize_name_field_name')),
                'seconds' => (int) $this->valuationSettings->get('security.honeypot.amount_of_seconds', config('honeypot.amount_of_seconds', 2)),
                'field' => (string) config('honeypot.name_field_name', 'company_website'),
                'timestamp_field' => (string) config('honeypot.valid_from_field_name', 'valid_from'),
                'protected_forms' => $protectedForms,
            ],
            'clamav' => $this->clamavStatusSnapshot(),
            'request_filters' => [
                'blocked_ips' => array_values($this->valuationSettings->get('security.request_filters.blocked_ips', config('security.blocked_ips', [])) ?: []),
                'allowed_ips' => array_values($this->valuationSettings->get('security.request_filters.allowed_ips', config('security.allowed_ips', [])) ?: []),
                'blocked_user_agents' => array_values($this->valuationSettings->get('security.request_filters.blocked_user_agents', config('security.blocked_user_agents', [])) ?: []),
            ],
        ];
    }

    private function clamavStatusSnapshot(bool $forceProbe = false): array
    {
        $preferredSocket = (string) $this->valuationSettings->get('security.clamav.preferred_socket', config('clamav.preferred_socket', 'tcp_socket'));
        $socket = $preferredSocket === 'unix_socket'
            ? (string) $this->valuationSettings->get('security.clamav.unix_socket', config('clamav.unix_socket'))
            : (string) $this->valuationSettings->get('security.clamav.tcp_socket', config('clamav.tcp_socket'));
        $skipValidation = (bool) $this->valuationSettings->get('security.clamav.skip_validation', config('clamav.skip_validation', false));
        $clientExceptions = (bool) $this->valuationSettings->get('security.clamav.client_exceptions', config('clamav.client_exceptions', false));
        $connectTimeout = (int) $this->valuationSettings->get('security.clamav.socket_connect_timeout', config('clamav.socket_connect_timeout', 5));

        if (app()->environment('testing') && ! $forceProbe) {
            return [
                'preferred_socket' => $preferredSocket,
                'socket' => $socket,
                'skip_validation' => $skipValidation,
                'client_exceptions' => $clientExceptions,
                'reachable' => null,
                'status' => 'No verificado en pruebas',
            ];
        }

        $reachable = false;
        $status = 'No disponible';

        if ($preferredSocket === 'unix_socket') {
            $reachable = $socket !== '' && file_exists($socket);
            $status = $reachable ? 'Socket unix detectado' : 'Socket unix no encontrado';
        } else {
            $connection = @stream_socket_client($socket, $errorCode, $errorMessage, $connectTimeout);

            if (is_resource($connection)) {
                fclose($connection);
                $reachable = true;
                $status = 'Socket TCP alcanzable';
            } elseif (! empty($errorMessage)) {
                $status = 'Sin conexion: '.$errorMessage;
            }
        }

        return [
            'preferred_socket' => $preferredSocket,
            'socket' => $socket,
            'skip_validation' => $skipValidation,
            'client_exceptions' => $clientExceptions,
            'reachable' => $reachable,
            'status' => $status,
        ];
    }

    private function splitTextareaList(string $value): array
    {
        return array_values(array_filter(array_map(
            static fn (string $entry) => trim($entry),
            preg_split('/[\r\n,]+/', $value) ?: []
        )));
    }



    private function mailConfigSnapshot(): array
    {
        return [
            'default_mailer' => (string) config('mail.default'),
            'host' => (string) config('mail.mailers.smtp.host'),
            'port' => (string) config('mail.mailers.smtp.port'),
            'scheme' => (string) (config('mail.mailers.smtp.scheme') ?? ''),
            'username' => (string) (config('mail.mailers.smtp.username') ?? ''),
            'from_address' => (string) config('mail.from.address'),
            'from_name' => (string) config('mail.from.name'),
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



        return redirect()->to(route('admin.features').'#features')->with('status', 'Característica actualizada correctamente.');
    }



    public function destroyFeatureOption(VehicleFeatureOption $featureOption): RedirectResponse
    {
        if (Vehicle::query()->whereJsonContains('features', $featureOption->slug)->exists()) {
            return redirect()->to(route('admin.features').'#features')->with('status', 'No puedes eliminar esta característica porque ya está en uso en uno o más vehículos.');
        }



        $featureOption->delete();



        return redirect()->to(route('admin.features').'#features')->with('status', 'Característica eliminada correctamente.');
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



        return redirect()->to(route('admin.settings').'#payment-methods')->with('status', 'Métodos de pago actualizados correctamente.');
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
                ? 'Tema oscuro activado para el home público.'
                : 'Tema claro activado para el home público.'
        );
    }



    private function normalizeRedirectPath(string $path): string
    {
        $normalized = '/'.trim($path, '/');

        return $normalized === '//' ? '/' : $normalized;
    }



    private function validateAdminVehicle(Request $request, ?Vehicle $vehicle = null): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query
                ->where('is_active', true)
                ->whereIn('account_type', [AccountType::Seller->value, AccountType::Dealer->value, AccountType::Admin->value]))],
            'vehicle_make_id' => ['required', 'integer', Rule::exists('vehicle_makes', 'id')],
            'vehicle_model_id' => ['required', 'integer', Rule::exists('vehicle_models', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'condition' => ['required', Rule::in(['new', 'used'])],
            'year' => ['required', 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'body_type' => ['required', 'string', 'max:60'],
            'fuel_type' => ['required', 'string', 'max:60'],
            'transmission' => ['required', 'string', 'max:60'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'city' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:20'],
            'status' => ['required', Rule::in(['draft', 'published', 'paused', 'sold', 'archived'])],
            'publication_tier' => ['required', Rule::in(['basic', 'estándar', 'premium', 'agencia', 'agencia-pro'])],
        ]);



        $modelBelongs = VehicleModel::query()
            ->whereKey($data['vehicle_model_id'])
            ->where('vehicle_make_id', $data['vehicle_make_id'])
            ->exists();



        if (! $modelBelongs) {
            abort(422, 'El modelo seleccionado no pertenece a la marca indicada.');
        }



        return $data;
    }



    private function adminVehiclePayload(array $data, ?Vehicle $vehicle = null): array
    {
        $targetStatus = $data['status'];
        $isPublishingNow = $targetStatus === 'published' && ($vehicle?->status !== 'published');



        return [
            'user_id' => $data['user_id'],
            'vehicle_make_id' => $data['vehicle_make_id'],
            'vehicle_model_id' => $data['vehicle_model_id'],
            'title' => $data['title'],
            'slug' => $this->uniqueAdminVehicleSlug($data['title'], (int) $data['year'], $vehicle?->id),
            'condition' => $data['condition'],
            'year' => $data['year'],
            'body_type' => $data['body_type'],
            'fuel_type' => $data['fuel_type'],
            'transmission' => $data['transmission'],
            'price' => $data['price'],
            'currency' => strtoupper((string) ($data['currency'] ?? 'CRC')),
            'city' => $data['city'] ?? null,
            'description' => $data['description'],
            'status' => $targetStatus,
            'publication_tier' => $data['publication_tier'],
            'country_code' => $vehicle?->country_code ?? 'CR',
            'published_at' => $targetStatus === 'published'
                ? ($vehicle?->published_at ?? ($isPublishingNow ? now() : now()))
                : $vehicle?->published_at,
        ];
    }



    private function uniqueAdminVehicleSlug(string $title, int $year, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($title).' '.$year) ?: 'vehiculo';
        $slug = $base;
        $counter = 2;



        while (Vehicle::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }



        return $slug;
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
