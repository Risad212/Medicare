import { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';

function Icon({ name, className = '' }) {
    return <i aria-hidden="true" className={`bi bi-${name} ${className}`} />;
}

export default function AdminLayout({ title, active, routes, features, children, actions }) {
    const { auth, flash = {}, csrfToken } = usePage().props;
    const [navOpen, setNavOpen] = useState(false);
    const role = auth.user.role;
    const isAdmin = role === 'admin';
    const isFrontDesk = ['admin', 'receptionist'].includes(role);
    const isLabStaff = ['admin', 'lab-technician'].includes(role) && features.lab;
    const isPharmacist = ['admin', 'pharmacist'].includes(role);

    const navLink = (href, icon, label, key) => (
        <a
            href={href}
            onClick={() => setNavOpen(false)}
            aria-current={active === key ? 'page' : undefined}
            className={`group flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-semibold no-underline transition-colors ${
                active === key ? 'bg-teal-dk text-white shadow-sm' : 'text-ink-2 hover:bg-wash hover:text-teal-dk'
            }`}
        >
            <Icon name={icon} className="w-4 text-[16px]" />
            <span>{label}</span>
            {active === key && <span className="ml-auto h-1.5 w-1.5 rounded-full bg-gold" />}
        </a>
    );

    return (
        <>
            <Head title={title} />
            {navOpen && <button className="fixed inset-0 z-40 bg-ink/40 lg:hidden" onClick={() => setNavOpen(false)} aria-label="Close navigation" />}
            <aside className={`fixed inset-y-0 left-0 z-50 flex w-[264px] flex-col border-r border-line bg-white transition-transform duration-200 lg:translate-x-0 ${
                navOpen ? 'translate-x-0' : '-translate-x-full'
            }`}>
                <a href={routes.dashboard} className="flex items-center gap-3 border-b border-line-2 px-5 py-5 no-underline">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-dk font-display text-[23px] font-bold text-white">M</span>
                    <span>
                        <span className="block text-[17px] font-extrabold tracking-tight text-ink">MediCare</span>
                        <span className="block text-[10px] font-bold uppercase tracking-[0.16em] text-mut">Care operations</span>
                    </span>
                </a>

                <div className="px-4 pt-5">
                    <p className="mc-kicker mb-2 px-3">Workspace</p>
                    <nav aria-label="Admin navigation" className="flex flex-col gap-1">
                        {navLink(routes.dashboard, 'speedometer2', 'Overview', 'dashboard')}
                        {isFrontDesk && navLink(routes.appointments, 'calendar2-check', 'Appointments', 'appointments')}
                        {isFrontDesk && navLink(routes.patients, 'people', 'Patients', 'patients')}
                        {isFrontDesk && navLink(routes.doctors, 'person-badge', 'Doctors', 'doctors')}
                        {isLabStaff && routes.labOrders && navLink(routes.labOrders, 'clipboard2-pulse', 'Laboratory', 'laboratory')}
                        {isPharmacist && navLink(routes.prescriptions, 'capsule', 'Prescriptions', 'prescriptions')}
                    </nav>
                </div>

                {isAdmin && (
                    <div className="px-4 pt-6">
                        <p className="mc-kicker mb-2 px-3">Administration</p>
                        <nav aria-label="Administration navigation" className="flex flex-col gap-1">
                            {navLink(routes.comments, 'chat-left-text', 'Review comments', 'comments')}
                            {navLink(routes.invoices, 'receipt', 'Invoices', 'invoices')}
                        </nav>
                    </div>
                )}

                <div className="mt-auto border-t border-line-2 p-4">
                    <div className="rounded-xl bg-panel p-3">
                        <p className="m-0 text-[11px] font-bold uppercase tracking-wider text-mut">Signed in as</p>
                        <p className="mb-0 mt-1 truncate text-[13px] font-bold text-ink">{auth.user.name}</p>
                        <p className="m-0 mt-0.5 text-[11px] capitalize text-mut">{role.replaceAll('-', ' ')}</p>
                    </div>
                </div>
            </aside>

            <main className="min-h-screen bg-panel lg:pl-[264px]">
                <header className="sticky top-0 z-30 flex min-h-[68px] items-center gap-3 border-b border-line bg-white/95 px-4 backdrop-blur sm:px-7">
                    <button type="button" onClick={() => setNavOpen(true)} className="welly-iconbtn !h-9 !w-9 lg:hidden" aria-label="Open navigation"><Icon name="list" /></button>
                    <div className="min-w-0">
                        <p className="m-0 truncate text-[12px] font-bold text-ink">MediCare Hospital</p>
                        <p className="m-0 truncate text-[10px] capitalize text-mut">
                            {new Intl.DateTimeFormat(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }).format(new Date())}
                            <span className="mx-1.5 text-faint">/</span>{role.replaceAll('-', ' ')} portal
                        </p>
                    </div>
                    <div className="ml-auto flex items-center gap-2">
                        <a href={routes.notifications} className="welly-iconbtn !h-9 !w-9" aria-label="Notifications"><Icon name="bell" /></a>
                        {actions}
                        <form action={routes.logout} method="post" className="m-0">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <button type="submit" className="welly-iconbtn !h-9 !w-9" aria-label="Sign out"><Icon name="box-arrow-right" /></button>
                        </form>
                    </div>
                </header>

                <div className="mx-auto max-w-[1600px] px-4 pb-10 pt-6 sm:px-7">
                    {flash.success && <div role="status" className="mb-4 rounded-lg bg-green-bg px-4 py-3 text-[13px] text-green-t">{flash.success}</div>}
                    {flash.error && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-[13px] text-red-t">{flash.error}</div>}
                    {children}
                </div>
            </main>
        </>
    );
}
