import React from 'react';
import { createRoot } from 'react-dom/client';
import { Icon, PublicFooter, PublicTopBar } from './public-shell';

function NewsCard({ post }) {
    return (
        <a href={post.url} className="group overflow-hidden rounded-[1.75rem] border border-outline-variant/20 bg-white shadow-xl transition hover:-translate-y-1 hover:shadow-2xl">
            <div className="relative h-56 overflow-hidden bg-slate-100">
                {post.cover_image_url ? (
                    <img src={post.cover_image_url} alt={post.title} className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                ) : (
                    <div className="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top_left,_rgba(0,91,183,0.22),_transparent_34%),linear-gradient(180deg,#0b1118_0%,#111827_100%)] text-white">
                        <Icon name="article" className="text-[42px]" />
                    </div>
                )}
                {post.is_featured ? <span className="absolute left-4 top-4 rounded-full bg-secondary px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950">Destacado</span> : null}
            </div>
            <div className="p-6">
                <p className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{post.published_at || 'Sin fecha'} · {post.author_name}</p>
                <h2 className="mt-3 font-headline text-2xl font-extrabold tracking-tight text-slate-950">{post.title}</h2>
                <p className="mt-3 text-sm leading-7 text-slate-600">{post.excerpt || 'Lee la nota completa para conocer el detalle.'}</p>
                <div className="mt-5 inline-flex items-center gap-2 text-sm font-bold text-primary">Leer artículo</div>
            </div>
        </a>
    );
}

function NewsIndexPage({ homeUrl, catalogUrl, brandsUrl, newsUrl, valuationUrl, sellUrl, accountUrl, loginUrl, authUser, publicTheme = 'light', posts, footerLinks }) {
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
                newsUrl={newsUrl}
                featuredUrl={`${catalogUrl}?featured=1`}
            />
            <main className="pt-20">
                <section className={`border-b border-outline-variant/20 py-16 sm:py-20 ${isDark ? 'bg-transparent' : 'bg-gradient-to-br from-[#eff5ff] via-white to-[#f8efe8]'}`}>
                    <div className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                        <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Noticias</p>
                        <h1 className={`mt-4 max-w-3xl font-headline text-4xl font-extrabold tracking-tight sm:text-5xl ${isDark ? 'text-white' : 'text-slate-950'}`}>Actualidad, guías y novedades del mercado automotriz.</h1>
                        <p className={`mt-5 max-w-2xl text-base sm:text-lg ${isDark ? 'text-slate-300' : 'text-slate-600'}`}>Publicaciones editoriales para entender mejor el mercado, tomar decisiónes con más contexto y mantenerte al día con Movikaa.</p>
                    </div>
                </section>

                <section className="mx-auto max-w-screen-2xl px-4 py-16 sm:px-6 lg:px-8">
                    {posts.data.length ? (
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                            {posts.data.map((post) => <NewsCard key={post.id} post={post} />)}
                        </div>
                    ) : (
                        <div className="rounded-[2rem] border border-outline-variant/20 bg-white p-10 text-center shadow-xl">
                            <Icon name="newspaper" className="text-[42px] text-primary" />
                            <h2 className="mt-4 font-headline text-2xl font-extrabold tracking-tight">Todavía no hay artículos publicados</h2>
                            <p className="mt-3 text-sm text-slate-500">En cuanto el equipo publique contenido, aparecerá aquí automáticamente.</p>
                        </div>
                    )}
                </section>
            </main>
            <PublicFooter
                homeUrl={homeUrl}
                catalogUrl={catalogUrl}
                brandsUrl={brandsUrl}
                valuationUrl={valuationUrl}
                sellUrl={sellUrl}
                loginUrl={loginUrl || accountUrl}
                newsUrl={newsUrl}
                termsUrl={footerLinks.termsUrl}
                privacyUrl={footerLinks.privacyUrl}
                cookiesUrl={footerLinks.cookiesUrl}
            />
        </div>
    );
}

function NewsShowPage({ homeUrl, catalogUrl, brandsUrl, newsUrl, valuationUrl, sellUrl, accountUrl, loginUrl, authUser, publicTheme = 'light', post, relatedPosts, footerLinks }) {
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
                newsUrl={newsUrl}
                featuredUrl={`${catalogUrl}?featured=1`}
            />
            <main className="pt-20">
                <section className="mx-auto max-w-screen-4xl px-4 py-10 sm:px-6 lg:px-8">
                    <a href={newsUrl} className="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline"><Icon name="arrow_back" className="text-[18px]" />Volver a noticias</a>
                    <article className="mt-6 overflow-hidden rounded-[2rem] border border-outline-variant/20 bg-white shadow-2xl">
                        <div className="relative h-80 overflow-hidden bg-slate-100 sm:h-[28rem]">
                            {post.cover_image_url ? (
                                <img src={post.cover_image_url} alt={post.title} className="h-full w-full object-cover" />
                            ) : (
                                <div className="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top_left,_rgba(0,91,183,0.22),_transparent_34%),linear-gradient(180deg,#0b1118_0%,#111827_100%)] text-white">
                                    <Icon name="article" className="text-[56px]" />
                                </div>
                            )}
                        </div>
                        <div className="mx-auto max-w-4xl px-6 py-8 sm:px-10 sm:py-12">
                            <div className="flex flex-wrap items-center gap-3">
                                {post.is_featured ? <span className="rounded-full bg-secondary px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950">Destacado</span> : null}
                                <span className="text-xs font-bold uppercase tracking-[0.2em] text-secondary">{post.published_at || 'Sin fecha'} · {post.author_name}</span>
                            </div>
                            <h1 className="mt-4 font-headline text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl">{post.title}</h1>
                            {post.excerpt ? <p className="mt-5 text-lg leading-8 text-slate-600">{post.excerpt}</p> : null}
                            <div className="prose prose-slate mt-8 max-w-none prose-headings:font-headline prose-headings:font-extrabold prose-p:leading-8" dangerouslySetInnerHTML={{ __html: post.content_html }} />
                        </div>
                    </article>
                </section>

                {relatedPosts?.length ? (
                    <section className="mx-auto max-w-screen-2xl px-4 pb-16 sm:px-6 lg:px-8">
                        <div className="mb-8 flex items-end justify-between gap-4">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-[0.24em] text-secondary">Más contenido</p>
                                <h2 className="mt-2 font-headline text-3xl font-extrabold tracking-tight">También puede interesarte</h2>
                            </div>
                            <a href={newsUrl} className="text-sm font-bold text-primary hover:underline">Ver todas</a>
                        </div>
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                            {relatedPosts.map((item) => <NewsCard key={item.id} post={item} />)}
                        </div>
                    </section>
                ) : null}
            </main>
            <PublicFooter
                homeUrl={homeUrl}
                catalogUrl={catalogUrl}
                brandsUrl={brandsUrl}
                valuationUrl={valuationUrl}
                sellUrl={sellUrl}
                loginUrl={loginUrl || accountUrl}
                newsUrl={newsUrl}
                termsUrl={footerLinks.termsUrl}
                privacyUrl={footerLinks.privacyUrl}
                cookiesUrl={footerLinks.cookiesUrl}
            />
        </div>
    );
}

const listElement = document.getElementById('news-react');
if (listElement) {
    createRoot(listElement).render(<NewsIndexPage {...JSON.parse(listElement.dataset.props || '{}')} />);
}

const detailElement = document.getElementById('news-show-react');
if (detailElement) {
    createRoot(detailElement).render(<NewsShowPage {...JSON.parse(detailElement.dataset.props || '{}')} />);
}
