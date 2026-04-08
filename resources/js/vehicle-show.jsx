import React, { useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Icon, PriceStack, PublicFooter, PublicTopBar } from './public-shell';

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
        throw new Error(payload.message || 'No fue posible completar la acción.');
    }
    return payload;
}

function VehicleShowPage({ homeUrl, catalogUrl, brandsUrl, sellUrl, accountUrl, loginUrl, authUser, valuationUrl, publicTheme = 'light', vehicle, relatedVehicles, engagement, endpoints, footerLinks }) {
    const [activeImage, setActiveImage] = useState(vehicle.media[0]?.url || vehicle.primary_image);
    const [favorited, setFavorited] = useState((engagement.favoriteVehicleIds || []).includes(vehicle.id));
    const [compared, setCompared] = useState((engagement.comparisonVehicleIds || []).includes(vehicle.id));
    const [message, setMessage] = useState('');
    const [guestName, setGuestName] = useState('');
    const [guestPhone, setGuestPhone] = useState('');
    const [messageState, setMessageState] = useState('');
    const isDark = publicTheme === 'dark';
    const currentPath = `${window.location.pathname}${window.location.search}`;
    const specs = [
        ['Marca', vehicle.make],
        ['Modelo', vehicle.model],
        ['A\u00f1o', vehicle.year],
        ['Combustible', vehicle.fuel_type],
        ['Transmisi\u00f3n', vehicle.transmission],
        ['Carrocer\u00eda', vehicle.body_type],
        ['Condici\u00f3n', vehicle.condition],
        ['Kilometraje', vehicle.mileage ? `${vehicle.mileage} ${vehicle.mileage_unit}` : 'No indicado'],
    ];

    const whatsappHref = useMemo(() => {
        if (!vehicle.whatsapp_url) {
            return null;
        }

        const composed = [
            `Hola ${vehicle.vendedor_name}, me interesa ${vehicle.title}.`,
            guestName ? `Mi nombre es ${guestName}.` : '',
            guestPhone ? `Mi teléfono es ${guestPhone}.` : '',
            message || '',
        ].filter(Boolean).join(' ');

        const [baseUrl] = vehicle.whatsapp_url.split('?text=');
        return `${baseUrl}?text=${encodeURIComponent(composed)}`;
    }, [vehicle.whatsapp_url, vehicle.vendedor_name, vehicle.title, guestName, guestPhone, message]);

    const ensureBuyer = () => {
        if (!engagement.authenticated) {
            window.location.href = `${endpoints.loginUrl}?redirect=${encodeURIComponent(currentPath)}`;
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

        if (whatsappHref) {
            setMessageState('Te llevamos a WhatsApp para contactar al vendedor directamente.');
            window.open(whatsappHref, '_blank', 'noopener,noreferrer');
            return;
        }

        setMessageState('Este vehículo no tiene un WhatsApp configurado todavía.');
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
                featuredUrl={`${homeUrl}#destacados`}
            />
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
                                    <button key={item.id} type="button" onClick={() => setActiveImage(item.url)} className={`overflow-hidden rounded-2xl border ${activeImage === item.url ? 'border-secondary ring-2 ring-secondary/25' : 'border-outline-variant/30'}`}>
                                        <img src={item.thumb_url || item.url} alt={item.alt} className="h-20 w-full object-cover" />
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div className="space-y-6">
                            <div className="rounded-[2rem] bg-white p-6 shadow-xl sm:p-8">
                                <div className="flex flex-wrap items-center gap-3">
                                    <span className="rounded-full bg-primary-fixed px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-primary">{vehicle.publication_tier}</span>
                                </div>
                                <h1 className="mt-4 font-headline text-4xl font-extrabold tracking-tight text-slate-950">{vehicle.title}</h1>
                                <p className="mt-3 text-base text-slate-500">{vehicle.city || 'Costa Rica'} | {vehicle.published_label}</p>
                                <div className="mt-6 flex items-end justify-between gap-4">
                                    <PriceStack primary={vehicle.price} secondary={vehicle.price_secondary} large />
                                    <div className="flex gap-2">
                                        <button type="button" onClick={toggleFavorite} className={`rounded-full p-3 transition-colors ${favorited ? 'bg-secondary text-white' : 'bg-secondary/12 text-secondary hover:bg-secondary hover:text-white'}`}>
                                            <Icon name="favorite" className="text-[22px]" />
                                        </button>
                                        <button type="button" onClick={toggleCompare} className={`rounded-full p-3 transition-colors ${compared ? 'bg-secondary text-white' : 'bg-secondary/12 text-secondary hover:bg-secondary hover:text-white'}`}>
                                            <Icon name="compare_arrows" className="text-[22px]" />
                                        </button>
                                    </div>
                                </div>
                                <p className="mt-6 text-sm leading-7 text-slate-600">{vehicle.description || 'Vehículo publicado en Movikaa, listo para contacto directo y validación comercial.'}</p>
                            </div>

                            <div className="rounded-[2rem] bg-white p-6 shadow-xl sm:p-8">
                                <div className="mb-5 flex items-center justify-between">
                                    <h2 className="font-headline text-2xl font-extrabold tracking-tight">Ficha técnica</h2>
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
                                    <div>
                                        <h2 className="font-headline text-2xl font-extrabold tracking-tight">Contactar vendedor</h2>
                                        <p className="mt-2 text-sm text-slate-500">Escribe directo por WhatsApp y continúa la conversación con el vendedor fuera de la plataforma.</p>
                                        <p className="mt-2 text-sm text-slate-400">{vehicle.contact_phone ? `WhatsApp: ${vehicle.contact_phone}` : 'Sin WhatsApp configurado'}{vehicle.contact_email ? ` | Correo: ${vehicle.contact_email}` : ''}</p>
                                    </div>
                                    <Icon name="chat" className="text-[24px] text-primary" />
                                </div>

                                {!engagement.authenticated ? (
                                    <div className="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <input value={guestName} onChange={(event) => setGuestName(event.target.value)} type="text" placeholder="Tu nombre" className="w-full rounded-2xl border border-outline-variant/30 bg-slate-50 px-4 py-4 text-sm outline-none focus:border-primary" />
                                        <input value={guestPhone} onChange={(event) => setGuestPhone(event.target.value)} type="text" placeholder="Tu teléfono" className="w-full rounded-2xl border border-outline-variant/30 bg-slate-50 px-4 py-4 text-sm outline-none focus:border-primary" />
                                    </div>
                                ) : null}

                                <textarea value={message} onChange={(event) => setMessage(event.target.value)} rows={5} placeholder="Hola, me interesa este vehículo. Me gustaría conocer disponibilidad, historial y opciones de financiamiento." className="w-full rounded-2xl border border-outline-variant/30 bg-slate-50 px-4 py-4 text-sm outline-none focus:border-primary"></textarea>
                                <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <button type="submit" className="inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-4 font-headline text-lg font-extrabold text-white transition-colors hover:bg-secondary-container">Abrir WhatsApp</button>
                                    {whatsappHref ? (
                                        <a href={whatsappHref} target="_blank" rel="noreferrer" className="inline-flex items-center justify-center rounded-2xl border border-secondary bg-secondary/12 px-5 py-4 font-headline text-lg font-extrabold text-secondary transition-colors hover:bg-secondary hover:text-white">WhatsApp directo</a>
                                    ) : (
                                        <a href={`${loginUrl}?redirect=${encodeURIComponent(currentPath)}`} className="inline-flex items-center justify-center rounded-2xl border border-secondary bg-secondary/12 px-5 py-4 font-headline text-lg font-extrabold text-secondary transition-colors hover:bg-secondary hover:text-white">Iniciar sesión</a>
                                    )}
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
                            <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">También podría interesarte</h2>
                        </div>
                        <a href={catalogUrl} className="text-sm font-bold text-primary hover:underline">Explorar más</a>
                    </div>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                        {relatedVehicles.map((item) => (
                            <a key={item.id} href={item.url} className="group overflow-hidden rounded-2xl border border-outline-variant/20 bg-white transition-all hover:-translate-y-1 hover:shadow-2xl">
                                <img src={item.primary_image} alt={item.title} className="h-52 w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                <div className="p-5">
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

const element = document.getElementById('vehicle-show-react');
if (element) {
    createRoot(element).render(<VehicleShowPage {...JSON.parse(element.dataset.props || '{}')} />);
}



