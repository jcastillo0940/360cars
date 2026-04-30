import React, { useMemo, useState } from 'react';

function normalizeAppUrl(url) {
    if (!url || typeof window === 'undefined') {
        return url;
    }

    try {
        const parsed = new URL(url, window.location.origin);
        return `${window.location.origin}${parsed.pathname}${parsed.search}${parsed.hash}`;
    } catch {
        return url;
    }
}

function readCookie(name) {
    if (typeof document === 'undefined') {
        return '';
    }

    const match = document.cookie
        .split('; ')
        .find((entry) => entry.startsWith(`${name}=`));

    return match ? decodeURIComponent(match.split('=').slice(1).join('=')) : '';
}

function LogoutButton({ className = '' }) {
    const handleLogout = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = normalizeAppUrl('/logout');

        const token = readCookie('XSRF-TOKEN');

        if (token) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = token;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    };

    return (
        <button type="button" onClick={handleLogout} className={className}>
            Cerrar sesión
        </button>
    );
}

export function Icon({ name, className = '', filled = false }) {
    const sharedProps = {
        'aria-hidden': 'true',
        className,
        fill: 'none',
        focusable: 'false',
        stroke: 'currentColor',
        strokeLinecap: 'round',
        strokeLinejoin: 'round',
        strokeWidth: 2,
        viewBox: '0 0 24 24',
    };

    switch (name) {
        case 'analytics':
            return (
                <svg {...sharedProps}>
                    <path d="M5 20V10" />
                    <path d="M12 20V4" />
                    <path d="M19 20v-7" />
                </svg>
            );
        case 'article':
            return (
                <svg {...sharedProps}>
                    <path d="M7 4h8l4 4v12H7z" />
                    <path d="M15 4v4h4" />
                    <path d="M10 13h6" />
                    <path d="M10 17h6" />
                </svg>
            );
        case 'arrow_back':
            return (
                <svg {...sharedProps}>
                    <path d="M19 12H5" />
                    <path d="m12 19-7-7 7-7" />
                </svg>
            );
        case 'auto_awesome':
            return (
                <svg {...sharedProps}>
                    <path d="m12 3 1.7 4.3L18 9l-4.3 1.7L12 15l-1.7-4.3L6 9l4.3-1.7z" />
                    <path d="m18 15 .9 2.1L21 18l-2.1.9L18 21l-.9-2.1L15 18l2.1-.9z" />
                </svg>
            );
        case 'chat':
            return (
                <svg {...sharedProps}>
                    <path d="M7 17 3 21V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2z" />
                </svg>
            );
        case 'close':
            return (
                <svg {...sharedProps}>
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            );
        case 'compare_arrows':
            return (
                <svg {...sharedProps}>
                    <path d="M8 7h11" />
                    <path d="m16 3 4 4-4 4" />
                    <path d="M16 17H5" />
                    <path d="m8 13-4 4 4 4" />
                </svg>
            );
        case 'directions_car':
            return (
                <svg {...sharedProps}>
                    <path d="m5 16 1.5-5A2 2 0 0 1 8.4 9h7.2a2 2 0 0 1 1.9 1.4L19 16" />
                    <path d="M4 16h16" />
                    <path d="M6 16v2" />
                    <path d="M18 16v2" />
                    <circle cx="7.5" cy="13.5" r="1.25" />
                    <circle cx="16.5" cy="13.5" r="1.25" />
                </svg>
            );
        case 'expand_less':
            return (
                <svg {...sharedProps}>
                    <path d="m6 15 6-6 6 6" />
                </svg>
            );
        case 'expand_more':
            return (
                <svg {...sharedProps}>
                    <path d="m6 9 6 6 6-6" />
                </svg>
            );
        case 'favorite':
            return filled ? (
                <svg aria-hidden="true" className={className} fill="currentColor" focusable="false" viewBox="0 0 24 24">
                    <path d="m12 20.5-1.1-1C5.1 14.2 2 11.4 2 7.9A4.9 4.9 0 0 1 6.9 3 5.4 5.4 0 0 1 12 5.7 5.4 5.4 0 0 1 17.1 3 4.9 4.9 0 0 1 22 7.9c0 3.5-3.1 6.3-8.9 11.6z" />
                </svg>
            ) : (
                <svg {...sharedProps}>
                    <path d="m12 20.5-1.1-1C5.1 14.2 2 11.4 2 7.9A4.9 4.9 0 0 1 6.9 3 5.4 5.4 0 0 1 12 5.7 5.4 5.4 0 0 1 17.1 3 4.9 4.9 0 0 1 22 7.9c0 3.5-3.1 6.3-8.9 11.6z" />
                </svg>
            );
        case 'forum':
            return (
                <svg {...sharedProps}>
                    <path d="M4 15h8l4 4v-4h3a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2z" />
                </svg>
            );
        case 'ios_share':
            return (
                <svg {...sharedProps}>
                    <path d="M12 16V4" />
                    <path d="m8 8 4-4 4 4" />
                    <path d="M5 12v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6" />
                </svg>
            );
        case 'location_on':
            return (
                <svg {...sharedProps}>
                    <path d="M12 21s6-4.6 6-10a6 6 0 1 0-12 0c0 5.4 6 10 6 10Z" />
                    <circle cx="12" cy="11" r="2.25" />
                </svg>
            );
        case 'menu':
            return (
                <svg {...sharedProps}>
                    <path d="M4 7h16" />
                    <path d="M4 12h16" />
                    <path d="M4 17h16" />
                </svg>
            );
        case 'newspaper':
            return (
                <svg {...sharedProps}>
                    <path d="M5 7h12v10a2 2 0 0 0 2 2H7a2 2 0 0 1-2-2z" />
                    <path d="M17 7h2a2 2 0 0 1 2 2v8" />
                    <path d="M8 11h6" />
                    <path d="M8 15h6" />
                </svg>
            );
        case 'notifications_active':
            return (
                <svg {...sharedProps}>
                    <path d="M15 18H5l1.4-1.4a2 2 0 0 0 .6-1.4V11a5 5 0 1 1 10 0v4.2a2 2 0 0 0 .6 1.4L19 18h-4" />
                    <path d="M10 20a2 2 0 0 0 4 0" />
                    <path d="M19 4v3" />
                    <path d="M20.5 5.5h-3" />
                </svg>
            );
        case 'person':
            return (
                <svg {...sharedProps}>
                    <circle cx="12" cy="8" r="3.25" />
                    <path d="M5 19a7 7 0 0 1 14 0" />
                </svg>
            );
        case 'query_stats':
            return (
                <svg {...sharedProps}>
                    <path d="M4 19h16" />
                    <path d="m6 15 4-4 3 3 5-6" />
                </svg>
            );
        case 'search':
            return (
                <svg {...sharedProps}>
                    <circle cx="11" cy="11" r="6" />
                    <path d="m20 20-4.35-4.35" />
                </svg>
            );
        case 'search_off':
            return (
                <svg {...sharedProps}>
                    <circle cx="11" cy="11" r="6" />
                    <path d="m20 20-4.35-4.35" />
                    <path d="M4 4 20 20" />
                </svg>
            );
        case 'sell':
            return (
                <svg {...sharedProps}>
                    <path d="m12 3 8 8-8 8-8-8V3z" />
                    <circle cx="9" cy="9" r="1.25" />
                </svg>
            );
        case 'trending_up':
            return (
                <svg {...sharedProps}>
                    <path d="m4 16 6-6 4 4 6-8" />
                    <path d="M14 6h6v6" />
                </svg>
            );
        case 'tune':
            return (
                <svg {...sharedProps}>
                    <path d="M4 6h8" />
                    <path d="M16 6h4" />
                    <path d="M10 6a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z" />
                    <path d="M4 12h3" />
                    <path d="M11 12h9" />
                    <path d="M7 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z" />
                    <path d="M4 18h10" />
                    <path d="M18 18h2" />
                    <path d="M14 18a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z" />
                </svg>
            );
        case 'verified':
            return (
                <svg {...sharedProps}>
                    <path d="m9 12 2 2 4-4" />
                    <path d="M12 3.5 15 5l3.5.5.5 3.5L20.5 12 19 15l-.5 3.5-3.5.5L12 20.5 9 19l-3.5-.5L5 15 3.5 12 5 9l.5-3.5L9 5z" />
                </svg>
            );
        case 'visibility':
            return (
                <svg {...sharedProps}>
                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                    <circle cx="12" cy="12" r="2.5" />
                </svg>
            );
        default:
            return (
                <svg {...sharedProps}>
                    <circle cx="12" cy="12" r="8" />
                    <path d="M12 8v5" />
                    <path d="M12 17h.01" />
                </svg>
            );
    }
}

