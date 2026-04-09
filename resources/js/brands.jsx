import React from 'react';
import { createRoot } from 'react-dom/client';
import { PublicFooter, PublicTopBar } from './public-shell';
import { BrandMark } from './brand-assets';

function BrandDirectory({ homeUrl, catalogUrl, brandsUrl, valuationUrl, sellUrl, accountUrl, loginUrl, authUser, makes, publicTheme = 'light', footerLinks }) {
    const isDark = publicTheme === 'dark';

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
                        <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Directorio de marcas</p>
                        <h1 className={`mt-4 max-w-3xl font-headline text-4xl font-extrabold tracking-tight sm:text-5xl ${isDark ? 'text-white' : 'text-slate-950'}`}>Explora todas las marcas activas del catálogo.</h1>
                        <p className={`mt-5 max-w-2xl text-base sm:text-lg ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>Usa esta vista para entrar más rápido al inventario filtrado por fabricante y descubrir qué marcas tienen más oferta publicada en este momento.</p>
                    </div>
                </section>

                <section className="mx-auto max-w-screen-2xl px-4 py-16 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {makes.map((make) => (
                            <a key={make.id} href={`${catalogUrl}?make=${encodeURIComponent(make.name)}`} className="group rounded-[1.75rem] border border-outline-variant/20 bg-white p-6 shadow-xl transition hover:-translate-y-1 hover:border-primary hover:shadow-2xl">
                                <div className="flex items-center gap-4">
                                    <BrandMark name={make.name} className="h-16 w-16 rounded-3xl" />
                                    <div>
                                        <h2 className="font-headline text-2xl font-extrabold tracking-tight text-slate-950">{make.name}</h2>
                                        <p className="mt-1 text-sm text-slate-500">{make.listings_count} autos publicados</p>
                                    </div>
                                </div>
                                <div className="mt-5 inline-flex items-center gap-2 text-sm font-bold text-primary transition group-hover:translate-x-1">
                                    Ver inventario
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

const element = document.getElementById('brands-react');
if (element) {
    createRoot(element).render(<BrandDirectory {...JSON.parse(element.dataset.props || '{}')} />);
}
