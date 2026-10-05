import { useEffect, useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';

function imageUrl(path) {
    if (!path) return '';
    return /^https?:\/\//i.test(path) ? path : `/storage/${path.replace(/^\/+/, '')}`;
}

function SocialLinks({ site }) {
    const items = [
        ['facebook', site.facebook, 'Facebook'],
        ['twitter-x', site.twitter, 'X'],
        ['linkedin', site.linkedin, 'LinkedIn'],
        ['youtube', site.youtube, 'YouTube'],
    ].filter(([, href]) => href);

    return (
        <div className="flex gap-3">
            {items.map(([icon, href, label]) => (
                <a key={icon} href={href} aria-label={label} target="_blank" rel="noreferrer"
                    className="grid h-9 w-9 place-items-center rounded-full border border-white/20 text-xs text-white/80 transition hover:border-gold hover:text-white">
                    <i aria-hidden="true" className={`bi bi-${icon}`} />
                </a>
            ))}
        </div>
    );
}

export default function FrontendLayout({ title, seo, children }) {
    const {
        auth,
        flash = {},
        frontendRoutes: routes,
        navLabels: labels,
        site,
        footerServices = [],
        availableLanguages = [],
    } = usePage().props;
    const [menuOpen, setMenuOpen] = useState(false);
    const [unreadCount, setUnreadCount] = useState(0);
    const [notificationError, setNotificationError] = useState('');
    const user = auth?.user;

    useEffect(() => {
        if (!user || !routes.notifications) return undefined;

        let mounted = true;
        const refresh = async () => {
            try {
                const response = await fetch('/notifications/unread-count', {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Could not refresh notifications.');
                const result = await response.json();
                if (mounted) {
                    setUnreadCount(Number(result.count) || 0);
                    setNotificationError('');
                }
            } catch (error) {
                if (mounted) setNotificationError(error.message);
            }
        };

        refresh();
        const interval = window.setInterval(refresh, 60_000);
        return () => {
            mounted = false;
            window.clearInterval(interval);
        };
    }, [routes.notifications, user]);

    const navItems = [
        [routes.home, labels.home],
        [routes.about, labels.about],
        [routes.services, labels.services],
        [routes.doctors, labels.doctors],
        [routes.blog, labels.blog],
        [routes.contact, labels.contact],
    ];

    const portal = user?.role === 'patient'
        ? [routes.profile, labels.profile]
        : user?.role === 'doctor'
            ? [routes.doctorDashboard, 'Dashboard']
            : [routes.adminDashboard, 'Dashboard'];

    return (
        <>
            <Head title={title ? `${title} · ${site.name}` : site.name}>
                {seo?.description && <meta head-key="description" name="description" content={seo.description} />}
                {seo?.keywords && <meta head-key="keywords" name="keywords" content={seo.keywords} />}
                {seo?.title && <meta head-key="og:title" property="og:title" content={seo.title} />}
                {seo?.description && <meta head-key="og:description" property="og:description" content={seo.description} />}
            </Head>

            <div className="bg-teal-dark text-white">
                <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 px-5 py-2 text-xs sm:px-8">
                    <div className="flex flex-wrap gap-x-6 gap-y-1 text-white/80">
                        {site.address && <span><i className="bi bi-geo-alt mr-2 text-gold" />{site.address}</span>}
                        {site.workingHours && <span><i className="bi bi-clock mr-2 text-gold" />{site.workingHours}</span>}
                    </div>
                    <SocialLinks site={site} />
                </div>
            </div>

            <header className="sticky top-0 z-40 border-b border-line bg-paper/95 backdrop-blur">
                <div className="mx-auto flex max-w-7xl items-center gap-5 px-5 py-3 sm:px-8">
                    <Link href={routes.home} className="flex shrink-0 items-center gap-3 no-underline">
                        {site.logo
                            ? <img src={imageUrl(site.logo)} alt={site.name} className="h-11 max-w-40 object-contain" />
                            : <span className="grid h-10 w-10 place-items-center rounded-full bg-teal text-xl font-semibold text-white">+</span>}
                        {!site.logo && <span className="font-display text-xl font-semibold text-ink">{site.name}</span>}
                    </Link>

                    <button type="button" aria-expanded={menuOpen} aria-controls="public-navigation"
                        onClick={() => setMenuOpen((open) => !open)}
                        className="ml-auto grid h-11 w-11 place-items-center rounded-full border border-line text-teal lg:hidden">
                        <span className="sr-only">{menuOpen ? 'Close menu' : 'Open menu'}</span>
                        <i aria-hidden="true" className={`bi bi-${menuOpen ? 'x-lg' : 'list'} text-xl`} />
                    </button>

                    <nav id="public-navigation" aria-label="Main navigation"
                        className={`${menuOpen ? 'absolute inset-x-0 top-full border-b border-line bg-paper px-5 pb-5 shadow-lg' : 'hidden'} lg:static lg:ml-auto lg:block lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none`}>
                        <ul className="flex flex-col gap-1 lg:flex-row lg:items-center lg:gap-5">
                            {navItems.map(([href, label]) => (
                                <li key={href}>
                                    <Link href={href} onClick={() => setMenuOpen(false)}
                                        className="block py-2 text-sm font-semibold text-ink transition hover:text-teal">
                                        {label}
                                    </Link>
                                </li>
                            ))}
                            {availableLanguages.length > 1 && (
                                <li>
                                    <label className="sr-only" htmlFor="site-language">{labels.language}</label>
                                    <select id="site-language" aria-label={labels.language} className="rounded-lg border border-line bg-transparent px-2 py-2 text-sm"
                                        value={document.documentElement.lang || 'en'}
                                        onChange={(event) => { window.location.href = `/language/${event.target.value}`; }}>
                                        {availableLanguages.map((language) => (
                                            <option key={language.code} value={language.code}>{language.name}</option>
                                        ))}
                                    </select>
                                </li>
                            )}
                            <li>
                                <Link href={routes.appointment} onClick={() => setMenuOpen(false)}
                                    className="inline-flex items-center justify-center rounded-full bg-teal px-5 py-2.5 text-sm font-bold text-white no-underline transition hover:bg-teal-dark">
                                    {labels.appointment}<span className="ml-2" aria-hidden="true">↗</span>
                                </Link>
                            </li>
                            {user ? (
                                <>
                                    {routes.notifications && (
                                        <li>
                                            <a href={routes.notifications} aria-label={`Notifications${unreadCount ? `, ${unreadCount} unread` : ''}`}
                                                title={notificationError || 'Notifications'}
                                                className="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-line text-teal no-underline">
                                                <i aria-hidden="true" className="bi bi-bell" />
                                                {unreadCount > 0 && <span className="absolute -right-1 -top-1 min-w-5 rounded-full bg-red-600 px-1 text-center text-[10px] font-bold text-white">{unreadCount > 99 ? '99+' : unreadCount}</span>}
                                            </a>
                                        </li>
                                    )}
                                    {portal[0] && <li><a href={portal[0]} className="py-2 text-sm font-semibold text-ink no-underline">{portal[1]}</a></li>}
                                </>
                            ) : (
                                <li><a href={routes.login} className="py-2 text-sm font-semibold text-ink no-underline">{labels.login}</a></li>
                            )}
                        </ul>
                    </nav>
                </div>
            </header>

            {flash.success && <div role="status" className="mx-auto mt-5 max-w-7xl rounded-xl bg-teal-pale px-5 py-3 text-sm text-teal-dark">{flash.success}</div>}
            {flash.error && <div role="alert" className="mx-auto mt-5 max-w-7xl rounded-xl bg-red-50 px-5 py-3 text-sm text-red-800">{flash.error}</div>}

            <main>{children}</main>

            <footer className="mt-20 bg-ink text-white">
                <div className="mx-auto grid max-w-7xl gap-12 px-5 py-14 sm:px-8 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <Link href={routes.home} className="inline-flex min-h-12 items-center no-underline">
                            {site.footerLogo || site.logo
                                ? <img src={imageUrl(site.footerLogo || site.logo)} alt={site.name} className="max-h-12 max-w-48 object-contain" />
                                : <span className="font-display text-2xl font-semibold text-white">{site.name}</span>}
                        </Link>
                        <p className="mt-4 max-w-xs text-sm leading-7 text-white/65">{site.footerDescription}</p>
                        <SocialLinks site={site} />
                    </div>
                    <div>
                        <h2 className="font-display text-xl">Care &amp; services</h2>
                        <ul className="mt-4 space-y-3 text-sm text-white/70">
                            {footerServices.length ? footerServices.map((service) => (
                                <li key={service.slug || service.title}>
                                    <Link href={service.slug ? `/service/${service.slug}` : routes.services} className="text-inherit no-underline hover:text-gold">{service.title}</Link>
                                </li>
                            )) : <li><Link href={routes.services} className="text-inherit no-underline hover:text-gold">All services</Link></li>}
                        </ul>
                    </div>
                    <div>
                        <h2 className="font-display text-xl">Explore MediCare</h2>
                        <ul className="mt-4 space-y-3 text-sm text-white/70">
                            {navItems.slice(1).map(([href, label]) => <li key={href}><Link href={href} className="text-inherit no-underline hover:text-gold">{label}</Link></li>)}
                        </ul>
                    </div>
                    <div>
                        <h2 className="font-display text-xl">Get in touch</h2>
                        <ul className="mt-4 space-y-4 text-sm text-white/70">
                            {site.address && <li className="flex gap-3"><i className="bi bi-geo-alt text-gold" />{site.address}</li>}
                            {site.phone && <li><a href={`tel:${site.phone}`} className="text-inherit no-underline hover:text-gold">{site.phone}</a></li>}
                            {site.email && <li><a href={`mailto:${site.email}`} className="text-inherit no-underline hover:text-gold">{site.email}</a></li>}
                            {site.workingHours && <li>{site.workingHours}</li>}
                        </ul>
                    </div>
                </div>
                <div className="border-t border-white/10">
                    <div className="mx-auto flex max-w-7xl flex-wrap justify-between gap-3 px-5 py-5 text-xs text-white/55 sm:px-8">
                        <span>{site.copyright || `© ${new Date().getFullYear()} ${site.name}`}</span>
                        <span>Care with clarity. Expertise with compassion.</span>
                    </div>
                </div>
            </footer>
        </>
    );
}

export function publicImage(path, fallback = '') {
    if (!path) return fallback;
    return /^https?:\/\//i.test(path) ? path : `/storage/${path.replace(/^\/+/, '')}`;
}
