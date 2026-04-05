import React, { useState } from 'react';
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

function PriceStack({ primary, secondary, large = false }) {
    return (
        <div>
            <span className={large ? 'block text-4xl font-black text-primary' : 'block text-xl font-black text-primary'}>{primary}</span>
            {secondary ? <span className={large ? 'mt-2 block text-sm font-semibold text-slate-400' : 'mt-1 block text-[11px] font-semibold text-slate-400'}>{secondary}</span> : null}
        </div>
    );
}

async function requestJson(url, method, csrfToken, body = null) {
    const response = await fetch(url, {
        method,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: body ? JSON.stringify(body) : null,
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(payload.message || 'No fue posible completar la accion.');
    }
    return payload;
}

function VehicleShowPage({ homeUrl, catalogUrl, sellUrl, accountUrl, publicTheme = 'light', vehicle, relatedVehicles, engagement, endpoints }) {
    const [activeImage, setActiveImage] = useState(vehicle.media[0]?.url || vehicle.primary_image);
    const [favorited, setFavorited] = useState((engagement.favoriteVehicleIds || []).includes(vehicle.id));
    const [compared, setCompared] = useState((engagement.comparisonVehicleIds || []).includes(vehicle.id));
    const [message, setMessage] = useState('');
    const [messageState, setMessageState] = useState('');
    const isDark = publicTheme === 'dark';
    const specs = [
        ['Marca', vehicle.make],
        ['Modelo', vehicle.model],
        ['Ano', vehicle.year],
        ['Combustible', vehicle.fuel_type],
        ['Transmision', vehicle.transmission],
        ['Carroceria', vehicle.body_type],
        ['Condicion', vehicle.condition],
        ['Kilometraje', vehicle.mileage ? `${vehicle.mileage} ${vehicle.mileage_unit}` : 'No indicado'],
    ];

    const ensureBuyer = () => {
        if (!engagement.authenticated) {
            window.location.href = endpoints.loginUrl;
            return false;
        }
        return true;
    };

    const toggleFavorite = async () => {
        if (!ensureBuyer()) return;
        const payload = await requestJson(endpoints.favoriteTemplate.replace('__VEHICLE__', String(vehicle.id)), favorited ? 'DELETE' : 'POST', endpoints.csrfToken);
        setFavorited(Boolean(payload.favorited));
    };

    const toggleCompare = async () => {
        if (!ensureBuyer()) return;
        const payload = await requestJson(endpoints.comparisonTemplate.replace('__VEHICLE__', String(vehicle.id)), compared ? 'DELETE' : 'POST', endpoints.csrfToken);
        setCompared(Boolean(payload.compared));
    };

    const contactSeller = async (event) => {
        event.preventDefault();
        if (!ensureBuyer()) return;
        const payload = await requestJson(endpoints.contactTemplate.replace('__VEHICLE__', String(vehicle.id)), 'POST', endpoints.csrfToken, { body: message });
        setMessageState(payload.sent ? 'Mensaje enviado al vendedor y guardado en tu dashboard.' : 'No fue posible enviar el mensaje.');
        if (payload.sent) setMessage('');
    };

    return (
        <div className={`font-body md:pb-0 ${isDark ? 'theme-dark bg-[#05070b] pb-20 text-white' : 'bg-background pb-20 text-on-background'}`}>
            <TopBar homeUrl={homeUrl} catalogUrl={catalogUrl} sellUrl={sellUrl} accountUrl={accountUrl} />
            <main className="pt-20">
                <section className="mx-auto max-w-screen-2xl px-4 py-8 sm:px-6 lg:px-8">
                    <a href={catalogUrl} className="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                        <Icon name="arrow_back" className="text-[18px]" /> Volver al inventario
                    </a>
                    <div className="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                        <div>
                            <div className="overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                                <img src={activeImage} alt={vehicle.title} className="h-[420px] w-full object-cover sm:h-[520px]" />
                            </div>
                            <div className="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-5">
                                {(vehicle.media.length ? vehicle.media : [{ id: 'primary', url: vehicle.primary_image, thumb_url: vehicle.primary_image, alt: vehicle.title }]).map((item) => (
                                    <button key={item.id} type="button" onClick={() => setActiveImage(item.url)} className={`overflow-hidden rounded-2xl border ${activeImage === item.url ? 'border-primary ring-2 ring-primary/20' : 'border-outline-variant/30'}`}>
                                        <img src={item.thumb_url || item.url} alt={item.alt} className="h-20 w-full object-cover" />
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div className="space-y-6">
                            <div className="rounded-[2rem] bg-white p-6 shadow-xl sm:p-8">
                                <div className="flex flex-wrap items-center gap-3">
                                    <span className="rounded-full bg-primary-fixed px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-primary">{vehicle.publication_tier}</span>
                                    {vehicle.is_paid ? <span className="rounded-full bg-secondary/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-secondary">{vehicle.plan_name}</span> : null}
                                    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-slate-500">{vehicle.visibility_bucket}</span>
                                    {vehicle.price_badge ? <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-amber-700">{vehicle.price_badge}</span> : null}
                                </div>
                                <h1 className="mt-4 font-headline text-4xl font-extrabold tracking-tight text-slate-950">{vehicle.title}</h1>
                                <p className="mt-3 text-base text-slate-500">{vehicle.city || 'Costa Rica'} · {vehicle.published_label}</p>
                                <div className="mt-6 flex items-end justify-between gap-4">
                                    <PriceStack primary={vehicle.price} secondary={vehicle.price_secondary} large />
                                    <div className="flex gap-2">
                                        <button type="button" onClick={toggleFavorite} className={`rounded-full p-3 transition-colors ${favorited ? 'bg-primary text-white' : 'bg-surface-container-high text-slate-600 hover:bg-primary hover:text-white'}`}>
                                            <Icon name="favorite" className="text-[22px]" />
                                        </button>
                                        <button type="button" onClick={toggleCompare} className={`rounded-full p-3 transition-colors ${compared ? 'bg-primary text-white' : 'bg-surface-container-high text-slate-600 hover:bg-primary hover:text-white'}`}>
                                            <Icon name="balance" className="text-[22px]" />
                                        </button>
                                    </div>
                                </div>
                                <p className="mt-6 text-sm leading-7 text-slate-600">{vehicle.description || 'Vehiculo publicado en Movikaa, listo para contacto directo y validacion comercial.'}</p>
                            </div>

                            <div className="rounded-[2rem] bg-white p-6 shadow-xl sm:p-8">
                                <div className="mb-5 flex items-center justify-between">
                                    <h2 className="font-headline text-2xl font-extrabold tracking-tight">Ficha tecnica</h2>
                                    <Icon name="verified" className="text-[24px] text-primary" />
                                </div>
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    {specs.map(([label, value]) => (
                                        <div key={label} className="rounded-2xl border border-outline-variant/20 bg-slate-50 p-4">
                                            <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{label}</p>
                                            <strong className="mt-2 block text-base text-slate-900">{value || 'No indicado'}</strong>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-6 flex flex-wrap gap-2">
                                    {(vehicle.features || []).map((feature) => (
                                        <span key={feature} className="rounded-full bg-primary-fixed px-3 py-2 text-sm font-semibold text-primary">{feature}</span>
                                    ))}
                                </div>
                            </div>

                            <form onSubmit={contactSeller} className="rounded-[2rem] bg-white p-6 shadow-xl sm:p-8">
                                <div className="mb-5 flex items-center justify-between">
                                    <h2 className="font-headline text-2xl font-extrabold tracking-tight">Contactar vendedor</h2>
                                    <Icon name="chat" className="text-[24px] text-primary" />
                                </div>
                                <textarea value={message} onChange={(event) => setMessage(event.target.value)} rows={5} placeholder="Hola, me interesa este vehiculo. Me gustaria conocer disponibilidad, historial y opciones de financiamiento." className="w-full rounded-2xl border border-outline-variant/30 bg-slate-50 px-4 py-4 text-sm outline-none focus:border-primary"></textarea>
                                <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <button type="submit" className="inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-4 font-headline text-lg font-extrabold text-white transition-colors hover:bg-secondary-container">Enviar mensaje</button>
                                    <a href={accountUrl} className="inline-flex items-center justify-center rounded-2xl border border-primary px-5 py-4 font-headline text-lg font-extrabold text-primary transition-colors hover:bg-primary hover:text-white">Ver mi panel buyer</a>
                                </div>
                                {messageState ? <p className="mt-4 text-sm text-slate-500">{messageState}</p> : null}
                            </form>
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-screen-2xl px-4 pb-16 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-end justify-between gap-4">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Sugerencias</p>
                            <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Tambien podria interesarte</h2>
                        </div>
                        <a href={catalogUrl} className="text-sm font-bold text-primary hover:underline">Explorar mas</a>
                    </div>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                        {relatedVehicles.map((item) => (
                            <a key={item.id} href={item.url} className="group overflow-hidden rounded-2xl border border-outline-variant/20 bg-white transition-all hover:-translate-y-1 hover:shadow-2xl">
                                <img src={item.primary_image} alt={item.title} className="h-52 w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                <div className="p-5">
                                    <div className="mb-2 flex flex-wrap gap-2">
                                        {item.is_paid ? <span className="rounded-full bg-secondary/10 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-secondary">{item.plan_name}</span> : null}
                                        <span className="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">{item.visibility_bucket}</span>
                                    </div>
                                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{item.make} {item.model}</p>
                                    <h3 className="mt-2 font-headline text-xl font-extrabold tracking-tight text-slate-950">{item.title}</h3>
                                    <div className="mt-4 flex items-center justify-between gap-3">
                                        <PriceStack primary={item.price} secondary={item.price_secondary} />
                                        <span className="text-xs text-slate-400">{item.city}</span>
                                    </div>
                                </div>
                            </a>
                        ))}
                    </div>
                </section>
            </main>
        </div>
    );
}

const element = document.getElementById('vehicle-show-react');
if (element) {
    createRoot(element).render(<VehicleShowPage {...JSON.parse(element.dataset.props || '{}')} />);
}
