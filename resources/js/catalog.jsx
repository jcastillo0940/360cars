import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { buildComparisonsUrl, getComparisonIds } from './comparison-store';
import { Icon, PriceStack, PublicFooter, PublicTopBar, formatCRC } from './public-shell';

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
        throw new Error(payload.message || 'No fue posible completar la acción.');
    }

    return payload;
}

function VehicleCard({ vehicle, isFavorited, onFavorite }) {
    return (
        <article className="group overflow-hidden rounded-2xl border border-outline-variant/20 bg-white transition-all hover:-translate-y-1 hover:shadow-2xl">
            <a href={vehicle.url} className="block">
                <div className="relative h-56 overflow-hidden">
                    <img src={vehicle.primary_image} alt={vehicle.title} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                    <div className="absolute left-3 top-3 flex flex-wrap gap-2">
                        {vehicle.is_featured ? <div className="rounded-full bg-secondary px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-950">Destacado</div> : null}
                    </div>
                </div>
            </a>
            <div className="p-5">
                <div className="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{vehicle.make} {vehicle.model}</p>
                        <a href={vehicle.url} className="mt-2 block font-headline text-xl font-extrabold tracking-tight text-slate-900">{vehicle.title}</a>
                    </div>
                    <button type="button" onClick={() => onFavorite(vehicle.id)} className={`rounded-full p-2 transition-colors ${isFavorited ? 'bg-secondary text-white' : 'bg-secondary/12 text-secondary hover:bg-secondary hover:text-white'}`}>
                        <Icon name="favorite" className="text-[18px]" />
                    </button>
                </div>
                <div className="flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-500">
                    <span>{vehicle.year}</span>
                    <span>{vehicle.mileage ? `${vehicle.mileage} ${vehicle.mileage_unit}` : 'Kilometraje no indicado'}</span>
                    <span>{vehicle.province || vehicle.city || 'Costa Rica'}</span>
                </div>
                <div className="mt-4 flex flex-wrap gap-2">
                    {vehicle.performance_badge ? <span className="rounded-full bg-primary-fixed px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-primary">{vehicle.performance_badge}</span> : null}
                    <span className="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">{vehicle.view_count} vistas</span>
                    <span className="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">{vehicle.lead_count} contactos</span>
                </div>
                <div className="mt-5 flex items-center justify-between gap-3">
                    <PriceStack primary={vehicle.price} secondary={vehicle.price_secondary} />
                    {vehicle.contact_url ? (
                        <a
                            href={vehicle.contact_url}
                            className="inline-flex items-center gap-2 rounded-full border border-[#25D366]/30 bg-[#25D366]/12 px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] text-[#128C7E] transition-colors hover:border-[#25D366] hover:bg-[#25D366] hover:text-white"
                        >
                            <Icon name="chat" className="text-[16px]" />
                            Contactar
                        </a>
                    ) : (
                        <a
                            href={vehicle.url}
                            className="inline-flex items-center gap-2 rounded-full border border-secondary/45 bg-secondary/12 px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] text-secondary transition-colors hover:border-secondary hover:bg-secondary hover:text-white"
                        >
                            <Icon name="visibility" className="text-[16px]" />
                            Ver auto
                        </a>
                    )}
                </div>
            </div>
        </article>
    );
}

function RangeControl({ label, min, max, step, valueMin, valueMax, onMinChange, onMaxChange, formatter }) {
    return (
        <div className="rounded-2xl border border-outline-variant/30 bg-white p-4">
            <div className="flex items-center justify-between gap-3">
                <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{label}</span>
                <strong className="text-xs font-extrabold text-primary">{formatter(valueMin)} - {formatter(valueMax)}</strong>
            </div>
            <div className="mt-4 space-y-3">
                <div>
                    <div className="mb-1 flex items-center justify-between text-[11px] font-semibold text-slate-400"><span>Desde</span><span>{formatter(valueMin)}</span></div>
                    <input type="range" min={min} max={max} step={step} value={valueMin} onChange={(event) => onMinChange(Number(event.target.value))} className="brand-range w-full" />
                </div>
                <div>
                    <div className="mb-1 flex items-center justify-between text-[11px] font-semibold text-slate-400"><span>Hasta</span><span>{formatter(valueMax)}</span></div>
                    <input type="range" min={min} max={max} step={step} value={valueMax} onChange={(event) => onMaxChange(Number(event.target.value))} className="brand-range w-full" />
                </div>
            </div>
        </div>
    );
}

