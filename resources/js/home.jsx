import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Icon, PriceStack, PublicFooter, PublicTopBar, formatCRC } from './public-shell';

const quickLinks = [
    { label: 'Usados', icon: 'directions_car', href: '#populares' },
    { label: 'Tasador', icon: 'query_stats', kind: 'valuation' },
    { label: 'Electricos', icon: 'electric_car', href: '#destacados' },
    { label: 'Ofertas', icon: 'sell', href: '#destacados' },
    { label: 'Financiamiento', icon: 'account_balance', href: '#ciudades' },
    { label: 'Certificados', icon: 'verified', href: '#destacados' },
];

const brandLogos = {
    Toyota: 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Toyota_carlogo.svg',
    Hyundai: 'https://upload.wikimedia.org/wikipedia/commons/4/44/Hyundai_Motor_Company_logo.svg',
    Nissan: 'https://upload.wikimedia.org/wikipedia/commons/2/23/Nissan_2020_logo.svg',
    Suzuki: 'https://upload.wikimedia.org/wikipedia/commons/1/12/Suzuki_logo_2.svg',
    Mitsubishi: 'https://upload.wikimedia.org/wikipedia/commons/b/bd/Mitsubishi_logo.svg',
    BMW: 'https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg',
    Honda: 'https://upload.wikimedia.org/wikipedia/commons/7/7b/Honda-logo.svg',
    Kia: 'https://upload.wikimedia.org/wikipedia/commons/1/13/Kia-logo.png',
    Mazda: 'https://upload.wikimedia.org/wikipedia/commons/3/32/Mazda_logo_with_emblem.svg',
    Changan: 'https://upload.wikimedia.org/wikipedia/commons/8/80/Changan_Automobile_logo.svg',
    Chery: 'https://upload.wikimedia.org/wikipedia/commons/0/0e/Chery_logo.svg',
    Geely: 'https://upload.wikimedia.org/wikipedia/commons/6/62/Geely_logo.svg',
    BYD: 'https://upload.wikimedia.org/wikipedia/commons/7/7f/BYD_Auto_2022_logo.svg',
};

