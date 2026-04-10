import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { buildComparisonsUrl, clearComparisonIds, getComparisonIds, MAX_COMPARISON_ITEMS, saveComparisonIds } from './comparison-store';
import { Icon, PriceStack, PublicFooter, PublicTopBar } from './public-shell';

function ComparisonCard({ vehicle, onRemove, isDark }) {
    return (
        <article className={`overflow-hidden rounded-[2rem] border border-outline-variant/20 shadow-xl ${isDark ? 'bg-white/5' : 'bg-white'}`}>
            <img src={vehicle.primary_image} alt={vehicle.title} className="h-56 w-full object-cover" />
            <div className="p-6">
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{vehicle.make} {vehicle.model}</p>
                        <a href={vehicle.url} className={`mt-2 block font-headline text-2xl font-extrabold tracking-tight ${isDark ? 'text-white' : 'text-slate-950'}`}>{vehicle.title}</a>
                    </div>
                    <button type="button" onClick={() => onRemove(vehicle.id)} className={`rounded-full p-3 transition ${isDark ? 'bg-white/10 text-white/70 hover:bg-white/20' : 'bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-600'}`} aria-label="Quitar del comparador">
                        <Icon name="close" className="text-[18px]" />
                    </button>
                </div>

                <div className="mt-5 flex flex-wrap gap-2">
                    {vehicle.performance_badge ? <span className="rounded-full bg-primary/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-primary">{vehicle.performance_badge}</span> : null}
                    <span className={`rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${isDark ? 'bg-white/10 text-white/60' : 'bg-slate-100 text-slate-600'}`}>{vehicle.view_count} vistas</span>
                    <span className={`rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${isDark ? 'bg-white/10 text-white/60' : 'bg-slate-100 text-slate-600'}`}>{vehicle.lead_count} contactos</span>
                </div>

                <div className="mt-5">
                    <PriceStack primary={vehicle.price} secondary={vehicle.price_secondary} />
                </div>

                <div className="mt-6 grid grid-cols-2 gap-3 text-sm">
                    <div className={`rounded-2xl border border-outline-variant/20 p-4 ${isDark ? 'bg-white/5' : 'bg-slate-50'}`}>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Año</p>
                        <strong className={`mt-2 block ${isDark ? 'text-white' : 'text-slate-900'}`}>{vehicle.year || 'N/D'}</strong>
                    </div>
                    <div className={`rounded-2xl border border-outline-variant/20 p-4 ${isDark ? 'bg-white/5' : 'bg-slate-50'}`}>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Kilometraje</p>
                        <strong className={`mt-2 block ${isDark ? 'text-white' : 'text-slate-900'}`}>{vehicle.mileage ? `${vehicle.mileage} ${vehicle.mileage_unit}` : 'N/D'}</strong>
                    </div>
                    <div className={`rounded-2xl border border-outline-variant/20 p-4 ${isDark ? 'bg-white/5' : 'bg-slate-50'}`}>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Combustible</p>
                        <strong className={`mt-2 block ${isDark ? 'text-white' : 'text-slate-900'}`}>{vehicle.fuel_type || 'N/D'}</strong>
                    </div>
                    <div className={`rounded-2xl border border-outline-variant/20 p-4 ${isDark ? 'bg-white/5' : 'bg-slate-50'}`}>
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Transmisión</p>
                        <strong className={`mt-2 block ${isDark ? 'text-white' : 'text-slate-900'}`}>{vehicle.transmission || 'N/D'}</strong>
                    </div>
                </div>

                <div className="mt-6 flex flex-wrap gap-3">
                    <a href={vehicle.url} className={`inline-flex items-center justify-center rounded-2xl border px-4 py-3 font-bold transition ${isDark ? 'border-white/20 bg-white/10 text-white hover:bg-white/20' : 'border-slate-300 bg-slate-50 text-slate-800 hover:border-primary hover:bg-primary/10 hover:text-primary'}`}>Ver ficha</a>
                    {vehicle.contact_url ? (
                        <a href={vehicle.contact_url} className="inline-flex items-center justify-center rounded-2xl bg-secondary px-4 py-3 font-bold text-white transition hover:bg-secondary-container">
                            Contactar
                        </a>
                    ) : null}
                </div>
            </div>
        </article>
    );
}

