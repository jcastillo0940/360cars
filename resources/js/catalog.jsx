import React, { useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';

function Icon({ name, className = '' }) {
    return <span className={`material-symbols-outlined ${className}`.trim()} aria-hidden="true">{name}</span>;
}

function TopBar({ homeUrl, catalogUrl, sellUrl, accountUrl }) {
    const [menuOpen, setMenuOpen] = useState(false);

    return (
        <nav className="fixed inset-x-0 top-0 z-50 border-b border-outline-variant/30 bg-white/80 backdrop-blur-md">
            <div className="mx-auto flex h-20 max-w-screen-2xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div className="flex items-center gap-4 lg:gap-12">
                    <button
                        type="button"
                        className="inline-flex h-11 w-11 items-center justify-center rounded-full border border-outline-variant/40 text-primary transition hover:bg-primary/5 md:hidden"
                        onClick={() => setMenuOpen((current) => !current)}
                        aria-label="Abrir menu"
                    >
                        <Icon name={menuOpen ? 'close' : 'menu'} className="text-[24px]" />
                    </button>
                    <a href={homeUrl} className="font-headline text-2xl font-black tracking-tight text-primary sm:text-3xl">Movikaa</a>
                    <div className="hidden md:flex md:gap-6 lg:gap-8">
                        <a href={catalogUrl} className="font-headline text-sm font-bold tracking-tight text-primary lg:text-base">Comprar</a>
                        <a href={homeUrl + '#destacados'} className="font-headline text-sm font-bold tracking-tight text-slate-600 transition hover:text-primary lg:text-base">Destacados</a>
                        <a href={homeUrl + '/tasador'} className="font-headline text-sm font-bold tracking-tight text-slate-600 transition hover:text-primary lg:text-base">Valuacion</a>
                        <a href={homeUrl + '#noticias'} className="font-headline text-sm font-bold tracking-tight text-slate-600 transition hover:text-primary lg:text-base">Noticias</a>
                    </div>
                </div>
                <div className="hidden items-center gap-4 md:flex">
                    <a href={accountUrl} className="px-5 py-2 text-sm font-bold text-slate-600 transition hover:text-primary">Ingresar</a>
                    <a href={sellUrl} className="rounded bg-secondary px-4 py-2.5 font-headline text-sm font-bold text-white shadow-md transition-colors hover:bg-secondary-container">Vender mi auto</a>
                </div>
                <a href={accountUrl} className="inline-flex h-11 w-11 items-center justify-center rounded-full text-primary md:hidden">
                    <Icon name="person" className="text-[24px]" />
                </a>
            </div>
            {menuOpen ? (
                <div className="border-t border-outline-variant/20 bg-white px-4 py-4 shadow-xl md:hidden">
                    <div className="flex flex-col gap-4">
                        <a href={catalogUrl} className="font-headline text-base font-bold tracking-tight text-slate-700">Comprar</a>
                        <a href={homeUrl + '#destacados'} className="font-headline text-base font-bold tracking-tight text-slate-700">Destacados</a>
                        <a href={homeUrl + '/tasador'} className="font-headline text-base font-bold tracking-tight text-slate-700">Valuacion</a>
                        <a href={homeUrl + '#noticias'} className="font-headline text-base font-bold tracking-tight text-slate-700">Noticias</a>
                        <div className="mt-3 flex flex-col gap-3 border-t border-outline-variant/20 pt-4">
                            <a href={accountUrl} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Ingresar</a>
                            <a href={sellUrl} className="rounded bg-secondary px-4 py-3 text-center font-headline font-bold text-white">Vender mi auto</a>
                        </div>
                    </div>
                </div>
            ) : null}
        </nav>
    );
}

function PriceStack({ primary, secondary, align = 'right' }) {
    return (
        <div className={align === 'right' ? 'text-right' : ''}>
            <span className="block text-2xl font-black text-primary">{primary}</span>
            {secondary ? <span className="mt-1 block text-xs font-semibold text-slate-400">{secondary}</span> : null}
        </div>
    );
}

async function mutateVehicle(urlTemplate, vehicleId, method, csrfToken) {
    const response = await fetch(urlTemplate.replace('__VEHICLE__', String(vehicleId)), {
        method,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });

    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(payload.message || 'No fue posible completar la accion.');
    }

    return payload;
}