const fallbackPopular = [
    { title: 'Toyota Hilux 2023', meta: 'Diesel · 4x4 · Automatico', price: '₡21.400.000', priceSecondary: '≈ US$42,500', image: 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80', href: '/inventario' },
    { title: 'Hyundai Tucson 2021', meta: 'Gasolina · 4x2 · Automatico', price: '₡14.600.000', priceSecondary: '≈ US$28,900', image: 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1200&q=80', href: '/inventario' },
    { title: 'Nissan Frontier 2022', meta: 'Diesel · 4x4 · Manual', price: '₡19.700.000', priceSecondary: '≈ US$39,000', image: 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80', href: '/inventario' },
    { title: 'Toyota Prado 2020', meta: 'Diesel · 4x4 · Automatico', price: '₡27.800.000', priceSecondary: '≈ US$55,000', image: 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=80', href: '/inventario' },
];

const fallbackRecent = [
    { title: 'Mitsubishi L200 2024', meta: 'San Jose · Hace 2 horas', price: '₡23.700.000', priceSecondary: '≈ US$46,900', image: 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=900&q=80', href: '/inventario' },
    { title: 'Hyundai Elantra 2023', meta: 'Heredia · Hace 5 horas', price: '₡12.400.000', priceSecondary: '≈ US$24,500', image: 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=900&q=80', href: '/inventario' },
];

const posts = [
    { category: 'Tendencias', title: 'El auge de los autos electricos en San Jose', excerpt: 'Analizamos como la infraestructura de carga esta transformando el mercado nacional.', image: 'https://images.unsplash.com/photo-1593941707882-a5bac6861d75?auto=format&fit=crop&w=1200&q=80' },
    { category: 'Consejos', title: 'Guia de mantenimiento para 4x4 en epoca lluviosa', excerpt: 'Tips esenciales para preparar tu vehiculo para las condiciones de nuestras carreteras.', image: 'https://images.unsplash.com/photo-1549924231-f129b911e442?auto=format&fit=crop&w=1200&q=80' },
    { category: 'Mercado', title: 'Por que el Toyota Hilux sigue siendo el rey', excerpt: 'Un recorrido por la historia y fiabilidad del pick-up mas vendido del pais.', image: 'https://images.unsplash.com/photo-1502161254066-6c74afbf07aa?auto=format&fit=crop&w=1200&q=80' },
];

function VehicleCard({ car, paid = false }) {
    return (
        <article className="group overflow-hidden rounded-xl border border-outline-variant/20 bg-white transition-all hover:-translate-y-1 hover:shadow-2xl">
            <a href={car.href}>
                <div className="relative h-56 overflow-hidden">
                    <img src={car.image} alt={car.title} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                    {car.badge ? <div className="absolute left-3 top-3 rounded bg-secondary px-2 py-1 text-[10px] font-bold text-white">{car.badge}</div> : null}
                </div>
                <div className="p-5">
                    <h3 className="font-headline text-lg font-bold">{car.title}</h3>
                    <p className="mt-1 text-xs text-slate-500">{car.meta}</p>
                    <div className="mt-4 flex items-center justify-between gap-3">
                        <PriceStack primary={car.price} secondary={car.priceSecondary} />
                        {paid ? <span className="rounded-full bg-primary-fixed px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Destacado</span> : null}
                    </div>
                </div>
            </a>
        </article>
    );
}

function HomePage({
    homeUrl,
    buyUrl,
    catalogUrl,
    valuationUrl,
    sellUrl,
    accountUrl,
    loginUrl,
    authUser,
    publicTheme = 'light',
    featuredPaidVehicles = [],
    recentVehicles = [],
    catalogMakes = [],
    catalogCities = [],
    catalogPriceCeiling = 20000000,
    footerLinks = {},
}) {
    const isDark = publicTheme === 'dark';
    const [filters, setFilters] = useState({
        make: '',
        model: '',
        city: '',
        minPrice: 0,
        maxPrice: catalogPriceCeiling,
    });

    const selectedMake = useMemo(() => catalogMakes.find((entry) => entry.name === filters.make) || null, [catalogMakes, filters.make]);
    const availableModels = selectedMake?.models || [];
    const featuredCars = featuredPaidVehicles.length ? featuredPaidVehicles.map((vehicle) => ({
        title: vehicle.title,
        meta: `${vehicle.plan_name} · ${vehicle.visibility_bucket}`,
        price: vehicle.price,
        priceSecondary: vehicle.price_secondary,
        image: vehicle.image,
        href: vehicle.url,
        badge: vehicle.is_featured ? 'DESTACADO' : 'PAGO',
    })) : fallbackPopular;
    const recentCars = recentVehicles.length ? recentVehicles.map((vehicle) => ({
        title: vehicle.title,
        meta: `${vehicle.city || 'Costa Rica'} · ${vehicle.published_label || 'Recien publicado'}`,
        price: vehicle.price,
        priceSecondary: vehicle.price_secondary,
        image: vehicle.image,
        href: vehicle.url,
        badge: null,
    })) : fallbackRecent;
    const popularCars = featuredPaidVehicles.length ? featuredPaidVehicles.map((vehicle) => ({
        title: vehicle.title,
        meta: `${vehicle.city || 'Costa Rica'} · ${vehicle.published_label || 'Recien publicado'}`,
        price: vehicle.price,
        priceSecondary: vehicle.price_secondary,
        image: vehicle.image,
        href: vehicle.url,
        badge: vehicle.is_featured ? 'PAGO' : null,
    })) : fallbackPopular;
    const featuredBrands = useMemo(() => {
        const withLogo = catalogMakes.filter((make) => brandLogos[make.name]);
        return (withLogo.length ? withLogo : catalogMakes).slice(0, 6);
    }, [catalogMakes]);

    useEffect(() => {
        if (filters.model && !availableModels.some((model) => model.name === filters.model)) {
            setFilters((current) => ({ ...current, model: '' }));
        }
    }, [availableModels, filters.model]);

    const setFilter = (key, value) => {
        setFilters((current) => ({ ...current, [key]: value }));
    };

    const priceRangeLabel = useMemo(() => {
        const min = Number(filters.minPrice);
        const max = Number(filters.maxPrice);

        if (min <= 0 && max >= Number(catalogPriceCeiling)) {
            return '₡0 - Sin límite';
        }

        return `${formatCRC(min)} - ${max >= Number(catalogPriceCeiling) ? 'Sin límite' : formatCRC(max)}`;
    }, [filters.minPrice, filters.maxPrice, catalogPriceCeiling]);

    const handleSearch = (event) => {
        event.preventDefault();
        const params = new URLSearchParams();
        if (filters.make) params.set('make', filters.make);
        if (filters.model) params.set('model', filters.model);
        if (filters.city) params.set('city', filters.city);
        if (Number(filters.minPrice) > 0) params.set('min_price', String(filters.minPrice));
        if (Number(filters.maxPrice) < Number(catalogPriceCeiling)) params.set('max_price', String(filters.maxPrice));
        window.location.href = `${catalogUrl}${params.toString() ? `?${params.toString()}` : ''}`;
    };

    return (
        <div className={`font-body md:pb-0 ${isDark ? 'theme-dark bg-[#05070b] text-white' : 'bg-background pb-20 text-on-background'}`}>
            <PublicTopBar
                homeUrl={homeUrl}
                catalogUrl={catalogUrl}
                valuationUrl={valuationUrl}
                sellUrl={sellUrl}
                accountUrl={accountUrl}
                authUser={authUser}
                newsUrl={`${homeUrl}#noticias`}
                featuredUrl={`${homeUrl}#destacados`}
            />

            <main className="pt-20">
                <section className="relative flex min-h-[720px] items-center justify-center overflow-hidden sm:min-h-[760px]">
                    <div className="absolute inset-0 z-0">
                        <img className="h-full w-full object-cover" src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1800&q=80" alt="SUV moderno recorriendo Costa Rica" />
                        <div className="absolute inset-0 bg-gradient-to-b from-black/40 via-black/35 to-black/55"></div>
                    </div>
                    <div className="relative z-10 w-full max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                        <div className="mx-auto mb-8 max-w-3xl text-center sm:mb-10">
                            <h1 className="text-balance font-headline text-4xl font-extrabold leading-none tracking-tight text-white sm:text-6xl lg:text-7xl">Encuentre su auto ideal en Costa Rica</h1>
                            <p className="mx-auto mt-4 max-w-2xl text-base font-medium text-white/85 sm:text-xl">La plataforma mas completa para comprar y vender vehiculos con confianza.</p>
                        </div>
                        <form onSubmit={handleSearch} className="hero-search-shell mx-auto max-w-6xl shadow-2xl">
                            <div className="hero-search-grid">
                                <section className="hero-search-block hero-search-block--vehicle">
                                    <span className="hero-search-label">Vehiculo</span>
                                    <div className="hero-vehicle-stack hero-vehicle-stack--inline">
                                        <label className="hero-select-pill">
                                            <span className="material-symbols-outlined text-[22px] text-white/72" aria-hidden="true">directions_car</span>
                                            <select value={filters.make} onChange={(event) => setFilter('make', event.target.value)} className="public-hero-select hero-search-select">
                                                <option value="">Marca</option>
                                                {catalogMakes.map((make) => <option key={make.id} value={make.name}>{make.name}</option>)}
                                            </select>
                                        </label>
                                        <label className="hero-select-pill">
                                            <span className="material-symbols-outlined text-[22px] text-white/72" aria-hidden="true">sell</span>
                                            <select value={filters.model} onChange={(event) => setFilter('model', event.target.value)} className="public-hero-select hero-search-select" disabled={!filters.make && !availableModels.length}>
                                                <option value="">Modelo (ej: Corolla)</option>
                                                {availableModels.map((model) => <option key={model.id} value={model.name}>{model.name}</option>)}
                                            </select>
                                        </label>
                                    </div>
                                </section>

                                <section className="hero-search-block hero-search-block--price hero-search-block--price-compact">
                                    <span className="hero-search-label">Precio</span>
                                    <strong className="hero-search-value">{priceRangeLabel}</strong>
                                    <div className="hero-range-single">
                                        <div className="hero-range-track" aria-hidden="true">
                                            <span
                                                className="hero-range-track__active"
                                                style={{
                                                    left: `${(Number(filters.minPrice) / Number(catalogPriceCeiling)) * 100}%`,
                                                    width: `${((Number(filters.maxPrice) - Number(filters.minPrice)) / Number(catalogPriceCeiling)) * 100}%`,
                                                }}
                                            />
                                        </div>
                                        <input type="range" min="0" max={catalogPriceCeiling} step="500000" value={filters.minPrice} onChange={(event) => setFilter('minPrice', Math.min(Number(event.target.value), filters.maxPrice))} className="brand-range hero-dark-range hero-dark-range--overlay w-full" />
                                        <input type="range" min="0" max={catalogPriceCeiling} step="500000" value={filters.maxPrice} onChange={(event) => setFilter('maxPrice', Math.max(Number(event.target.value), filters.minPrice))} className="brand-range hero-dark-range hero-dark-range--overlay w-full" />
                                    </div>
                                    <div className="hero-price-meta">
                                        <span>{formatCRC(filters.minPrice)}</span>
                                        <span>{Number(filters.maxPrice) >= Number(catalogPriceCeiling) ? 'Sin límite' : formatCRC(filters.maxPrice)}</span>
                                    </div>
                                </section>

                                <section className="hero-search-block hero-search-block--location">
                                    <span className="hero-search-label">Ubicacion</span>
                                    <label className="hero-select-pill">
                                        <span className="material-symbols-outlined text-[22px] text-white/72" aria-hidden="true">location_on</span>
                                        <select value={filters.city} onChange={(event) => setFilter('city', event.target.value)} className="public-hero-select hero-search-select">
                                            <option value="">Todo el pais</option>
                                            {catalogCities.map((city) => <option key={city} value={city}>{city}</option>)}
                                        </select>
                                    </label>
                                </section>

                                <div className="hero-search-cta">
                                    <button type="submit" className="hero-search-submit hero-search-submit--compact">
                                        <span className="material-symbols-outlined text-[24px]" aria-hidden="true">search</span>
                                        <span>
                                            <strong>Buscar autos</strong>
                                            <small>Busqueda refinada</small>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div className="mx-auto mt-4 flex max-w-5xl justify-center"><a href={valuationUrl} className="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-5 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white/15"><Icon name="query_stats" className="text-[18px]" />Probar tasador</a></div>
                    </div>
                </section>

                <section className="border-b border-outline-variant/20 bg-white py-10 sm:py-12"><div className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 lg:gap-6">{quickLinks.map((link) => <a key={link.label} href={link.kind === 'valuation' ? valuationUrl : link.href} className="group flex flex-col items-center gap-3 rounded-xl p-4 text-center transition-colors hover:bg-surface-container-low"><div className="flex h-14 w-14 items-center justify-center rounded-full bg-primary-fixed text-primary transition-all group-hover:bg-primary group-hover:text-white"><Icon name={link.icon} className="text-[28px]" /></div><span className="text-xs font-bold uppercase tracking-[0.2em]">{link.label}</span></a>)}</div></div></section>
                <section id="marcas" className="bg-surface-container-low py-16 sm:py-20"><div className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div className="mb-10 flex items-center justify-between gap-4"><h2 className="font-headline text-3xl font-extrabold tracking-tight">Buscar por Marca</h2><a href={buyUrl} className="text-sm font-bold text-primary hover:underline sm:text-base">Ver todas las marcas</a></div><div className="grid grid-cols-3 gap-4 md:grid-cols-6">{featuredBrands.map((brand) => <a key={brand.id} href={`${catalogUrl}?make=${encodeURIComponent(brand.name)}`} className="flex items-center justify-center rounded-xl border border-outline-variant/10 bg-white p-6 grayscale transition-all hover:-translate-y-1 hover:shadow-xl hover:grayscale-0 sm:p-8"><img src={brandLogos[brand.name] || 'https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg'} alt={brand.name} className="h-8 max-w-full object-contain sm:h-10" /></a>)}</div></div></section>
                <section id="populares" className="mx-auto max-w-screen-2xl px-4 py-18 sm:px-6 sm:py-24 lg:px-8"><div className="mb-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-secondary">Los mas buscados</span><h2 className="font-headline text-4xl font-extrabold tracking-tight">Autos Populares</h2></div><a href={buyUrl} className="inline-flex items-center gap-2 font-bold text-primary hover:underline">Explorar populares <Icon name="arrow_forward" className="text-[18px]" /></a></div><div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">{popularCars.map((car) => <VehicleCard key={car.title} car={car} />)}</div></section>
                <section id="destacados" className="bg-surface-container-low py-16 sm:py-20"><div className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div className="mb-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-secondary">Monetizacion visible</span><h2 className="font-headline text-4xl font-extrabold tracking-tight">Autos Destacados</h2></div><a href={`${catalogUrl}?visibility=paid`} className="inline-flex items-center gap-2 font-bold text-primary hover:underline">Ver destacados pagos <Icon name="arrow_forward" className="text-[18px]" /></a></div><div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">{featuredCars.map((car) => <VehicleCard key={`paid-${car.title}`} car={car} paid />)}</div></div></section>
                <section className="bg-slate-50 py-16 sm:py-20"><div className="mx-auto grid max-w-screen-2xl grid-cols-1 gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8"><div><div className="mb-8 flex items-center justify-between border-b border-outline-variant pb-4"><h2 className="font-headline text-2xl font-extrabold tracking-tight">Recien Ingresados</h2><a href={buyUrl} className="text-sm font-bold text-primary">Ver todos</a></div><div className="space-y-4">{recentCars.map((car) => <a key={car.title} href={car.href} className="flex gap-4 rounded-lg border border-outline-variant/10 bg-white p-3 transition-shadow hover:shadow-lg"><img src={car.image} alt={car.title} className="h-24 w-32 rounded-md object-cover" /><div className="flex-1 py-1"><h4 className="font-bold">{car.title}</h4><p className="mb-2 text-xs text-slate-500">{car.meta}</p><PriceStack primary={car.price} secondary={car.priceSecondary} /></div></a>)}</div></div><div><div className="mb-8 flex items-center justify-between border-b border-outline-variant pb-4"><h2 className="font-headline text-2xl font-extrabold tracking-tight">Luxury Picks</h2><a href={buyUrl} className="text-sm font-bold text-primary">Gama Alta</a></div><div className="space-y-4">{fallbackPopular.slice(0, 2).map((car) => <a key={`luxury-${car.title}`} href={car.href} className="flex gap-4 rounded-lg border border-outline-variant/10 bg-white p-3 transition-shadow hover:shadow-lg"><img src={car.image} alt={car.title} className="h-24 w-32 rounded-md object-cover" /><div className="flex-1 py-1"><h4 className="font-bold">{car.title}</h4><p className="mb-2 text-xs text-slate-500">{car.meta}</p><PriceStack primary={car.price} secondary={car.priceSecondary} /></div></a>)}</div></div></div></section>
                <section id="ciudades" className="bg-white py-16 text-center sm:py-20"><div className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><h2 className="mb-12 font-headline text-3xl font-extrabold tracking-tight">Buscar por Ciudad</h2><div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6 lg:gap-6">{catalogCities.slice(0, 6).map((city) => <a key={city} href={`${catalogUrl}?city=${encodeURIComponent(city)}`} className="rounded-xl border border-outline-variant/30 p-5 transition-all hover:border-primary hover:bg-primary-fixed sm:p-6"><Icon name="location_city" className="mb-3 text-[28px] text-primary" /><h4 className="font-bold">{city}</h4><p className="text-xs text-slate-500">Ver inventario</p></a>)}</div></div></section>
                <section className="relative overflow-hidden bg-primary py-18 text-on-primary sm:py-24"><div className="absolute inset-y-0 right-0 hidden w-1/2 opacity-30 lg:block"><img className="h-full w-full object-cover" src="https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=1400&q=80" alt="Llaves de automovil" /></div><div className="relative z-10 mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"><div className="max-w-2xl"><h2 className="font-headline text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">Venda su auto en Movikaa</h2><p className="mt-6 text-lg text-white/90 sm:text-xl">La forma mas rapida y segura de vender en Costa Rica. Sin intermediarios, sin complicaciones.</p><div className="mt-8 grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-6">{['Publicacion Gratis', 'Trato Directo', 'Maximo Alcance'].map((item) => <div key={item} className="flex items-center gap-3"><Icon name="check_circle" filled className="text-[24px] text-secondary" /><span className="font-bold">{item}</span></div>)}</div><div className="mt-8 flex flex-wrap gap-3"><a href={sellUrl} className="inline-flex items-center justify-center rounded bg-secondary px-8 py-4 font-headline text-lg font-extrabold text-white shadow-2xl transition-transform hover:scale-[1.02] hover:bg-secondary-container">Empezar a Vender</a><a href={valuationUrl} className="inline-flex items-center justify-center rounded border border-white/30 px-8 py-4 font-headline text-lg font-extrabold text-white transition hover:bg-white/10">Tasador de autos</a></div></div></div></section>
                <section id="noticias" className="mx-auto max-w-screen-2xl border-t border-outline-variant/10 px-4 py-18 sm:px-6 sm:py-24 lg:px-8"><div className="mb-12 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><h2 className="font-headline text-3xl font-extrabold tracking-tight">Ultimas Noticias</h2><p className="text-slate-500">Mantengase al dia con el mundo automotriz</p></div><a href={buyUrl} className="font-bold text-primary hover:underline">Ver el blog</a></div><div className="grid grid-cols-1 gap-8 md:grid-cols-3">{posts.map((post) => <article key={post.title} className="group cursor-pointer"><div className="relative mb-4 aspect-[16/9] overflow-hidden rounded-xl"><img src={post.image} alt={post.title} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" /></div><span className="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-secondary">{post.category}</span><h3 className="font-headline text-xl font-bold transition-colors group-hover:text-primary">{post.title}</h3><p className="mt-3 text-sm text-slate-500">{post.excerpt}</p></article>)}</div></section>
            </main>

            <PublicFooter
                homeUrl={homeUrl}
                catalogUrl={catalogUrl}
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
    const props = JSON.parse(element.dataset.props || '{}');
    createRoot(element).render(<HomePage {...props} />);
}