function CatalogPage({ homeUrl, catalogUrl, brandsUrl, sellUrl, accountUrl, loginUrl, authUser, comparisonsUrl, valuationUrl, publicTheme = 'light', vehicles, filters, filterOptions, engagement, endpoints, footerLinks }) {
    const priceRange = filterOptions.priceRange || { min: 0, max: 20000000, step: 500000 };
    const yearRange = filterOptions.yearRange || { min: 1950, max: new Date().getFullYear() + 1, step: 1 };
    const [localFilters, setLocalFilters] = useState({
        make: filters.make || '',
        model: filters.model || '',
        province: filters.province || '',
        features: filters.features || [],
        min_price: Number(filters.min_price || priceRange.min),
        max_price: Number(filters.max_price || priceRange.max),
        min_year: Number(filters.min_year || yearRange.min),
        max_year: Number(filters.max_year || yearRange.max),
    });
    const [favoriteIds, setFavoriteIds] = useState(engagement.favoriteVehicleIds || []);
    const [comparisonIds, setComparisonIds] = useState(() => getComparisonIds());
    const [saveMessage, setSaveMessage] = useState('');
    const [compareMessage, setCompareMessage] = useState('');
    const isDark = publicTheme === 'dark';

    const modelsByMake = filterOptions.modelsByMake || {};
    const availableModels = localFilters.make ? (modelsByMake[localFilters.make] || []) : (filterOptions.models || []);
    const stats = useMemo(() => [
        { label: 'Resultados activos', value: vehicles.meta.total },
        { label: 'Página actual', value: vehicles.meta.current_page },
        { label: 'Marcas disponibles', value: filterOptions.makes.length },
    ], [vehicles, filterOptions]);

    useEffect(() => {
        if (localFilters.model && !availableModels.includes(localFilters.model)) {
            setLocalFilters((current) => ({ ...current, model: '' }));
        }
    }, [availableModels, localFilters.model]);

    useEffect(() => {
        const syncComparisonIds = () => setComparisonIds(getComparisonIds());

        window.addEventListener('storage', syncComparisonIds);
        window.addEventListener('focus', syncComparisonIds);

        return () => {
            window.removeEventListener('storage', syncComparisonIds);
            window.removeEventListener('focus', syncComparisonIds);
        };
    }, []);

    const setFilter = (key, value) => {
        setLocalFilters((current) => ({ ...current, [key]: value }));
    };

    const toggleFeature = (slug) => {
        setLocalFilters((current) => ({
            ...current,
            features: current.features.includes(slug)
                ? current.features.filter((item) => item !== slug)
                : [...current.features, slug],
        }));
    };

    const handleSubmit = (event) => {
        event.preventDefault();
        const params = new URLSearchParams();
        Object.entries(localFilters).forEach(([key, value]) => {
            if (key === 'min_price' && Number(value) <= priceRange.min) return;
            if (key === 'max_price' && Number(value) >= priceRange.max) return;
            if (key === 'min_year' && Number(value) <= yearRange.min) return;
            if (key === 'max_year' && Number(value) >= yearRange.max) return;
            if (key === 'features') {
                value.forEach((feature) => params.append('features[]', String(feature)));
                return;
            }
            if (value !== '' && value !== null && value !== undefined) params.set(key, String(value));
        });
        window.location.href = `${catalogUrl}${params.toString() ? `?${params.toString()}` : ''}`;
    };

    const clearFilters = () => {
        setLocalFilters({
            make: '',
            model: '',
            province: '',
            features: [],
            min_price: priceRange.min,
            max_price: priceRange.max,
            min_year: yearRange.min,
            max_year: yearRange.max,
        });
        window.location.href = catalogUrl;
    };

    const ensureBuyer = () => {
        if (!engagement.authenticated) {
            window.location.href = `${endpoints.loginUrl}?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
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
                name: `Búsqueda ${localFilters.make || 'general'} ${localFilters.province || ''}`.trim(),
                filters: localFilters,
                notification_frequency: 'instant',
            }),
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            setSaveMessage(payload.message || 'No se pudo guardar la búsqueda.');
            return;
        }
        setSaveMessage('Búsqueda guardada correctamente en tu panel comprador.');
    };

    return (
        <div className={`font-body md:pb-0 ${isDark ? 'theme-dark bg-[#05070b] pb-20 text-white' : 'bg-background pb-20 text-on-background'}`}>
            <PublicTopBar
                homeUrl={homeUrl}
                catalogUrl={catalogUrl}
                brandsUrl={brandsUrl}
                valuationUrl={valuationUrl}
                sellUrl={sellUrl}
                accountUrl={accountUrl}
                authUser={authUser}
                newsUrl={`${homeUrl}#noticias`}
                featuredUrl={`${catalogUrl}?featured=1`}
            />
            <main className="pt-20">
                <section className={`border-b border-outline-variant/20 py-16 sm:py-20 ${isDark ? 'bg-transparent' : 'bg-gradient-to-br from-[#eff5ff] via-white to-[#f8efe8]'}`}>
                    <div className="mx-auto grid max-w-screen-2xl grid-cols-1 gap-10 px-4 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Inventario verificado</p>
                            <h1 className={`mt-4 font-headline text-4xl font-extrabold tracking-tight sm:text-5xl ${isDark ? 'text-white' : 'text-slate-950'}`}>Encuentra el vehículo ideal con filtros útiles y resultados rápidos.</h1>
                            <p className={`mt-5 max-w-2xl text-base sm:text-lg ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>Filtra por marca, modelo, provincia, rango de precio, rango de año y características clave desde un solo lugar.</p>
                            <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                {stats.map((item) => (
                                    <div key={item.label} className={`rounded-2xl p-5 shadow-sm backdrop-blur ${isDark ? 'border border-white/10 bg-white/5' : 'border border-white/70 bg-white/80'}`}>
                                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{item.label}</p>
                                        <strong className="mt-3 block font-headline text-3xl font-extrabold text-primary">{item.value}</strong>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className={`glass-panel rounded-3xl p-5 shadow-2xl sm:p-6 ${isDark ? 'border border-white/10 bg-white/5' : 'border border-white/70'}`}>
                            <div className="mb-6 flex items-center justify-between">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Listo para explorar</p>
                                    <h2 className="mt-2 font-headline text-2xl font-extrabold tracking-tight">Tu búsqueda se mantiene entre pantallas</h2>
                                </div>
                                <Icon name="tune" className="text-[26px] text-primary" />
                            </div>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div className={`rounded-2xl border border-outline-variant/20 p-4 ${isDark ? 'bg-white/5' : 'bg-white'}`}><span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Rango de precio</span><strong className={`mt-2 block text-sm ${isDark ? 'text-white' : 'text-slate-900'}`}>{formatCRC(localFilters.min_price)} - {formatCRC(localFilters.max_price)}</strong></div>
                                <div className={`rounded-2xl border border-outline-variant/20 p-4 ${isDark ? 'bg-white/5' : 'bg-white'}`}><span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Rango de año</span><strong className={`mt-2 block text-sm ${isDark ? 'text-white' : 'text-slate-900'}`}>{localFilters.min_year} - {localFilters.max_year}</strong></div>
                            </div>
                            <div className="mt-5 flex flex-wrap items-center gap-3">
                                <span className="rounded-full bg-secondary/12 px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] text-secondary">{comparisonIds.length} en comparador</span>
                                <a href={buildComparisonsUrl(comparisonsUrl, comparisonIds)} className="rounded-full border border-secondary bg-secondary px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-white transition hover:bg-secondary-container">Ver comparador</a>
                            </div>
                            {compareMessage ? <p className={`mt-4 text-sm ${isDark ? 'text-slate-300' : 'text-slate-500'}`}>{compareMessage}</p> : null}
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-screen-2xl px-4 py-16 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 gap-8 lg:grid-cols-[340px_minmax(0,1fr)] lg:items-start">
                        <aside className="lg:sticky lg:top-28">
                            <form onSubmit={handleSubmit} className="space-y-4 rounded-[2rem] border border-outline-variant/20 bg-white p-5 shadow-xl">
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Filtros</p>
                                        <h2 className="mt-2 font-headline text-2xl font-extrabold tracking-tight">Encuentra mejor</h2>
                                    </div>
                                    <button type="button" onClick={clearFilters} className="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 transition hover:text-primary">Limpiar</button>
                                </div>

                                <label className="block rounded-2xl border border-outline-variant/30 bg-white p-4">
                                    <span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Marca</span>
                                    <select value={localFilters.make} onChange={(event) => setFilter('make', event.target.value)} className="catalog-filter-select w-full border-none bg-white p-0 font-semibold text-slate-900 focus:ring-0">
                                        <option value="">Todas</option>
                                        {filterOptions.makes.map((item) => <option key={item} value={item}>{item}</option>)}
                                    </select>
                                </label>

                                <label className="block rounded-2xl border border-outline-variant/30 bg-white p-4">
                                    <span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Modelo</span>
                                    <select value={localFilters.model} onChange={(event) => setFilter('model', event.target.value)} className="catalog-filter-select w-full border-none bg-white p-0 font-semibold text-slate-900 focus:ring-0">
                                        <option value="">Todos</option>
                                        {availableModels.map((item) => <option key={item} value={item}>{item}</option>)}
                                    </select>
                                </label>

                                <label className="block rounded-2xl border border-outline-variant/30 bg-white p-4">
                                    <span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Provincia</span>
                                    <select value={localFilters.province} onChange={(event) => setFilter('province', event.target.value)} className="catalog-filter-select w-full border-none bg-white p-0 font-semibold text-slate-900 focus:ring-0">
                                        <option value="">Todas</option>
                                        {filterOptions.provinces.map((item) => <option key={item} value={item}>{item}</option>)}
                                    </select>
                                </label>

                                <div className="rounded-2xl border border-outline-variant/30 bg-white p-4">
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Características</span>
                                        <strong className="text-xs font-extrabold text-primary">{localFilters.features.length || 'Todas'}</strong>
                                    </div>
                                    <div className="mt-4 grid gap-3">
                                        {filterOptions.features.map((feature) => (
                                            <label key={feature.slug} className="inline-flex items-center gap-3 rounded-xl border border-outline-variant/20 bg-slate-50 px-3 py-3 text-sm font-semibold text-slate-700 transition hover:border-primary hover:text-primary">
                                                <input
                                                    type="checkbox"
                                                    checked={localFilters.features.includes(feature.slug)}
                                                    onChange={() => toggleFeature(feature.slug)}
                                                    className="h-4 w-4 accent-[var(--color-secondary)]"
                                                />
                                                <span>{feature.name}</span>
                                            </label>
                                        ))}
                                    </div>
                                </div>

                                <RangeControl
                                    label="Precio"
                                    min={priceRange.min}
                                    max={priceRange.max}
                                    step={priceRange.step}
                                    valueMin={localFilters.min_price}
                                    valueMax={localFilters.max_price}
                                    onMinChange={(value) => setFilter('min_price', Math.min(value, localFilters.max_price))}
                                    onMaxChange={(value) => setFilter('max_price', Math.max(value, localFilters.min_price))}
                                    formatter={formatCRC}
                                />

                                <RangeControl
                                    label="Año"
                                    min={yearRange.min}
                                    max={yearRange.max}
                                    step={yearRange.step}
                                    valueMin={localFilters.min_year}
                                    valueMax={localFilters.max_year}
                                    onMinChange={(value) => setFilter('min_year', Math.min(value, localFilters.max_year))}
                                    onMaxChange={(value) => setFilter('max_year', Math.max(value, localFilters.min_year))}
                                    formatter={(value) => String(value)}
                                />

                                <button type="submit" className="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-secondary px-5 py-4 font-headline text-lg font-extrabold text-white transition-colors hover:bg-secondary-container">
                                    <Icon name="search" className="text-[20px]" /> Buscar vehículos
                                </button>
                                <button type="button" onClick={saveSearch} className="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-secondary bg-secondary px-5 py-3 font-headline text-sm font-bold text-white transition-colors hover:bg-secondary-container">
                                    <Icon name="notifications_active" className="text-[18px]" /> Guardar est? búsqueda
                                </button>
                                {saveMessage ? <p className="text-sm text-slate-500">{saveMessage}</p> : null}
                            </form>
                        </aside>

                        <div>
                            <div className="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Catálogo público</p>
                                    <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Vehículos disponibles</h2>
                                </div>
                                <p className="text-sm text-slate-500">Mostrando {vehicles.data.length} de {vehicles.meta.total} resultados.</p>
                            </div>

                            {vehicles.data.length ? (
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                                    {vehicles.data.map((vehicle) => (
                                        <VehicleCard
                                            key={vehicle.id}
                                            vehicle={vehicle}
                                            isFavorited={favoriteIds.includes(vehicle.id)}
                                            onFavorite={toggleFavorite}
                                        />
                                    ))}
                                </div>
                            ) : (
                                <div className="rounded-[2rem] border border-outline-variant/20 bg-white p-10 text-center shadow-xl">
                                    <Icon name="search_off" className="text-[42px] text-primary" />
                                    <h3 className="mt-4 font-headline text-2xl font-extrabold tracking-tight">No encontramos autos con esos filtros</h3>
                                    <p className="mt-3 text-sm text-slate-500">Prueba ampliar el rango de precio, cambiar el año o limpiar la marca, modelo y provincia para ver más resultados.</p>
                                </div>
                            )}
                        </div>
                    </div>
                </section>
            </main>

            <PublicFooter
                homeUrl={homeUrl}
                catalogUrl={catalogUrl}
                brandsUrl={brandsUrl}
                valuationUrl={valuationUrl}
                sellUrl={sellUrl}
                loginUrl={loginUrl || accountUrl}
                termsUrl={footerLinks.termsUrl}
                privacyUrl={footerLinks.privacyUrl}
                cookiesUrl={footerLinks.cookiesUrl}
            />
        </div>
    );
}

const element = document.getElementById('catalog-react');
if (element) {
    createRoot(element).render(<CatalogPage {...JSON.parse(element.dataset.props || '{}')} />);
}
