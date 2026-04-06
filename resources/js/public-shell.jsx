import React, { useMemo, useState } from 'react';

export function Icon({ name, className = '', filled = false }) {
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

export function PriceStack({ primary, secondary, align = 'left', large = false }) {
    return (
        <div className={align === 'right' ? 'text-right' : ''}>
            <span className={large ? 'block text-4xl font-black text-primary' : 'block text-xl font-black text-primary'}>{primary}</span>
            {secondary ? <span className={large ? 'mt-2 block text-sm font-semibold text-slate-400' : 'mt-1 block text-xs font-semibold text-slate-400'}>{secondary}</span> : null}
        </div>
    );
}

export function formatCRC(value) {
    return `\u20a1${new Intl.NumberFormat('es-CR').format(Number(value || 0))}`;
}

function AccountMenu({ authUser, sellUrl }) {
    const firstName = authUser?.firstName || 'Cuenta';

    return (
        <div className="absolute right-0 top-[calc(100%+0.75rem)] w-72 overflow-hidden rounded-3xl border border-outline-variant/20 bg-white p-3 shadow-2xl">
            <div className="rounded-2xl bg-surface-container-low p-4">
                <p className="text-xs font-bold uppercase tracking-[0.18em] text-primary">Tu cuenta</p>
                <strong className="mt-2 block font-headline text-xl font-extrabold text-slate-900">Hola, {firstName}</strong>
                <p className="mt-2 text-sm text-slate-500">Tu sesion ya esta activa. Entra a tu panel o continua publicando autos.</p>
            </div>
            <div className="mt-3 grid gap-2">
                <a href={authUser.dashboardUrl} className="rounded-2xl border border-outline-variant/20 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-primary hover:bg-primary-fixed hover:text-primary">Ir a mi panel</a>
                <a href={sellUrl} className="rounded-2xl border border-secondary bg-secondary px-4 py-3 text-sm font-bold text-white transition hover:bg-secondary-container">Publicar o gestionar autos</a>
                {authUser.buyerUrl ? <a href={authUser.buyerUrl} className="rounded-2xl border border-outline-variant/20 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-primary hover:bg-primary-fixed hover:text-primary">Ver mi actividad</a> : null}
            </div>
        </div>
    );
}

export function PublicTopBar({ homeUrl, catalogUrl, valuationUrl, sellUrl, accountUrl, authUser = null, newsUrl, featuredUrl }) {
    const [menuOpen, setMenuOpen] = useState(false);
    const [accountMenuOpen, setAccountMenuOpen] = useState(false);

    const navigation = [
        { label: 'Comprar', href: catalogUrl },
        { label: 'Destacados', href: featuredUrl || `${homeUrl}#destacados` },
        { label: 'Valuacion', href: valuationUrl },
        { label: 'Noticias', href: newsUrl || `${homeUrl}#noticias` },
    ];

    const greetingLabel = useMemo(() => {
        if (!authUser?.authenticated) {
            return 'Ingresar';
        }

        return `Hola, ${authUser.firstName || 'Cuenta'}`;
    }, [authUser]);

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
                        {navigation.map((item) => (
                            <a key={item.label} href={item.href} className="font-headline text-sm font-bold tracking-tight text-slate-600 transition-colors hover:text-primary lg:text-base">
                                {item.label}
                            </a>
                        ))}
                    </div>
                </div>
                <div className="hidden items-center gap-4 md:flex">
                    {authUser?.authenticated ? (
                        <div className="relative">
                            <button
                                type="button"
                                onClick={() => setAccountMenuOpen((current) => !current)}
                                className="inline-flex items-center gap-3 rounded-full border border-outline-variant/40 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-primary hover:text-primary"
                            >
                                <span className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary-fixed text-primary">
                                    <Icon name="person" className="text-[20px]" />
                                </span>
                                <span>{greetingLabel}</span>
                                <Icon name={accountMenuOpen ? 'expand_less' : 'expand_more'} className="text-[18px]" />
                            </button>
                            {accountMenuOpen ? <AccountMenu authUser={authUser} sellUrl={sellUrl} /> : null}
                        </div>
                    ) : (
                        <a href={accountUrl} className="px-5 py-2 text-sm font-bold text-slate-600 transition hover:text-primary">Ingresar</a>
                    )}
                    <a href={sellUrl} className="rounded bg-secondary px-4 py-2.5 font-headline text-sm font-bold text-white shadow-md transition-colors hover:bg-secondary-container lg:px-6">Vender mi auto</a>
                </div>
                <button
                    type="button"
                    onClick={() => setAccountMenuOpen((current) => !current)}
                    className="inline-flex h-11 min-w-11 items-center justify-center gap-2 rounded-full text-primary md:hidden"
                    aria-label={authUser?.authenticated ? 'Abrir menu de cuenta' : 'Ir a ingresar'}
                >
                    <Icon name="person" className="text-[24px]" />
                </button>
            </div>
            {menuOpen ? (
                <div className="mobile-menu border-t border-outline-variant/20 bg-white px-4 py-4 shadow-xl md:hidden">
                    <div className="flex flex-col gap-4">
                        {navigation.map((item) => (
                            <a key={item.label} href={item.href} className="font-headline text-base font-bold tracking-tight text-slate-700">
                                {item.label}
                            </a>
                        ))}
                        <div className="mt-3 flex flex-col gap-3 border-t border-outline-variant/20 pt-4">
                            {authUser?.authenticated ? (
                                <>
                                    <div className="rounded-2xl bg-surface-container-low px-4 py-4">
                                        <p className="text-xs font-bold uppercase tracking-[0.18em] text-primary">Cuenta activa</p>
                                        <strong className="mt-2 block font-headline text-lg font-extrabold text-slate-900">Hola, {authUser.firstName || 'Cuenta'}</strong>
                                        <p className="mt-2 text-sm text-slate-500">Tu sesion ya esta iniciada.</p>
                                    </div>
                                    <a href={authUser.dashboardUrl} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Ir a mi panel</a>
                                </>
                            ) : (
                                <a href={accountUrl} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Ingresar</a>
                            )}
                            <a href={sellUrl} className="rounded bg-secondary px-4 py-3 text-center font-headline font-bold text-white">Vender mi auto</a>
                        </div>
                    </div>
                </div>
            ) : null}
            {accountMenuOpen && authUser?.authenticated ? (
                <div className="border-t border-outline-variant/20 bg-white px-4 py-4 shadow-xl md:hidden">
                    <div className="flex flex-col gap-3">
                        <a href={authUser.dashboardUrl} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Ir a mi panel</a>
                        {authUser.buyerUrl ? <a href={authUser.buyerUrl} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Mi actividad</a> : null}
                        <a href={sellUrl} className="rounded bg-secondary px-4 py-3 text-center font-headline font-bold text-white">Publicar o gestionar autos</a>
                    </div>
                </div>
            ) : null}
        </nav>
    );
}