function ComparisonPage({ homeUrl, catalogUrl, brandsUrl, sellUrl, accountUrl, loginUrl, authUser, valuationUrl, comparisonsUrl, publicTheme = 'light', comparisonIds = [], comparisonVehicles = [], comparisonRecommendation = null, suggestedVehicles = [], footerLinks }) {
    const [ids, setIds] = useState(() => comparisonIds.length ? comparisonIds : getComparisonIds());
    const [message, setMessage] = useState('');
    const isDark = publicTheme === 'dark';

    useEffect(() => {
        const storedIds = getComparisonIds();
        const currentIds = comparisonIds.length ? comparisonIds : ids;
        const sameIds = JSON.stringify(storedIds) === JSON.stringify(currentIds);

        if (!sameIds) {
            if (!storedIds.length && currentIds.length) {
                saveComparisonIds(currentIds);
                return;
            }

            window.location.replace(buildComparisonsUrl(comparisonsUrl, storedIds));
        }
    }, [comparisonIds, comparisonsUrl, ids]);

    const removeVehicle = (vehicleId) => {
        const nextIds = saveComparisonIds(ids.filter((item) => item !== vehicleId));
        setIds(nextIds);
        setMessage('Auto removido del comparador.');
        window.location.href = buildComparisonsUrl(comparisonsUrl, nextIds);
    };

    const clearAll = () => {
        clearComparisonIds();
        setIds([]);
        window.location.href = comparisonsUrl;
    };

    const priceSummary = useMemo(() => {
        if (!comparisonVehicles.length) {
            return 'Sin autos comparados';
        }

        const values = comparisonVehicles.map((vehicle) => Number(vehicle.price_value || 0)).filter((value) => value > 0);
        if (!values.length) {
            return 'Precio no disponible';
        }

        return `${new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC', maximumFractionDigits: 0 }).format(Math.min(...values))} - ${new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC', maximumFractionDigits: 0 }).format(Math.max(...values))}`;
    }, [comparisonVehicles]);

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
                    <div className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                        <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Comparador público</p>
                        <div className="mt-4 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h1 className={`font-headline text-4xl font-extrabold tracking-tight sm:text-5xl ${isDark ? 'text-white' : 'text-slate-950'}`}>Compara autos sin iniciar sesión.</h1>
                                <p className={`mt-4 max-w-3xl text-base leading-8 sm:text-lg ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>Guarda hasta {MAX_COMPARISON_ITEMS} autos en tu navegador, compáralos lado a lado y decide más rápido con señales reales de vistas, contactos y precio.</p>
                            </div>
                            <div className="flex flex-wrap gap-3">
                                <a href={catalogUrl} className={`inline-flex items-center justify-center rounded-2xl border px-5 py-4 font-bold transition ${isDark ? 'border-white/10 bg-white/5 text-white hover:bg-white/10' : 'border-slate-300 bg-slate-50 text-slate-800 hover:border-primary hover:bg-primary/10 hover:text-primary'}`}>Agregar otro auto</a>
                                {ids.length ? (
                                    <button type="button" onClick={clearAll} className="inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-4 font-bold text-white transition hover:bg-secondary-container">
                                        Limpiar comparador
                                    </button>
                                ) : null}
                            </div>
                        </div>

                        <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div className={`rounded-2xl border p-5 shadow-sm transition-all ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-white'}`}>
                                <p className={`text-xs font-bold uppercase tracking-[0.2em] ${isDark ? 'text-slate-400' : 'text-slate-500'}`}>Autos comparados</p>
                                <strong className="mt-3 block font-headline text-4xl font-extrabold text-primary">{comparisonVehicles.length}</strong>
                            </div>
                            <div className={`rounded-2xl border p-5 shadow-sm transition-all ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-white'}`}>
                                <p className={`text-xs font-bold uppercase tracking-[0.2em] ${isDark ? 'text-slate-400' : 'text-slate-500'}`}>Rango de precio</p>
                                <strong className={`mt-3 block font-headline text-2xl font-extrabold ${isDark ? 'text-white' : 'text-slate-950'}`}>{priceSummary}</strong>
                            </div>
                            <div className={`rounded-2xl border p-5 shadow-sm transition-all ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-white'}`}>
                                <p className={`text-xs font-bold uppercase tracking-[0.2em] ${isDark ? 'text-slate-400' : 'text-slate-500'}`}>Estado</p>
                                <strong className={`mt-3 block font-headline text-2xl font-extrabold ${isDark ? 'text-white' : 'text-slate-950'}`}>{comparisonVehicles.length >= 2 ? 'Listo para comparar' : 'Agrega otro auto'}</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-screen-2xl px-4 py-16 sm:px-6 lg:px-8">
                    {comparisonVehicles.length ? (
                        <>
                            {message ? <p className="mb-6 text-sm text-slate-500">{message}</p> : null}
                            <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                                {comparisonVehicles.map((vehicle) => <ComparisonCard key={vehicle.id} vehicle={vehicle} onRemove={removeVehicle} isDark={isDark} />)}
                            </div>

                            {comparisonRecommendation ? (
                                <div className="mt-10 grid grid-cols-1 gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                                    <article className={`rounded-[2rem] border border-outline-variant/20 p-6 shadow-xl sm:p-8 ${isDark ? 'bg-white/5' : 'bg-white'}`}>
                                        <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Lectura rápida</p>
                                        <h2 className={`mt-3 font-headline text-3xl font-extrabold tracking-tight ${isDark ? 'text-white' : 'text-slate-950'}`}>La opción más equilibrada</h2>
                                        <div className="mt-6 rounded-3xl bg-slate-950 p-6 text-white">
                                            <span className="rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-white/70">Top comparador</span>
                                            <h3 className="mt-4 font-headline text-3xl font-extrabold">{comparisonRecommendation.winner.title}</h3>
                                            <p className="mt-2 text-sm font-semibold text-white/70">{comparisonRecommendation.winner.headline}</p>
                                            <div className="mt-5 inline-flex rounded-full bg-secondary px-4 py-2 text-sm font-black text-slate-950">{comparisonRecommendation.winner.score}/100</div>
                                            <ul className="mt-6 space-y-3 text-sm leading-7 text-white/82">
                                                {comparisonRecommendation.winner.reasons.map((reason) => <li key={reason}>• {reason}.</li>)}
                                            </ul>
                                            <a href={comparisonRecommendation.winner.url} className="mt-6 inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 font-bold text-slate-950 transition hover:bg-slate-100">Ver ficha del ganador</a>
                                        </div>
                                    </article>

                                    <article className={`rounded-[2rem] border border-outline-variant/20 p-6 shadow-xl sm:p-8 ${isDark ? 'bg-white/5' : 'bg-white'}`}>
                                        <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Ranking</p>
                                        <h2 className={`mt-3 font-headline text-3xl font-extrabold tracking-tight ${isDark ? 'text-white' : 'text-slate-950'}`}>Cómo quedaron ordenados</h2>
                                        <div className="mt-6 space-y-4">
                                            {comparisonRecommendation.ranking.map((item, index) => (
                                                <div key={item.vehicle_id} className={`rounded-2xl border border-outline-variant/20 p-5 ${isDark ? 'bg-white/5' : 'bg-slate-50'}`}>
                                                    <div className="flex flex-wrap items-center justify-between gap-3">
                                                        <div>
                                                            <p className="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">#{index + 1}</p>
                                                            <a href={item.url} className={`mt-2 block font-headline text-2xl font-extrabold ${isDark ? 'text-white' : 'text-slate-950'}`}>{item.title}</a>
                                                            <p className="mt-2 text-sm text-slate-500">{item.headline}</p>
                                                        </div>
                                                        <span className="rounded-full bg-primary/15 px-4 py-2 text-sm font-black text-primary">{item.score}/100</span>
                                                    </div>
                                                    <p className={`mt-4 text-sm leading-7 ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>{item.reasons.join('. ')}.</p>
                                                </div>
                                            ))}
                                        </div>
                                    </article>
                                </div>
                            ) : null}
                        </>
                    ) : (
                        <div className={`rounded-[2rem] border border-outline-variant/20 p-10 text-center shadow-xl ${isDark ? 'bg-white/5' : 'bg-white'}`}>
                            <Icon name="compare_arrows" className="text-[44px] text-primary" />
                            <h2 className={`mt-4 font-headline text-3xl font-extrabold tracking-tight ${isDark ? 'text-white' : 'text-slate-950'}`}>Tu comparador está vacío.</h2>
                            <p className={`mt-3 text-base leading-8 ${isDark ? 'text-slate-400' : 'text-slate-600'}`}>Primero elige un auto en el inventario, luego agrega otro para compararlos aquí con el mismo look del frontend.</p>
                            <a href={catalogUrl} className="mt-6 inline-flex items-center justify-center rounded-2xl bg-secondary px-5 py-4 font-headline text-lg font-extrabold text-white transition hover:bg-secondary-container">Ir al inventario</a>
                        </div>
                    )}
                </section>

                <section className="mx-auto max-w-screen-2xl px-4 pb-16 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-end justify-between gap-4">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Sugerencias</p>
                            <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">Autos para completar la comparación</h2>
                        </div>
                        <a href={catalogUrl} className="text-sm font-bold text-primary hover:underline">Explorar inventario</a>
                    </div>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                        {suggestedVehicles.map((vehicle) => (
                            <article key={vehicle.id} className={`overflow-hidden rounded-2xl border border-outline-variant/20 shadow-lg ${isDark ? 'bg-white/5' : 'bg-white'}`}>
                                <img src={vehicle.primary_image} alt={vehicle.title} className="h-52 w-full object-cover" />
                                <div className="p-5">
                                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{vehicle.make} {vehicle.model}</p>
                                    <a href={vehicle.url} className={`mt-2 block font-headline text-2xl font-extrabold tracking-tight ${isDark ? 'text-white' : 'text-slate-950'}`}>{vehicle.title}</a>
                                    <div className="mt-4 flex items-center justify-between gap-3">
                                        <PriceStack primary={vehicle.price} secondary={vehicle.price_secondary} />
                                        <span className="text-xs text-slate-400">{vehicle.province || vehicle.city || 'Costa Rica'}</span>
                                    </div>
                                </div>
                            </article>
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

const element = document.getElementById('comparisons-react');
if (element) {
    createRoot(element).render(<ComparisonPage {...JSON.parse(element.dataset.props || '{}')} />);
}

