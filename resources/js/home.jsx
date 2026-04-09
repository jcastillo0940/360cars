import React, { useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Icon, PriceStack, PublicFooter, PublicTopBar, formatCRC } from './public-shell';
import { BrandMark } from './brand-assets';

function vehicleUrl(catalogUrl, filters) {
    const params = new URLSearchParams();

    if (filters.make) params.set('make', filters.make);
    if (filters.model) params.set('model', filters.model);
    if (filters.province) params.set('province', filters.province);
    if (filters.min_price) params.set('min_price', String(filters.min_price));
    if (filters.max_price) params.set('max_price', String(filters.max_price));
    if (filters.min_year) params.set('min_year', String(filters.min_year));
    if (filters.max_year) params.set('max_year', String(filters.max_year));

    const query = params.toString();
    return `${catalogUrl}${query ? `?${query}` : ''}`;
}

function HomeVehicleCard({ vehicle, compact = false }) {
    return (
        <a href={vehicle.url} className="group overflow-hidden rounded-[1.75rem] border border-outline-variant/20 bg-white shadow-xl transition-all hover:-translate-y-1 hover:shadow-2xl">
            <div className={`relative overflow-hidden ${compact ? 'h-52' : 'h-64'}`}>
                <img src={vehicle.image} alt={vehicle.title} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                {vehicle.is_featured ? (
                    <span className="absolute left-4 top-4 rounded-full bg-secondary px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950">
                        Destacado
                    </span>
                ) : null}
                {vehicle.original_price ? (
                    <span className="absolute right-4 top-4 rounded-full bg-slate-950/85 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white">
                        Rebajado
                    </span>
                ) : null}
            </div>
            <div className="p-5 sm:p-6">
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{vehicle.make} {vehicle.model}</p>
                        <h3 className="mt-2 font-headline text-2xl font-extrabold tracking-tight text-slate-950">{vehicle.title}</h3>
                        <p className="mt-2 text-sm text-slate-500">{vehicle.year} · {vehicle.province || vehicle.city || 'Costa Rica'} · {vehicle.published_label}</p>
                    </div>
                    <PriceStack primary={vehicle.price} secondary={vehicle.price_secondary} align="right" />
                </div>
                {vehicle.original_price ? <p className="mt-4 text-sm font-semibold text-slate-400 line-through">Antes {vehicle.original_price}</p> : null}
            </div>
        </a>
    );
}

function BrandChip({ make, href }) {
    return (
        <a href={href} className="group rounded-[1.5rem] border border-outline-variant/20 bg-white p-5 shadow-lg transition hover:-translate-y-1 hover:border-primary hover:shadow-xl">
            <div className="flex items-center gap-4">
                <BrandMark name={make.name} />
                <div>
                    <strong className="block font-headline text-xl font-extrabold text-slate-950">{make.name}</strong>
                    <span className="mt-1 block text-sm text-slate-500">{make.listings_count || 0} autos en venta</span>
                </div>
            </div>
        </a>
    );
}