export function Logo({ className = '' }) {
    return (
        <div className={`flex items-center ${className}`}>
            <img 
                src="/img/logo.png" 
                alt="Movikaa" 
                className="h-8 w-auto object-contain sm:h-10"
                decoding="async"
                height="1374"
                onError={(e) => {
                    e.target.style.display = 'none';
                    e.target.nextSibling.style.display = 'block';
                }}
                width="3922"
            />
            <span className="hidden font-headline text-2xl font-black tracking-tighter sm:text-3xl">Movikaa</span>
        </div>
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
    return `₡${new Intl.NumberFormat('es-CR').format(Number(value || 0))}`;
}

function AccountMenu({ authUser, sellUrl }) {
    const firstName = authUser?.firstName || 'Cuenta';
    const dashboardUrl = normalizeAppUrl(authUser?.dashboardUrl);
    const sellerUrl = normalizeAppUrl(sellUrl);
    const buyerUrl = normalizeAppUrl(authUser?.buyerUrl);

    return (
        <div className="absolute right-0 top-[calc(100%+0.75rem)] w-72 overflow-hidden rounded-3xl border border-outline-variant/20 bg-white p-3 shadow-2xl">
            <div className="rounded-2xl bg-surface-container-low p-4">
                <p className="text-xs font-bold uppercase tracking-[0.18em] text-primary">Tu cuenta</p>
                <strong className="mt-2 block font-headline text-xl font-extrabold text-slate-900">Hola, {firstName}</strong>
            </div>
            <div className="mt-3 grid gap-2">
                <a href={dashboardUrl} className="rounded-2xl border border-outline-variant/20 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-primary hover:bg-primary-fixed hover:text-primary">Ir a mi panel</a>
                <a href={sellerUrl} className="rounded-2xl border border-secondary bg-secondary px-4 py-3 text-sm font-bold text-white transition hover:bg-secondary-container">Publicar o gestionar autos</a>
                {buyerUrl ? <a href={buyerUrl} className="rounded-2xl border border-outline-variant/20 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-primary hover:bg-primary-fixed hover:text-primary">Mi seguimiento</a> : null}
                <LogoutButton className="rounded-2xl border border-outline-variant/20 px-4 py-3 text-left text-sm font-bold text-slate-700 transition hover:border-primary hover:bg-primary-fixed hover:text-primary" />
            </div>
        </div>
    );
}

export function PublicTopBar({ homeUrl, catalogUrl, brandsUrl, valuationUrl, sellUrl, accountUrl, authUser = null, newsUrl, featuredUrl, transparent = false }) {
    const [menuOpen, setMenuOpen] = useState(false);
    const [accountMenuOpen, setAccountMenuOpen] = useState(false);

    const navigation = [
        { label: 'Comprar', href: catalogUrl },
        { label: 'Marcas', href: brandsUrl || `${homeUrl}#marcas` },
        { label: 'Destacados', href: featuredUrl || `${catalogUrl}?featured=1` },
        { label: 'Estimación de mercado', href: valuationUrl },
        { label: 'Noticias', href: newsUrl || '/noticias' },
    ];

    const greetingLabel = useMemo(() => {
        if (!authUser?.authenticated) {
            return 'Ingresar';
        }

        return `Hola, ${authUser.firstName || 'Cuenta'}`;
    }, [authUser]);

    return (
        <nav className={`fixed inset-x-0 top-0 z-50 transition-all duration-300 ${transparent ? 'border-b border-white/5 bg-transparent' : 'border-b border-outline-variant/30 bg-white/80 backdrop-blur-md'}`}>
            <div className="mx-auto flex h-20 max-w-screen-2xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div className="flex items-center gap-4 lg:gap-12">
                    <button type="button" className="inline-flex h-11 w-11 items-center justify-center rounded-full border border-outline-variant/40 text-primary transition hover:bg-primary/5 md:hidden" onClick={() => setMenuOpen((current) => !current)} aria-label="Abrir menú">
                        <Icon name={menuOpen ? 'close' : 'menu'} className="text-[24px]" />
                    </button>
                    <a href={normalizeAppUrl(homeUrl)} className={transparent ? 'text-white' : 'text-primary'}>
                        <Logo />
                    </a>
                    <div className="hidden md:flex md:gap-6 lg:gap-8">
                        {navigation.map((item) => (
                            <a key={item.label} href={normalizeAppUrl(item.href)} className={`font-headline text-sm font-bold tracking-tight transition-colors lg:text-base ${transparent ? 'text-white/80 hover:text-white' : 'text-slate-600 hover:text-primary'}`}>
                                {item.label}
                            </a>
                        ))}
                    </div>
                </div>
                <div className="hidden items-center gap-4 md:flex">
                    <a href={normalizeAppUrl(sellUrl)} className="rounded-full bg-secondary px-6 py-2.5 font-headline text-sm font-bold text-slate-950 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-[#ffb83a] hover:shadow-md">Vender auto</a>
                    {authUser?.authenticated ? (
                        <div className="relative">
                            <button type="button" onClick={() => setAccountMenuOpen((current) => !current)} className={`inline-flex items-center gap-3 rounded-full border px-4 py-2 text-sm font-bold transition ${transparent ? 'border-white/20 bg-white/5 text-white hover:bg-white/10' : 'border-outline-variant/40 bg-white text-slate-700 hover:border-primary hover:text-primary'}`}>
                                <span className={`inline-flex h-9 w-9 items-center justify-center rounded-full ${transparent ? 'bg-white/10 text-white' : 'bg-primary-fixed text-primary'}`}>
                                    <Icon name="person" className="text-[20px]" />
                                </span>
                                <span>{greetingLabel}</span>
                                <Icon name={accountMenuOpen ? 'expand_less' : 'expand_more'} className="text-[18px]" />
                            </button>
                            {accountMenuOpen ? <AccountMenu authUser={authUser} sellUrl={sellUrl} /> : null}
                        </div>
                    ) : (
                        <a href={normalizeAppUrl(accountUrl)} className={`px-5 py-2 text-sm font-bold transition ${transparent ? 'text-white/80 hover:text-white' : 'text-slate-600 hover:text-primary'}`}>Ingresar</a>
                    )}
                </div>
                <div className="flex items-center gap-2 md:hidden">
                    <a href={normalizeAppUrl(sellUrl)} className="rounded-full bg-secondary px-4 py-2 font-headline text-xs font-bold text-slate-950 shadow-sm transition-colors hover:bg-[#ffb83a]">Vender</a>
                    <button type="button" onClick={() => setAccountMenuOpen((current) => !current)} className="inline-flex h-11 w-11 items-center justify-center rounded-full text-primary" aria-label={authUser?.authenticated ? 'Abrir menú de cuenta' : 'Ir a ingresar'}>
                        <Icon name="person" className="text-[24px]" />
                    </button>
                </div>
            </div>
            {menuOpen ? (
                <div className="mobile-menu border-t border-outline-variant/20 bg-white px-4 py-4 shadow-xl md:hidden">
                    <div className="flex flex-col gap-4">
                        {navigation.map((item) => (
                            <a key={item.label} href={normalizeAppUrl(item.href)} className="font-headline text-base font-bold tracking-tight text-slate-700">{item.label}</a>
                        ))}
                        <div className="mt-3 flex flex-col gap-3 border-t border-outline-variant/20 pt-4">
                            {authUser?.authenticated ? (
                                <>
                                    <div className="rounded-2xl bg-surface-container-low px-4 py-4">
                                        <p className="text-xs font-bold uppercase tracking-[0.18em] text-primary">Cuenta activa</p>
                                        <strong className="mt-2 block font-headline text-lg font-extrabold text-slate-900">Hola, {authUser.firstName || 'Cuenta'}</strong>
                                    </div>
                                    <a href={normalizeAppUrl(authUser.dashboardUrl)} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Ir a mi panel</a>
                                </>
                            ) : (
                                <a href={normalizeAppUrl(accountUrl)} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Ingresar</a>
                            )}
                            <a href={normalizeAppUrl(sellUrl)} className="rounded bg-secondary px-4 py-3 text-center font-headline font-bold text-white">Vender mi auto</a>
                            {authUser?.authenticated ? <LogoutButton className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700" /> : null}
                        </div>
                    </div>
                </div>
            ) : null}
            {accountMenuOpen && authUser?.authenticated ? (
                <div className="border-t border-outline-variant/20 bg-white px-4 py-4 shadow-xl md:hidden">
                    <div className="flex flex-col gap-3">
                        <a href={normalizeAppUrl(authUser.dashboardUrl)} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Ir a mi panel</a>
                        {authUser.buyerUrl ? <a href={normalizeAppUrl(authUser.buyerUrl)} className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700">Mi actividad</a> : null}
                        <a href={normalizeAppUrl(sellUrl)} className="rounded bg-secondary px-4 py-3 text-center font-headline font-bold text-white">Publicar o gestionar autos</a>
                        <LogoutButton className="rounded border border-outline-variant/40 px-4 py-3 text-center font-headline font-bold text-slate-700" />
                    </div>
                </div>
            ) : null}
        </nav>
    );
}

export function PublicFooter({ homeUrl, catalogUrl, brandsUrl, valuationUrl, sellUrl, loginUrl, newsUrl, termsUrl, privacyUrl, cookiesUrl }) {
    return (
        <footer className="mt-16 bg-slate-950 text-white">
            <div className="mx-auto grid max-w-screen-2xl grid-cols-1 gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.5fr_repeat(3,minmax(0,1fr))] lg:px-8">
                <div>
                    <a href={normalizeAppUrl(homeUrl)} className="text-white">
                        <Logo />
                    </a>
                    <p className="mt-5 max-w-md text-sm leading-7 text-slate-400">Marketplace automotriz para Costa Rica con inventario real, herramientas claras para vender y una búsqueda pensada para decidir más rápido.</p>
                    <div className="mt-6 flex flex-wrap gap-3">
                        <a href={normalizeAppUrl(sellUrl)} className="inline-flex items-center justify-center rounded bg-secondary px-5 py-3 text-sm font-bold text-white transition hover:bg-secondary-container">Publicar auto</a>
                        <a href={normalizeAppUrl(loginUrl)} className="inline-flex items-center justify-center rounded border border-secondary bg-secondary/10 px-5 py-3 text-sm font-bold text-secondary transition hover:bg-secondary hover:text-white">Ingresar</a>
                    </div>
                </div>
                <div>
                    <h3 className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Explorar</h3>
                    <div className="mt-5 flex flex-col gap-3 text-sm text-slate-400">
                        <a href={normalizeAppUrl(catalogUrl)} className="transition hover:text-white">Inventario</a>
                        <a href={normalizeAppUrl(brandsUrl || catalogUrl)} className="transition hover:text-white">Marcas</a>
                        <a href={normalizeAppUrl(valuationUrl)} className="transition hover:text-white">Estimación de mercado</a>
                        <a href={normalizeAppUrl(sellUrl)} className="transition hover:text-white">Vender mi auto</a>
                    </div>
                </div>
                <div>
                    <h3 className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Cuenta</h3>
                    <div className="mt-5 flex flex-col gap-3 text-sm text-slate-400">
                        <a href={normalizeAppUrl(loginUrl)} className="transition hover:text-white">Iniciar sesión</a>
                        <a href={normalizeAppUrl(sellUrl)} className="transition hover:text-white">Publicar anuncio</a>
                        <a href={normalizeAppUrl(newsUrl || '/noticias')} className="transition hover:text-white">Noticias</a>
                        <a href={normalizeAppUrl(catalogUrl)} className="transition hover:text-white">Buscar autos</a>
                    </div>
                </div>
                <div>
                    <h3 className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Legal</h3>
                    <div className="mt-5 flex flex-col gap-3 text-sm text-slate-400">
                        <a href={normalizeAppUrl(termsUrl)} className="transition hover:text-white">Términos de servicio</a>
                        <a href={normalizeAppUrl(privacyUrl)} className="transition hover:text-white">Política de privacidad</a>
                        <a href={normalizeAppUrl(cookiesUrl)} className="transition hover:text-white">Política de cookies</a>
                    </div>
                </div>
            </div>
            <div className="border-t border-white/10">
                <div className="mx-auto flex max-w-screen-2xl flex-col gap-3 px-4 py-5 text-xs text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                    <span>© 2026 Movikaa Costa Rica. Todos los derechos reservados.</span>
                
                </div>
            </div>
        </footer>
    );
}
