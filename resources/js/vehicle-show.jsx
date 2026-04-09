import React, { useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { buildComparisonsUrl, getComparisonIds, toggleComparisonId } from './comparison-store';
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

function VehicleShowPage({ homeUrl, catalogUrl, brandsUrl, sellUrl, accountUrl, loginUrl, authUser, valuationUrl, publicTheme = 'light', vehicle, relatedVehicles, engagement, endpoints, footerLinks, isAvailable = true }) {
    const [activeImage, setActiveImage] = useState(vehicle.media[0]?.url || vehicle.primary_image);
    const [favorited, setFavorited] = useState((engagement.favoriteVehicleIds || []).includes(vehicle.id));
    const [compared, setCompared] = useState(() => getComparisonIds().includes(vehicle.id));
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
        if (!vehicle.contact_url) {
            return null;
        }

        const composed = [
            `Hola ${vehicle.seller_name || 'vendedor'}, me interesa ${vehicle.title}.`,
            guestName ? `Mi nombre es ${guestName}.` : '',
            guestPhone ? `Mi teléfono es ${guestPhone}.` : '',
            message || '',
        ].filter(Boolean).join(' ');

        return `${vehicle.contact_url}?text=${encodeURIComponent(composed)}`;
    }, [vehicle.contact_url, vehicle.seller_name, vehicle.title, guestName, guestPhone, message]);

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
        const next = toggleComparisonId(vehicle.id);

        if (next.reason) {
            setMessageState(next.reason);
            return;
        }

        setCompared(next.compared);
        setMessageState(next.compared
            ? 'Auto agregado al comparador público.'
            : 'Auto removido del comparador.');
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
                featuredUrl={`${catalogUrl}?featured=1`}
            />
            <main className="pt-20">
                {!isAvailable ? (
                    <>
                        <section className="mx-auto max-w-screen-2xl px-4 py-10 sm:px-6 lg:px-8">
                            <a href={catalogUrl} className="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                                <Icon name="arrow_back" className="text-[18px]" /> Volver al inventario
                            </a>
                            <div className="mt-6 rounded-[2rem] bg-white p-8 shadow-2xl sm:p-10">
                                <span className="rounded-full bg-secondary/12 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-secondary">Auto no disponible</span>
                                <h1 className="mt-4 font-headline text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl">Este auto ya no está disponible.</h1>
                                <p className="mt-4 max-w-3xl text-base leading-8 text-slate-600 sm:text-lg">
                                    El anuncio de {vehicle.title} fue pausado, venció o dejó de estar publicado. Pero no te dejamos en una página vacía: aquí mismo puedes seguir explorando otras opciones parecidas.
                                </p>
                                <div className="mt-8 flex flex-wrap gap-3">
                                    <a href={catalogUrl} className="inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-4 font-headline text-lg font-extrabold text-white transition-colors hover:bg-secondary-container">
                                        Ver inventario disponible
                                    </a>
                                    {vehicle.make ? (
                                        <a href={`${catalogUrl}?make=${encodeURIComponent(vehicle.make)}`} className="inline-flex items-center justify-center rounded-2xl border border-secondary bg-secondary/12 px-5 py-4 font-headline text-lg font-extrabold text-secondary transition-colors hover:bg-secondary hover:text-white">
                                            Ver más {vehicle.make}
                                        </a>
                                    ) : null}
                                </div>
                            </div>
                        </section>

                        <section className="mx-auto max-w-screen-2xl px-4 pb-16 sm:px-6 lg:px-8">
                            <div className="mb-8 flex items-end justify-between gap-4">
                                <div>
                                    <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Otras opciones</p>
                                    <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Autos similares disponibles</h2>
                                </div>
                                <a href={catalogUrl} className="text-sm font-bold text-primary hover:underline">Explorar todo</a>
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
                    </>
                ) : (
                <>
                <section className="mx-auto max-w-screen-2xl px-4 py-8 sm:px-6 lg:px-8">
                    <a href={catalogUrl} className="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                        <Icon name="arrow_back" className="text-[18px]" /> Volver al inventario
                    </a>
                        <div className="grid grid-cols-1 items-start gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                            <div className="lg:sticky lg:top-24">
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
                                        {vehicle.performance_badge ? <span className="rounded-full bg-secondary/12 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-secondary">{vehicle.performance_badge}</span> : null}
                                    </div>
                                    <h1 className="mt-4 font-headline text-4xl font-extrabold tracking-tight text-slate-950">{vehicle.title}</h1>
                                    <p className="mt-3 text-base text-slate-500">{vehicle.city || 'Costa Rica'} | {vehicle.published_label}</p>
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        <span className="rounded-full bg-slate-900/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-slate-700">{vehicle.view_count} vistas</span>
                                        {vehicle.is_owner ? <span className="rounded-full bg-secondary/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-secondary-container">{vehicle.lead_count} contactos</span> : null}
                                    </div>
                                    <div className="mt-6 flex items-end justify-between gap-4">
                                        <PriceStack primary={vehicle.price} secondary={vehicle.price_secondary} large />
                                        <div className="flex gap-2">
                                            <button 
                                                type="button" 
                                                onClick={toggleFavorite} 
                                                className={`group rounded-full p-3 transition-all ${favorited ? 'bg-secondary text-white shadow-lg' : 'bg-secondary/12 text-secondary hover:bg-secondary'}`}
                                                title={favorited ? 'Quitar de mis favoritos' : 'Guardar este auto en mis favoritos'}
                                            >
                                                <Icon name="favorite" className={`text-[22px] transition-colors ${favorited ? 'text-white' : 'group-hover:text-white'}`} />
                                            </button>
                                            <button 
                                                type="button" 
                                                onClick={toggleCompare} 
                                                className={`group rounded-full p-3 transition-all ${compared ? 'bg-secondary text-white shadow-lg' : 'bg-secondary/12 text-secondary hover:bg-secondary'}`}
                                                title={compared ? 'Quitar del comparador' : 'Añadir a lista comparativa'}
                                            >
                                                <Icon name="compare_arrows" className={`text-[22px] transition-colors ${compared ? 'text-white' : 'group-hover:text-white'}`} />
                                            </button>
                                        </div>
                                    </div>
                                    <a href={buildComparisonsUrl(endpoints.comparisonsUrl, getComparisonIds())} className="mt-4 inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">
                                        <Icon name="compare_arrows" className="text-[18px]" />
                                        Abrir comparador
                                    </a>
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

                                <div className="rounded-[2rem] bg-white p-6 shadow-xl sm:p-8">
                                    <div className="flex items-center gap-4">
                                        <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-secondary/10 text-secondary">
                                            <Icon name="chat" className="text-[28px]" />
                                        </div>
                                        <div>
                                            <h2 className="font-headline text-2xl font-extrabold tracking-tight">Escríbenos directamente</h2>
                                            <p className="text-sm text-slate-500">¿Tienes dudas sobre disponibilidad o financiamiento?</p>
                                        </div>
                                    </div>

                                    <div className="mt-8 space-y-4">
                                        <div className="rounded-2xl border border-outline-variant/30 bg-slate-50 p-5">
                                            <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Atención Directa</p>
                                            <strong className="mt-1 block text-lg text-slate-900">{vehicle.contact_phone || '+506 WhatsApp'}</strong>
                                            <p className="mt-1 text-sm text-slate-500">Haz clic en el botón de abajo para iniciar la conversación instantánea.</p>
                                        </div>

                                        {whatsappHref ? (
                                            <a 
                                                href={whatsappHref} 
                                                target="_blank" 
                                                rel="noreferrer" 
                                                className="inline-flex w-full items-center justify-center gap-3 rounded-2xl bg-secondary px-5 py-5 font-headline text-xl font-extrabold text-white shadow-lg transition-all hover:bg-secondary-container hover:shadow-xl active:scale-[0.98]"
                                            >
                                                <Icon name="forum" className="text-[24px]" />
                                                Contactar por WhatsApp
                                            </a>
                                        ) : (
                                            <a href={`${loginUrl}?redirect=${encodeURIComponent(currentPath)}`} className="inline-flex w-full items-center justify-center rounded-2xl border border-secondary bg-secondary/12 px-5 py-4 font-headline text-lg font-extrabold text-secondary transition-colors hover:bg-secondary hover:text-white">Iniciar sesión para ver datos</a>
                                        )}
                                    </div>
                                    {messageState ? <p className="mt-4 text-center text-sm text-slate-500">{messageState}</p> : null}
                                </div>
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
                </>
                )}
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