function HomePage({
    homeUrl,
    brandsUrl,
    catalogUrl,
    valuationUrl,
    sellUrl,
    accountUrl,
    loginUrl,
    authUser,
    publicTheme = 'light',
    featuredVehicles = [],
    recentVehicles = [],
    offerVehicles = [],
    catalogMakes = [],
    catalogProvinces = [],
    catalogPriceCeiling = 20000000,
    catalogYearRange = { min: 1950, max: new Date().getFullYear() + 1 },
    footerLinks,
}) {
    const isDark = publicTheme === 'dark';
    const [filters, setFilters] = useState({
        make: '',
        model: '',
        province: '',
        min_price: 0,
        max_price: catalogPriceCeiling,
        min_year: catalogYearRange.min,
        max_year: catalogYearRange.max,
    });

    const selectedMake = useMemo(
        () => catalogMakes.find((item) => item.name === filters.make) || null,
        [catalogMakes, filters.make],
    );

    const models = selectedMake?.models || [];
    const yearTrackLeft = ((filters.min_year - catalogYearRange.min) / Math.max(catalogYearRange.max - catalogYearRange.min, 1)) * 100;
    const yearTrackRight = ((filters.max_year - catalogYearRange.min) / Math.max(catalogYearRange.max - catalogYearRange.min, 1)) * 100;
    const priceTrackLeft = (filters.min_price / Math.max(catalogPriceCeiling, 1)) * 100;
    const priceTrackRight = (filters.max_price / Math.max(catalogPriceCeiling, 1)) * 100;

    const submitSearch = (event) => {
        event.preventDefault();
        window.location.href = vehicleUrl(catalogUrl, filters);
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
                newsUrl={'/noticias'}
                featuredUrl={`${homeUrl}#destacados`}
                transparent={true}
            />

            <main>
                <section className="relative isolate flex min-h-[90vh] flex-col items-center justify-center overflow-hidden border-b border-outline-variant/5 bg-[#05070b] py-20 text-white">
                    <div className="absolute inset-0 z-0">
                        <div className="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-[#05070b]" />
                        <img
                            src="/luxury_car_showroom_dark_1775669453755.png"
                            className="h-full w-full object-cover"
                            alt="Background"
                        />
                    </div>

                    <div className="relative z-10 mx-auto w-full max-w-screen-2xl px-4 text-center sm:px-6 lg:px-8">
                        <h1 className="mx-auto max-w-4xl font-headline text-5xl font-extrabold tracking-tight text-white sm:text-7xl lg:text-8xl">
                            Compra y vende autos de forma inteligente
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg font-medium text-white/80 sm:text-xl">
                            Compara precios, analiza opciones y encuentra el vehículo ideal para vos.
                        </p>

                        <div className="mx-auto mt-12 w-full max-w-5xl">
                            <form onSubmit={submitSearch} className="hero-search-shell shadow-2xl">
                                <div className="hero-search-grid">
                                    <section className="hero-search-block">
                                        <span className="hero-search-label">VEHÍCULO</span>
                                        <div className="flex gap-2">
                                            <div className="flex-1">
                                                <label className="hero-select-pill">
                                                    <Icon name="directions_car" className="text-[22px] text-white/72" />
                                                    <select value={filters.make} onChange={(event) => setFilters((current) => ({ ...current, make: event.target.value, model: '' }))} className="public-hero-select hero-search-select">
                                                        <option value="">Marca</option>
                                                        {catalogMakes.map((make) => <option key={make.id} value={make.name}>{make.name}</option>)}
                                                    </select>
                                                </label>
                                            </div>
                                            <div className="flex-1">
                                                <label className="hero-select-pill">
                                                    <Icon name="sell" className="text-[22px] text-white/72" />
                                                    <select value={filters.model} onChange={(event) => setFilters((current) => ({ ...current, model: event.target.value }))} className="public-hero-select hero-search-select" disabled={!selectedMake}>
                                                        <option value="">Modelo (ej)</option>
                                                        {models.map((model) => <option key={model.id} value={model.name}>{model.name}</option>)}
                                                    </select>
                                                </label>
                                            </div>
                                        </div>
                                    </section>

                                    <section className="hero-search-block">
                                        <span className="hero-search-label">PRECIO</span>
                                        <div className="flex flex-col gap-1">
                                            <strong className="hero-search-value">{formatCRC(filters.min_price)} - {filters.max_price >= catalogPriceCeiling ? 'Sin límite' : formatCRC(filters.max_price)}</strong>
                                            <div className="hero-range-single pt-1">
                                                <div className="hero-range-track" aria-hidden="true">
                                                    <span className="hero-range-track__active" style={{ left: `${priceTrackLeft}%`, width: `${Math.max(priceTrackRight - priceTrackLeft, 0)}%` }} />
                                                </div>
                                                <input min="0" max={catalogPriceCeiling} step="500000" className="brand-range hero-dark-range hero-dark-range--overlay w-full" type="range" value={filters.min_price} onChange={(event) => setFilters((current) => ({ ...current, min_price: Math.min(Number(event.target.value), current.max_price) }))} />
                                                <input min="0" max={catalogPriceCeiling} step="500000" className="brand-range hero-dark-range hero-dark-range--overlay w-full" type="range" value={filters.max_price} onChange={(event) => setFilters((current) => ({ ...current, max_price: Math.max(Number(event.target.value), current.min_price) }))} />
                                            </div>
                                            <div className="hero-price-meta"><span>{formatCRC(0)}</span><span>Sin límite</span></div>
                                        </div>
                                    </section>

                                    <section className="hero-search-block">
                                        <span className="hero-search-label">AÑO</span>
                                        <div className="flex flex-col gap-1">
                                            <strong className="hero-search-value">{filters.min_year} - {filters.max_year}</strong>
                                            <div className="hero-range-single pt-1">
                                                <div className="hero-range-track" aria-hidden="true">
                                                    <span className="hero-range-track__active" style={{ left: `${yearTrackLeft}%`, width: `${Math.max(yearTrackRight - yearTrackLeft, 0)}%` }} />
                                                </div>
                                                <input min={catalogYearRange.min} max={catalogYearRange.max} step="1" className="brand-range hero-dark-range hero-dark-range--overlay w-full" type="range" value={filters.min_year} onChange={(event) => setFilters((current) => ({ ...current, min_year: Math.min(Number(event.target.value), current.max_year) }))} />
                                                <input min={catalogYearRange.min} max={catalogYearRange.max} step="1" className="brand-range hero-dark-range hero-dark-range--overlay w-full" type="range" value={filters.max_year} onChange={(event) => setFilters((current) => ({ ...current, max_year: Math.max(Number(event.target.value), current.min_year) }))} />
                                            </div>
                                            <div className="hero-price-meta"><span>{catalogYearRange.min}</span><span>{catalogYearRange.max}</span></div>
                                        </div>
                                    </section>

                                    <section className="hero-search-block">
                                        <span className="hero-search-label">UBICACIÓN</span>
                                        <label className="hero-select-pill">
                                            <Icon name="location_on" className="text-[22px] text-white/72" />
                                            <select value={filters.province} onChange={(event) => setFilters((current) => ({ ...current, province: event.target.value }))} className="public-hero-select hero-search-select">
                                                <option value="">Todo el país</option>
                                                {catalogProvinces.map((province) => <option key={province} value={province}>{province}</option>)}
                                            </select>
                                        </label>
                                    </section>

                                    <div className="hero-search-cta">
                                        <button type="submit" className="hero-search-submit">
                                            <Icon name="search" className="text-[24px]" />
                                            <span>
                                                <strong>Buscar autos</strong>
                                                <small>Búsqueda refinada</small>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div className="mt-8 flex justify-center">
                                <a href={valuationUrl} className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-black/40 px-6 py-2.5 text-sm font-bold text-white backdrop-blur-md transition hover:bg-black/60">
                                    <Icon name="analytics" className="text-[20px]" />
                                    <span>Probar tasador</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-screen-2xl px-4 py-16 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-end justify-between gap-4">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Recientes</p>
                            <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Vehículos recién ingresados</h2>
                        </div>
                        <a href={catalogUrl} className="text-sm font-bold text-primary hover:underline">Ver todos</a>
                    </div>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                        {recentVehicles.map((vehicle) => <HomeVehicleCard key={vehicle.id} vehicle={vehicle} />)}
                    </div>
                </section>

                {offerVehicles.length ? (
                    <section className="mx-auto max-w-screen-2xl px-4 pb-16 sm:px-6 lg:px-8">
                        <div className="rounded-[2.2rem] bg-slate-950 px-6 py-8 text-white shadow-2xl sm:px-8 sm:py-10">
                            <div className="mb-8 flex items-end justify-between gap-4">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Ofertas</p>
                                    <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Autos con precio ajustado</h2>
                                </div>
                                <a href={`${catalogUrl}?offers=1`} className="text-sm font-bold text-white hover:text-secondary">Ver ofertas</a>
                            </div>
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                                {offerVehicles.map((vehicle) => <HomeVehicleCard key={vehicle.id} vehicle={vehicle} compact />)}
                            </div>
                        </div>
                    </section>
                ) : null}

                <section id="destacados" className="mx-auto max-w-screen-2xl px-4 pb-16 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-end justify-between gap-4">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Destacados</p>
                            <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Selección recomendada</h2>
                        </div>
                        <a href={catalogUrl} className="text-sm font-bold text-primary hover:underline">Explorar inventario</a>
                    </div>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                        {featuredVehicles.map((vehicle) => <HomeVehicleCard key={vehicle.id} vehicle={vehicle} compact />)}
                    </div>
                </section>

                <section id="marcas" className="mx-auto max-w-screen-2xl px-4 pb-16 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-end justify-between gap-4">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Marcas</p>
                            <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Explora por fabricante</h2>
                        </div>
                        <a href={brandsUrl} className="text-sm font-bold text-primary hover:underline">Ver todas las marcas</a>
                    </div>
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        {catalogMakes.slice(0, 8).map((make) => (
                            <BrandChip key={make.id} make={make} href={`${catalogUrl}?make=${encodeURIComponent(make.name)}`} />
                        ))}
                    </div>
                </section>

                <section id="noticias" className="mx-auto max-w-screen-2xl px-4 pb-8 sm:px-6 lg:px-8">
                    <div className="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="rounded-[2rem] border border-outline-variant/20 bg-white p-6 shadow-xl sm:p-8">
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Estimación de mercado</p>
                            <h2 className="mt-3 font-headline text-3xl font-extrabold tracking-tight text-slate-950">Conoce un rango real antes de publicar.</h2>
                            <p className="mt-4 max-w-2xl text-base leading-8 text-slate-600">Usa la herramienta de estimación para entender mejor el rango de mercado de tu auto y pasar luego a venderlo con más contexto.</p>
                            <div className="mt-6 flex flex-wrap gap-3">
                                <a href={valuationUrl} className="inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-3 font-headline text-sm font-extrabold text-slate-950 transition hover:bg-[#ffb83a]">Ir a la estimación</a>
                                <a href={sellUrl} className="inline-flex items-center justify-center rounded-2xl border border-outline-variant/30 px-5 py-3 font-headline text-sm font-extrabold text-slate-700 transition hover:border-primary hover:text-primary">Publicar mi auto</a>
                            </div>
                        </div>
                        <div className="rounded-[2rem] border border-outline-variant/20 bg-slate-950 p-6 text-white shadow-xl sm:p-8">
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Noticias</p>
                            <h2 className="mt-3 font-headline text-3xl font-extrabold tracking-tight text-white">Editorial Movikaa: El mercado en tus manos.</h2>
                            <p className="mt-4 text-base leading-8 text-slate-300">Descubre análisis profundos sobre la restricción vehicular, consejos de compra y las últimas tendencias del mercado automotriz tico.</p>
                            <div className="mt-6">
                                <a href="/noticias" className="inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-3 font-headline text-sm font-extrabold text-slate-950 transition hover:bg-[#ffb83a]">Ver todas las noticias</a>
                            </div>
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

const element = document.getElementById('home-react');
if (element) {
    createRoot(element).render(<HomePage {...JSON.parse(element.dataset.props || '{}')} />);
}