export function PublicFooter({
    homeUrl,
    catalogUrl,
    valuationUrl,
    sellUrl,
    loginUrl,
    termsUrl,
    privacyUrl,
    cookiesUrl,
}) {
    return (
        <footer className="mt-16 bg-slate-950 text-white">
            <div className="mx-auto grid max-w-screen-2xl grid-cols-1 gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.5fr_repeat(3,minmax(0,1fr))] lg:px-8">
                <div>
                    <a href={homeUrl} className="font-headline text-3xl font-black tracking-tight text-white">Movikaa</a>
                    <p className="mt-5 max-w-md text-sm leading-7 text-slate-400">
                        Marketplace automotriz para Costa Rica con inventario publicado por vendedores reales, tasador, filtros claros y una experiencia pensada para convertir.
                    </p>
                    <div className="mt-6 flex flex-wrap gap-3">
                        <a href={sellUrl} className="inline-flex items-center justify-center rounded bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container">Publicar auto</a>
                        <a href={loginUrl} className="inline-flex items-center justify-center rounded border border-secondary bg-secondary/10 px-5 py-3 text-sm font-bold text-secondary transition hover:bg-secondary hover:text-white">Ingresar</a>
                    </div>
                </div>
                <div>
                    <h3 className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Explorar</h3>
                    <div className="mt-5 flex flex-col gap-3 text-sm text-slate-400">
                        <a href={catalogUrl} className="transition hover:text-white">Inventario</a>
                        <a href={valuationUrl} className="transition hover:text-white">Tasador</a>
                        <a href={sellUrl} className="transition hover:text-white">Vende tu auto</a>
                        <a href={`${homeUrl}#destacados`} className="transition hover:text-white">Autos destacados</a>
                    </div>
                </div>
                <div>
                    <h3 className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Cuenta</h3>
                    <div className="mt-5 flex flex-col gap-3 text-sm text-slate-400">
                        <a href={loginUrl} className="transition hover:text-white">Login</a>
                        <a href={sellUrl} className="transition hover:text-white">Publicar anuncio</a>
                        <a href={`${homeUrl}#noticias`} className="transition hover:text-white">Noticias</a>
                        <a href={catalogUrl} className="transition hover:text-white">Buscar por marca</a>
                    </div>
                </div>
                <div>
                    <h3 className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Legal</h3>
                    <div className="mt-5 flex flex-col gap-3 text-sm text-slate-400">
                        <a href={termsUrl} className="transition hover:text-white">Terminos de servicio</a>
                        <a href={privacyUrl} className="transition hover:text-white">Politica de privacidad</a>
                        <a href={cookiesUrl} className="transition hover:text-white">Politica de cookies</a>
                    </div>
                </div>
            </div>
            <div className="border-t border-white/10">
                <div className="mx-auto flex max-w-screen-2xl flex-col gap-3 px-4 py-5 text-xs text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                    <span>(c) 2026 Movikaa Costa Rica. Todos los derechos reservados.</span>
                    <span>
                        Desarrollado por{' '}
                        <a href="https://pixelprocr.com" target="_blank" rel="noreferrer" className="font-semibold text-secondary transition hover:text-white">
                            PixelPRO
                        </a>
                    </span>
                </div>
            </div>
        </footer>
    );
}