function VehicleCard({ vehicle, isFavorited, isCompared, onFavorite, onCompare }) {
    return (
        <article className="group overflow-hidden rounded-2xl border border-outline-variant/20 bg-white transition-all hover:-translate-y-1 hover:shadow-2xl">
            <a href={vehicle.url} className="block">
                <div className="relative h-56 overflow-hidden">
                    <img src={vehicle.primary_image} alt={vehicle.title} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                    <div className="absolute left-3 top-3 flex flex-wrap gap-2">
                        {vehicle.is_featured ? <div className="rounded bg-primary px-2 py-1 text-[10px] font-bold text-white">DESTACADO</div> : null}
                        {vehicle.is_paid ? <div className="rounded bg-secondary px-2 py-1 text-[10px] font-bold text-white">{vehicle.plan_name}</div> : null}
                    </div>
                    {vehicle.price_badge ? <div className="absolute right-3 top-3 rounded bg-white/90 px-2 py-1 text-[10px] font-bold text-primary">{vehicle.price_badge}</div> : null}
                </div>
            </a>
            <div className="p-5">
                <div className="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{vehicle.make} {vehicle.model}</p>
                        <a href={vehicle.url} className="mt-2 block font-headline text-xl font-extrabold tracking-tight text-slate-900">{vehicle.title}</a>
                        <p className="mt-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{vehicle.visibility_bucket}</p>
                    </div>
                    <button type="button" onClick={() => onFavorite(vehicle.id)} className={`rounded-full p-2 transition-colors ${isFavorited ? 'bg-primary text-white' : 'bg-surface-container-high text-slate-600 hover:bg-primary hover:text-white'}`}>
                        <Icon name="favorite" className="text-[18px]" />
                    </button>
                </div>
                <div className="flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-500">
                    <span>{vehicle.year}</span>
                    <span>{vehicle.mileage ? `${vehicle.mileage} ${vehicle.mileage_unit}` : '0 km'}</span>
                    <span>{vehicle.city || 'Costa Rica'}</span>
                </div>
                <div className="mt-5 flex items-center justify-between gap-3">
                    <PriceStack primary={vehicle.price} secondary={vehicle.price_secondary} />
                    <button type="button" onClick={() => onCompare(vehicle.id)} className={`rounded-full px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] transition-colors ${isCompared ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}>
                        Comparar
                    </button>
                </div>
            </div>
        </article>
    );
}

function CatalogPage({ homeUrl, catalogUrl, sellUrl, accountUrl, publicTheme = 'light', vehicles, filters, filterOptions, engagement, endpoints }) {
    const [localFilters, setLocalFilters] = useState({
        make: filters.make || '',
        model: filters.model || '',
        city: filters.city || '',
        max_price: filters.max_price || '',
    });
    const [favoriteIds, setFavoriteIds] = useState(engagement.favoriteVehicleIds || []);
    const [comparisonIds, setComparisonIds] = useState(engagement.comparisonVehicleIds || []);
    const [saveMessage, setSaveMessage] = useState('');
    const isDark = publicTheme === 'dark';

    const stats = useMemo(() => [
        { label: 'Resultados activos', value: vehicles.meta.total },
        { label: 'Pagina actual', value: vehicles.meta.current_page },
        { label: 'Marcas disponibles', value: filterOptions.makes.length },
    ], [vehicles, filterOptions]);

    const handleChange = (key) => (event) => {
        setLocalFilters((current) => ({ ...current, [key]: event.target.value }));
    };

    const handleSubmit = (event) => {
        event.preventDefault();
        const params = new URLSearchParams();
        Object.entries(localFilters).forEach(([key, value]) => {
            if (value) params.set(key, value);
        });
        window.location.href = `${catalogUrl}${params.toString() ? `?${params.toString()}` : ''}`;
    };

    const ensureBuyer = () => {
        if (!engagement.authenticated) {
            window.location.href = endpoints.loginUrl;
            return false;
        }
        return true;
    };

    const toggleFavorite = async (vehicleId) => {
        if (!ensureBuyer()) return;
        const isActive = favoriteIds.includes(vehicleId);
        const payload = await mutateVehicle(endpoints.favoriteTemplate, vehicleId, isActive ? 'DELETE' : 'POST', endpoints.csrfToken);
        setFavoriteIds((current) => payload.favorited ? [...current.filter((id) => id !== vehicleId), vehicleId] : current.filter((id) => id !== vehicleId));
    };

    const toggleCompare = async (vehicleId) => {
        if (!ensureBuyer()) return;
        const isActive = comparisonIds.includes(vehicleId);
        const payload = await mutateVehicle(endpoints.comparisonTemplate, vehicleId, isActive ? 'DELETE' : 'POST', endpoints.csrfToken);
        setComparisonIds((current) => payload.compared ? [...current.filter((id) => id !== vehicleId), vehicleId] : current.filter((id) => id !== vehicleId));
    };

    const saveSearch = async () => {
        if (!ensureBuyer() || !endpoints.savedSearchUrl) return;
        const response = await fetch(endpoints.savedSearchUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': endpoints.csrfToken,
            },
            body: JSON.stringify({
                name: `Busqueda ${localFilters.make || 'general'} ${localFilters.city || ''}`.trim(),
                filters: localFilters,
                notification_frequency: 'instant',
            }),
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            setSaveMessage(payload.message || 'No se pudo guardar la busqueda.');
            return;
        }
        setSaveMessage('Busqueda guardada en tu dashboard buyer.');
    };

    return (
        <div className={`font-body md:pb-0 ${isDark ? 'theme-dark bg-[#05070b] pb-20 text-white' : 'bg-background pb-20 text-on-background'}`}>
            <TopBar homeUrl={homeUrl} catalogUrl={catalogUrl} sellUrl={sellUrl} accountUrl={accountUrl} />
            <main className="pt-20">
                <section className="border-b border-outline-variant/20 bg-gradient-to-br from-[#eff5ff] via-white to-[#f8efe8] py-16 sm:py-20">
                    <div className="mx-auto grid max-w-screen-2xl grid-cols-1 gap-10 px-4 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Inventario verificado</p>
                            <h1 className="mt-4 font-headline text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl">Encuentre el vehiculo ideal con un catalogo limpio, rapido y facil de filtrar.</h1>
                            <p className="mt-5 max-w-2xl text-base text-slate-600 sm:text-lg">Listado publico pensado para buyer, con fichas claras, filtros directos, badges de visibilidad y precio oficial en colones con referencia discreta en dolares.</p>
                            <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                {stats.map((item) => (
                                    <div key={item.label} className="rounded-2xl border border-white/70 bg-white/80 p-5 shadow-sm backdrop-blur">
                                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{item.label}</p>
                                        <strong className="mt-3 block font-headline text-3xl font-extrabold text-primary">{item.value}</strong>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="glass-panel rounded-3xl border border-white/70 p-5 shadow-2xl sm:p-6">
                            <div className="mb-6 flex items-center justify-between">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Filtros</p>
                                    <h2 className="mt-2 font-headline text-2xl font-extrabold tracking-tight">Refine su busqueda</h2>
                                </div>
                                <Icon name="tune" className="text-[26px] text-primary" />
                            </div>
                            <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label className="rounded-2xl border border-outline-variant/30 bg-white p-4">
                                    <span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Marca</span>
                                    <select value={localFilters.make} onChange={handleChange('make')} className="w-full border-none bg-transparent p-0 font-semibold focus:ring-0">
                                        <option value="">Todas</option>
                                        {filterOptions.makes.map((item) => <option key={item} value={item}>{item}</option>)}
                                    </select>
                                </label>
                                <label className="rounded-2xl border border-outline-variant/30 bg-white p-4">
                                    <span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Modelo</span>
                                    <select value={localFilters.model} onChange={handleChange('model')} className="w-full border-none bg-transparent p-0 font-semibold focus:ring-0">
                                        <option value="">Todos</option>
                                        {filterOptions.models.map((item) => <option key={item} value={item}>{item}</option>)}
                                    </select>
                                </label>
                                <label className="rounded-2xl border border-outline-variant/30 bg-white p-4">
                                    <span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Ciudad</span>
                                    <select value={localFilters.city} onChange={handleChange('city')} className="w-full border-none bg-transparent p-0 font-semibold focus:ring-0">
                                        <option value="">Todas</option>
                                        {filterOptions.cities.map((item) => <option key={item} value={item}>{item}</option>)}
                                    </select>
                                </label>
                                <label className="rounded-2xl border border-outline-variant/30 bg-white p-4">
                                    <span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Precio max.</span>
                                    <select value={localFilters.max_price} onChange={handleChange('max_price')} className="w-full border-none bg-transparent p-0 font-semibold focus:ring-0">
                                        <option value="">Sin limite</option>
                                        {filterOptions.priceSteps.map((item) => <option key={item} value={item}>{`¢${item.toLocaleString('es-CR')}`}</option>)}
                                    </select>
                                </label>
                                <button type="submit" className="inline-flex items-center justify-center gap-2 rounded-2xl bg-secondary px-5 py-4 font-headline text-lg font-extrabold text-white transition-colors hover:bg-secondary-container sm:col-span-2">
                                    <Icon name="search" className="text-[20px]" /> Buscar vehiculos
                                </button>
                            </form>
                            <button type="button" onClick={saveSearch} className="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-primary px-5 py-3 font-headline text-sm font-bold text-primary transition-colors hover:bg-primary hover:text-white">
                                <Icon name="notifications_active" className="text-[18px]" /> Guardar esta busqueda
                            </button>
                            {saveMessage ? <p className="mt-3 text-sm text-slate-500">{saveMessage}</p> : null}
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-screen-2xl px-4 py-16 sm:px-6 lg:px-8">
                    <div className="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Catalogo publico</p>
                            <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Vehiculos disponibles</h2>
                        </div>
                        <p className="text-sm text-slate-500">Mostrando {vehicles.data.length} de {vehicles.meta.total} resultados.</p>
                    </div>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                        {vehicles.data.map((vehicle) => (
                            <VehicleCard
                                key={vehicle.id}
                                vehicle={vehicle}
                                isFavorited={favoriteIds.includes(vehicle.id)}
                                isCompared={comparisonIds.includes(vehicle.id)}
                                onFavorite={toggleFavorite}
                                onCompare={toggleCompare}
                            />
                        ))}
                    </div>
                </section>
            </main>
        </div>
    );
}

const element = document.getElementById('catalog-react');
if (element) {
    createRoot(element).render(<CatalogPage {...JSON.parse(element.dataset.props || '{}')} />);
}
