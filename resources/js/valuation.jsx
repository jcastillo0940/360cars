import React, { useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';

function Icon({ name, className = '', filled = false }) {
    return (
        <span
            className={`material-symbols-outlined ${className}`.trim()}
            style={filled ? { fontVariationSettings: "'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24" } : undefined}
            aria-hidden="true"
        >
            {name}
        </span>
    );
}

function ResultPrice({ primary, secondary, label }) {
    return (
        <div className="rounded-2xl border border-outline-variant/20 bg-white p-5 shadow-sm">
            <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">{label}</span>
            <strong className="mt-2 block font-headline text-2xl font-extrabold text-primary">{primary}</strong>
            {secondary ? <span className="mt-1 block text-xs font-semibold text-slate-400">{secondary}</span> : null}
        </div>
    );
}

function ResultPanel({ result, shareUrl }) {
    const [copied, setCopied] = useState(false);

    const handleShare = async () => {
        if (!shareUrl || !navigator.clipboard) {
            return;
        }

        await navigator.clipboard.writeText(shareUrl);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 2200);
    };

    return (
        <section className="rounded-[28px] border border-outline-variant/25 bg-white p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)] sm:p-8">
            <div className="flex flex-col gap-4 border-b border-outline-variant/20 pb-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <span className="inline-flex rounded-full bg-primary-fixed px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">Evaluacion lista</span>
                    <h2 className="mt-4 font-headline text-3xl font-extrabold tracking-tight text-slate-900">{result.title}</h2>
                    <p className="mt-2 max-w-2xl text-sm text-slate-500">Calculamos un valor sugerido con depreciacion, comparables publicados y demanda estimada para Costa Rica.</p>
                </div>
                <div className="rounded-2xl border border-outline-variant/20 bg-slate-50 px-5 py-4">
                    <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Confianza</span>
                    <strong className="mt-2 block font-headline text-3xl font-extrabold text-secondary">{result.confidenceScore}%</strong>
                </div>
            </div>

            <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <ResultPrice label="Precio sugerido" primary={result.suggestedPrice} secondary={result.suggestedPriceSecondary} />
                <ResultPrice label="Rango minimo" primary={result.minPrice} secondary={result.minPriceSecondary} />
                <ResultPrice label="Rango maximo" primary={result.maxPrice} secondary={result.maxPriceSecondary} />
            </div>

            <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div className="rounded-2xl border border-outline-variant/20 bg-slate-50 p-6">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <span className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Lectura del mercado</span>
                            <h3 className="mt-2 font-headline text-2xl font-extrabold tracking-tight text-slate-900">Lo que esta moviendo el valor</h3>
                        </div>
                        <span className="rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">Costa Rica</span>
                    </div>
                    <div className="space-y-3">
                        {(result.insights || []).map((insight) => (
                            <div key={insight} className="flex gap-3 rounded-2xl bg-white p-4 shadow-sm">
                                <div className="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-fixed text-primary">
                                    <Icon name="trending_up" className="text-[18px]" />
                                </div>
                                <p className="text-sm leading-6 text-slate-600">{insight}</p>
                            </div>
                        ))}
                    </div>
                    {result.aiSummary ? (
                        <div className="mt-5 rounded-2xl bg-primary px-5 py-5 text-white">
                            <div className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-white/70">
                                <Icon name="auto_awesome" className="text-[18px]" />
                                Resumen IA opcional
                            </div>
                            <p className="mt-3 text-sm leading-6 text-white/90">{result.aiSummary}</p>
                        </div>
                    ) : null}
                </div>

                <div className="space-y-4">
                    <div className="rounded-2xl border border-outline-variant/20 bg-white p-5 shadow-sm">
                        <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Ficha usada</span>
                        <div className="mt-4 space-y-3 text-sm text-slate-600">
                            <div className="flex items-center justify-between gap-3"><span>Condicion</span><strong>{result.snapshot.condition === 'new' ? 'Nuevo' : 'Usado'}</strong></div>
                            <div className="flex items-center justify-between gap-3"><span>Carroceria</span><strong>{result.snapshot.body_type}</strong></div>
                            <div className="flex items-center justify-between gap-3"><span>Combustible</span><strong>{result.snapshot.fuel_type}</strong></div>
                            <div className="flex items-center justify-between gap-3"><span>Transmision</span><strong>{result.snapshot.transmission}</strong></div>
                            <div className="flex items-center justify-between gap-3"><span>Ciudad</span><strong>{result.snapshot.city}</strong></div>
                            {result.snapshot.mileage ? <div className="flex items-center justify-between gap-3"><span>Kilometraje</span><strong>{new Intl.NumberFormat('es-CR').format(result.snapshot.mileage)} km</strong></div> : null}
                        </div>
                    </div>

                    <div className="rounded-2xl border border-outline-variant/20 bg-slate-900 p-5 text-white shadow-sm">
                        <span className="text-xs font-bold uppercase tracking-[0.2em] text-white/60">Siguiente paso</span>
                        <h3 className="mt-2 font-headline text-2xl font-extrabold tracking-tight">Convierte esta evaluacion en anuncio</h3>
                        <p className="mt-3 text-sm leading-6 text-white/80">Puedes compartir la valuacion o pasar directo al flujo premium de venta con varios datos ya prellenados.</p>
                        <div className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                            <button type="button" onClick={handleShare} className="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                                <Icon name="ios_share" className="text-[18px]" />
                                {copied ? 'Enlace copiado' : 'Compartir evaluacion'}
                            </button>
                            <a href={result.sellUrl} className="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary px-4 py-3 text-sm font-bold text-white transition hover:bg-secondary-container">
                                <Icon name="directions_car" className="text-[18px]" />
                                Vender mi auto
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

function ValuationPage({ homeUrl, catalogUrl, sellUrl, accountUrl, submitUrl, csrfToken, publicTheme = 'light', makes = [], vehicleConfig, aiEnabled, aiConfigured, result, shareUrl }) {
    const [makeId, setMakeId] = useState('');
    const [menuOpen, setMenuOpen] = useState(false);
    const isDark = publicTheme === 'dark';

    const models = useMemo(() => {
        const currentMake = makes.find((entry) => String(entry.id) === String(makeId));
        return currentMake?.models || [];
    }, [makeId, makes]);

    return (
        <div className={`min-h-screen font-body ${isDark ? 'theme-dark bg-[#05070b] text-white' : 'bg-background text-on-background'}`}>
            <nav className="fixed inset-x-0 top-0 z-50 border-b border-outline-variant/30 bg-white/80 backdrop-blur-md">
                <div className="mx-auto flex h-20 max-w-screen-2xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-4 lg:gap-10">
                        <button type="button" className="inline-flex h-11 w-11 items-center justify-center rounded-full border border-outline-variant/40 text-primary md:hidden" onClick={() => setMenuOpen((current) => !current)}>
                            <Icon name={menuOpen ? 'close' : 'menu'} className="text-[24px]" />
                        </button>
                        <a href={homeUrl} className="font-headline text-3xl font-black tracking-tight text-primary">Movikaa</a>
                        <div className="hidden items-center gap-8 md:flex">
                            <a href={catalogUrl} className="font-headline text-sm font-bold tracking-tight text-slate-600 transition hover:text-primary">Comprar</a>
                            <a href={homeUrl + '#destacados'} className="font-headline text-sm font-bold tracking-tight text-slate-600 transition hover:text-primary">Destacados</a>
                            <a href={homeUrl + '/tasador'} className="font-headline text-sm font-bold tracking-tight text-slate-600 transition hover:text-primary">Valuacion</a>
                            <a href={homeUrl + '#noticias'} className="font-headline text-sm font-bold tracking-tight text-slate-600 transition hover:text-primary">Noticias</a>
                        </div>
                    </div>
                    <div className="hidden items-center gap-4 md:flex">
                        <a href={accountUrl} className="px-5 py-2 text-sm font-bold text-slate-600 transition hover:text-primary">Ingresar</a>
                        <a href={sellUrl} className="rounded bg-secondary px-6 py-2.5 font-headline text-sm font-bold text-white shadow-md transition hover:bg-secondary-container">Vender mi auto</a>
                    </div>
                </div>
                {menuOpen ? (
                    <div className="border-t border-outline-variant/20 bg-white px-4 py-4 md:hidden">
                        <div className="flex flex-col gap-3">
                            <a href={catalogUrl} className="font-headline font-bold text-slate-700">Comprar</a>
                            <a href={homeUrl + '#destacados'} className="font-headline font-bold text-slate-700">Destacados</a>
                            <a href={homeUrl + '/tasador'} className="font-headline font-bold text-slate-700">Valuacion</a>
                            <a href={homeUrl + '#noticias'} className="font-headline font-bold text-slate-700">Noticias</a>
                            <a href={accountUrl} className="font-headline font-bold text-slate-700">Ingresar</a>
                            <a href={sellUrl} className="font-headline font-bold text-slate-700">Vender mi auto</a>
                        </div>
                    </div>
                ) : null}
            </nav>

            <main className="pt-20">
                <section className="relative overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(0,91,183,0.16),_transparent_38%),linear-gradient(180deg,#f9f9fc_0%,#eef3fb_100%)] py-16 sm:py-20">
                    <div className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                        <div className="grid gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-start">
                            <div className="pt-4">
                                <span className="inline-flex rounded-full bg-primary-fixed px-4 py-2 text-[11px] font-bold uppercase tracking-[0.24em] text-primary">Tasador inteligente</span>
                                <h1 className="mt-6 max-w-2xl font-headline text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl">Descubre cuanto podrias pedir por tu auto.</h1>
                                <p className="mt-5 max-w-xl text-base leading-8 text-slate-600 sm:text-lg">Completa la ficha, recibe un rango de precio en colones y si te convence, pasa directo a venderlo con los datos ya adelantados.</p>
                                <div className="mt-8 grid gap-4 sm:grid-cols-2">
                                    <div className="rounded-2xl border border-outline-variant/20 bg-white/80 p-5 shadow-sm">
                                        <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Lo que recibes</span>
                                        <p className="mt-2 text-sm leading-6 text-slate-600">Un precio sugerido, un rango minimo y maximo, y una lectura simple del mercado de Costa Rica.</p>
                                    </div>
                                    <div className="rounded-2xl border border-outline-variant/20 bg-white/80 p-5 shadow-sm">
                                        <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Siguiente paso</span>
                                        <p className="mt-2 text-sm leading-6 text-slate-600">{aiEnabled && aiConfigured ? 'Ademas de la evaluacion, el sistema puede explicar mejor el resultado cuando la capa IA esta activa.' : 'Si el valor te gusta, desde el resultado puedes ir directo a vender tu auto sin volver a empezar.'}</p>
                                    </div>
                                </div>
                            </div>

                            {result ? (
                                <ResultPanel result={result} shareUrl={shareUrl} />
                            ) : (
                                <section className="rounded-[28px] border border-outline-variant/25 bg-white p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)] sm:p-8">
                                    <div className="mb-6 flex flex-col gap-3 border-b border-outline-variant/20 pb-6 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <span className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Valuacion guiada</span>
                                            <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight text-slate-900">Completa la ficha del auto</h2>
                                        </div>
                                        <div className="inline-flex rounded-full bg-slate-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-slate-500">3 min aprox.</div>
                                    </div>

                                    <form action={submitUrl} method="POST" className="grid gap-6">
                                        <input type="hidden" name="_token" value={csrfToken} />

                                        <div className="grid gap-4 md:grid-cols-2">
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Marca</span>
                                                <select name="vehicle_make_id" required onChange={(event) => setMakeId(event.target.value)} className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    <option value="">Selecciona la marca</option>
                                                    {makes.map((make) => <option key={make.id} value={make.id}>{make.name}</option>)}
                                                </select>
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Modelo</span>
                                                <select name="vehicle_model_id" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    <option value="">Selecciona el modelo</option>
                                                    {models.map((model) => <option key={model.id} value={model.id}>{model.name}</option>)}
                                                </select>
                                            </label>
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Anio</span>
                                                <select name="year" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    {vehicleConfig.years.map((year) => <option key={year} value={year}>{year}</option>)}
                                                </select>
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Condicion</span>
                                                <select name="condition" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    {Object.entries(vehicleConfig.conditions).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                                                </select>
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Carroceria</span>
                                                <select name="body_type" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    <option value="">Selecciona</option>
                                                    {vehicleConfig.bodyTypes.map((item) => <option key={item} value={item}>{item}</option>)}
                                                </select>
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Combustible</span>
                                                <select name="fuel_type" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    <option value="">Selecciona</option>
                                                    {vehicleConfig.fuelTypes.map((item) => <option key={item} value={item}>{item}</option>)}
                                                </select>
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Transmision</span>
                                                <select name="transmission" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    <option value="">Selecciona</option>
                                                    {vehicleConfig.transmissions.map((item) => <option key={item} value={item}>{item}</option>)}
                                                </select>
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Traccion</span>
                                                <select name="drivetrain" className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    <option value="">No especificar</option>
                                                    {vehicleConfig.drivetrains.map((item) => <option key={item} value={item}>{item}</option>)}
                                                </select>
                                            </label>
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Kilometraje</span>
                                                <input type="number" name="mileage" min="0" step="1" placeholder="Ej. 42000" className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary" />
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Tamano motor</span>
                                                <input type="number" name="engine_size" min="0" max="10" step="0.1" placeholder="Ej. 2.5" className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary" />
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Ciudad</span>
                                                <select name="city" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">
                                                    {vehicleConfig.cities.map((city) => <option key={city} value={city}>{city}</option>)}
                                                </select>
                                            </label>
                                        </div>

                                        <label className="flex flex-col gap-2 rounded-2xl border border-outline-variant/25 bg-slate-50 p-5">
                                            <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Precio de referencia opcional</span>
                                            <input type="number" name="price_reference" min="0" step="1" placeholder="Si quieres comparar contra tu expectativa en colones" className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary" />
                                            <small className="text-sm text-slate-500">El sistema devolvera el valor en CRC como precio principal y una referencia pequena en USD.</small>
                                        </label>

                                        <div className="flex flex-col gap-4 rounded-2xl bg-slate-900 px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-white/60">Salida</span>
                                                <p className="mt-2 text-sm text-white/80">Recibiras un rango de mercado, una lectura de depreciacion y un enlace para compartir la evaluacion.</p>
                                            </div>
                                            <button type="submit" className="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container">
                                                <Icon name="query_stats" className="text-[18px]" />
                                                Calcular valuacion
                                            </button>
                                        </div>
                                    </form>
                                </section>
                            )}
                        </div>
                    </div>
                </section>
            </main>
        </div>
    );
}

const element = document.getElementById('valuation-react');
if (element) {
    const props = JSON.parse(element.dataset.props || '{}');
    createRoot(element).render(<ValuationPage {...props} />);
}

