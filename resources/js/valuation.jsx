import React, { useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { Icon, PublicFooter, PublicTopBar } from './public-shell';

function ResultPrice({ primary, secondary, label, isDark }) {
    return (
        <div className={`rounded-2xl border p-5 shadow-sm ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-white'}`}>
            <span className={`text-xs font-bold uppercase tracking-[0.2em] ${isDark ? 'text-slate-400' : 'text-slate-500'}`}>{label}</span>
            <strong className="mt-2 block font-headline text-2xl font-extrabold text-primary">{primary}</strong>
            {secondary ? <span className="mt-1 block text-xs font-semibold text-slate-400">{secondary}</span> : null}
        </div>
    );
}

function ResultPanel({ result, shareUrl, isDark }) {
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
        <section className={`rounded-[28px] border p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)] sm:p-8 ${isDark ? 'border-white/10 bg-[#0b1118]' : 'border-outline-variant/25 bg-white'}`}>
            <div className="flex flex-col gap-4 border-b border-outline-variant/20 pb-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <span className="inline-flex rounded-full bg-primary-fixed px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-primary">EvaluaciÃ³n lista</span>
                    <h2 className={`mt-4 font-headline text-3xl font-extrabold tracking-tight ${isDark ? 'text-white' : 'text-slate-900'}`}>{result.title}</h2>
                    <p className={`mt-2 max-w-2xl text-sm ${isDark ? 'text-slate-300' : 'text-slate-500'}`}>Calculamos un valor sugerido con depreciaciÃ³n, comparables publicados y demanda estimada para Costa Rica.</p>
                </div>
                <div className={`rounded-2xl border px-5 py-4 ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-slate-50'}`}>
                    <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Confianza</span>
                    <strong className="mt-2 block font-headline text-3xl font-extrabold text-secondary">{result.confidenceScore}%</strong>
                </div>
            </div>

            <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <ResultPrice label="Precio sugerido" primary={result.suggestedPrice} secondary={result.suggestedPriceSecondary} isDark={isDark} />
                <ResultPrice label="Rango mÃ­nimo" primary={result.minPrice} secondary={result.minPriceSecondary} isDark={isDark} />
                <ResultPrice label="Rango mÃ¡ximo" primary={result.maxPrice} secondary={result.maxPriceSecondary} isDark={isDark} />
            </div>

            <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div className={`rounded-2xl border p-6 ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-slate-50'}`}>
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <span className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Lectura del mercado</span>
                            <h3 className={`mt-2 font-headline text-2xl font-extrabold tracking-tight ${isDark ? 'text-white' : 'text-slate-900'}`}>Lo que estÃ¡ moviendo el valor</h3>
                        </div>
                        <span className={`rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] ${isDark ? 'bg-[#0f1722] text-slate-300' : 'bg-white text-slate-500'}`}>Costa Rica</span>
                    </div>
                    <div className="space-y-3">
                        {(result.insights || []).map((insight) => (
                            <div key={insight} className={`flex gap-3 rounded-2xl p-4 shadow-sm ${isDark ? 'bg-[#0f1722]' : 'bg-white'}`}>
                                <div className="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-fixed text-primary">
                                    <Icon name="trending_up" className="text-[18px]" />
                                </div>
                                <p className={`text-sm leading-6 ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>{insight}</p>
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
                    <div className={`rounded-2xl border p-5 shadow-sm ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-white'}`}>
                        <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Ficha usada</span>
                        <div className={`mt-4 space-y-3 text-sm ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>
                            <div className="flex items-center justify-between gap-3"><span>CondiciÃ³n</span><strong>{result.snapshot.condition === 'new' ? 'Nuevo' : 'Usado'}</strong></div>
                            <div className="flex items-center justify-between gap-3"><span>CarrocerÃ­a</span><strong>{result.snapshot.body_type}</strong></div>
                            <div className="flex items-center justify-between gap-3"><span>Combustible</span><strong>{result.snapshot.fuel_type}</strong></div>
                            <div className="flex items-center justify-between gap-3"><span>Transmisión</span><strong>{result.snapshot.transmission}</strong></div>
                            <div className="flex items-center justify-between gap-3"><span>Ciudad</span><strong>{result.snapshot.city}</strong></div>
                            {result.snapshot.mileage ? <div className="flex items-center justify-between gap-3"><span>Kilometraje</span><strong>{new Intl.NumberFormat('es-CR').format(result.snapshot.mileage)} km</strong></div> : null}
                        </div>
                    </div>

                    <div className={`rounded-2xl border p-5 text-white shadow-sm ${isDark ? 'border-secondary/30 bg-secondary/90' : 'border-outline-variant/20 bg-slate-900'}`}>
                        <span className={`text-xs font-bold uppercase tracking-[0.2em] ${isDark ? 'text-white/80' : 'text-white/60'}`}>Siguiente paso</span>
                        <h3 className="mt-2 font-headline text-2xl font-extrabold tracking-tight">Convierte esta evaluaciÃ³n en anuncio</h3>
                        <p className={`mt-3 text-sm leading-6 ${isDark ? 'text-white' : 'text-white/80'}`}>Puedes compartir la valuaciÃ³n o pasar directo al flujo de venta con varios datos ya prellenados.</p>
                        <div className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                            <button type="button" onClick={handleShare} className="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                                <Icon name="ios_share" className="text-[18px]" />
                                {copied ? 'Enlace copiado' : 'Compartir evaluaciÃ³n'}
                            </button>
                            <a href={result.sellUrl} className={`inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white transition ${isDark ? 'bg-slate-950 hover:bg-black' : 'bg-secondary hover:bg-secondary-container'}`}>
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

function ValuationPage({ homeUrl, catalogUrl, sellUrl, accountUrl, loginUrl, authUser, submitUrl, csrfToken, valuationUrl, publicTheme = 'light', makes = [], vehicleConfig, result, shareUrl, footerLinks }) {
    const [makeId, setMakeId] = useState('');
    const isDark = publicTheme === 'dark';

    const models = useMemo(() => {
        const currentMake = makes.find((entry) => String(entry.id) === String(makeId));
        return currentMake?.models || [];
    }, [makeId, makes]);

    return (
        <div className={`min-h-screen font-body ${isDark ? 'theme-dark bg-[#05070b] text-white' : 'bg-background text-on-background'}`}>
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
                <section className={`relative overflow-hidden py-16 sm:py-20 ${isDark ? 'bg-[radial-gradient(circle_at_top_left,_rgba(0,91,183,0.18),_transparent_34%),linear-gradient(180deg,#060a10_0%,#0b1118_100%)]' : 'bg-[radial-gradient(circle_at_top_left,_rgba(0,91,183,0.16),_transparent_38%),linear-gradient(180deg,#f9f9fc_0%,#eef3fb_100%)]'}`}>
                    <div className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                        <div className="grid gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-start">
                            <div className="pt-4">
                                <span className="inline-flex rounded-full bg-primary-fixed px-4 py-2 text-[11px] font-bold uppercase tracking-[0.24em] text-primary">Tasador inteligente</span>
                                <h1 className={`mt-6 max-w-2xl font-headline text-4xl font-extrabold tracking-tight sm:text-6xl ${isDark ? 'text-white' : 'text-slate-900'}`}>Descubre cuÃ¡nto podrÃ­as pedir por tu auto.</h1>
                                <p className={`mt-5 max-w-xl text-base leading-8 sm:text-lg ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>Completa la ficha, recibe un rango de precio en colones y si te convence, pasa directo a venderlo con los datos ya adelantados.</p>
                                <div className="mt-8 grid gap-4 sm:grid-cols-2">
                                    <div className={`rounded-2xl border p-5 shadow-sm ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-white/80'}`}>
                                        <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Lo que recibes</span>
                                        <p className={`mt-2 text-sm leading-6 ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>Un precio sugerido, un rango mÃ­nimo y mÃ¡ximo, y una lectura simple del mercado de Costa Rica.</p>
                                    </div>
                                    <div className={`rounded-2xl border p-5 shadow-sm ${isDark ? 'border-white/10 bg-white/5' : 'border-outline-variant/20 bg-white/80'}`}>
                                        <span className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Si te convence</span>
                                        <p className={`mt-2 text-sm leading-6 ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>Comparte la evaluaciÃ³n o pasa directo a publicar el auto con varios datos ya adelantados.</p>
                                    </div>
                                </div>
                            </div>

                            {result ? (
                                <ResultPanel result={result} shareUrl={shareUrl} isDark={isDark} />
                            ) : (
                                <section className={`rounded-[28px] border p-6 shadow-[0_24px_80px_rgba(15,23,42,0.08)] sm:p-8 ${isDark ? 'border-white/10 bg-[#0b1118]' : 'border-outline-variant/25 bg-white'}`}>
                                    <div className="mb-6 flex flex-col gap-3 border-b border-outline-variant/20 pb-6 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <span className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">ValuaciÃ³n guiada</span>
                                            <h2 className={`mt-2 font-headline text-3xl font-extrabold tracking-tight ${isDark ? 'text-white' : 'text-slate-900'}`}>Completa la ficha del auto</h2>
                                        </div>
                                        <div className={`inline-flex rounded-full px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] ${isDark ? 'bg-white/5 text-slate-300' : 'bg-slate-50 text-slate-500'}`}>3 min aprox.</div>
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
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">AÃ±o</span><select name="year" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">{vehicleConfig.years.map((year) => <option key={year} value={year}>{year}</option>)}</select></label>
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">CondiciÃ³n</span><select name="condition" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">{Object.entries(vehicleConfig.conditions).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></label>
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">CarrocerÃ­a</span><select name="body_type" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary"><option value="">Selecciona</option>{vehicleConfig.bodyTypes.map((item) => <option key={item} value={item}>{item}</option>)}</select></label>
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Combustible</span><select name="fuel_type" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary"><option value="">Selecciona</option>{vehicleConfig.fuelTypes.map((item) => <option key={item} value={item}>{item}</option>)}</select></label>
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Transmisión</span><select name="transmission" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary"><option value="">Selecciona</option>{vehicleConfig.transmissions.map((item) => <option key={item} value={item}>{item}</option>)}</select></label>
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Tracción</span><select name="drivetrain" className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary"><option value="">No especificar</option>{vehicleConfig.drivetrains.map((item) => <option key={item} value={item}>{item}</option>)}</select></label>
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Kilometraje</span><input type="number" name="mileage" min="0" step="1" placeholder="Ej. 42000" className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary" /></label>
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Tamaño del motor</span><input type="number" name="engine_size" min="0" max="10" step="0.1" placeholder="Ej. 2.5" className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary" /></label>
                                            <label className="flex flex-col gap-2"><span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Ciudad</span><select name="city" required className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary">{vehicleConfig.cities.map((city) => <option key={city} value={city}>{city}</option>)}</select></label>
                                        </div>

                                        <label className="flex flex-col gap-2 rounded-2xl border border-outline-variant/25 bg-slate-50 p-5">
                                            <span className="text-xs font-bold uppercase tracking-[0.2em] text-primary">Precio de referencia opcional</span>
                                            <input type="number" name="price_reference" min="0" step="1" placeholder="Si quieres comparar contra tu expectativa en colones" className="rounded-2xl border border-outline-variant/30 bg-white px-4 py-4 text-sm font-semibold text-slate-800 shadow-sm outline-none transition focus:border-primary" />
                                            <small className="text-sm text-slate-500">El sistema devolverá el valor en CRC como precio principal y una referencia pequeña en USD.</small>
                                        </label>

                                        <div className="flex flex-col gap-4 rounded-2xl bg-secondary px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-white">Resultado</span>
                                                <p className="mt-2 text-sm font-medium text-white">Recibirás un rango de mercado, una lectura de depreciación y un enlace para compartir la evaluación.</p>
                                            </div>
                                            <button type="submit" className="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-black"><Icon name="query_stats" className="text-[18px]" />Calcular valuación</button>
                                        </div>
                                    </form>
                                </section>
                            )}
                        </div>
                    </div>
                </section>
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

const element = document.getElementById('valuation-react');
if (element) {
    const props = JSON.parse(element.dataset.props || '{}');
    createRoot(element).render(<ValuationPage {...props} />);
}




