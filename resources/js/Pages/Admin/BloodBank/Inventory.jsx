import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '../../../Components/AdminLayout';

export default function BloodBankInventory({ inventory, settings, bags, routes }) {
    const { errors = {} } = usePage().props;
    const [form, setForm] = useState({
        low_stock_threshold: String(settings.lowStockThreshold),
        min_donation_days: String(settings.minDonationDays),
    });

    function saveSettings(event) {
        event.preventDefault();
        router.patch(routes.settingsUpdate, form, { preserveScroll: true });
    }

    return (
        <AdminLayout title="Blood inventory" active="blood-bank" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Blood <em>inventory</em></h1><p className="mc-sub">Live stock per group and the pool of available bags.</p></div>
                <div className="mc-head-acts"><a href={routes.dashboard} className="mc-btn ghost">Dashboard</a></div>
            </div>
            <section className="mc-card mb-4">
                <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Stock by group</h2></div>
                <div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Group</th><th>Available</th><th>Reserved</th><th>Issued</th><th>Expired</th><th>Status</th></tr></thead>
                    <tbody>{inventory.length ? inventory.map((row) => <tr key={row.group}><td><b>{row.group}</b></td><td>{row.units.available} units <span className="text-xs text-mut">({row.quantity.available} ml)</span></td><td>{row.units.reserved}</td><td>{row.units.issued}</td><td>{row.units.expired}</td><td>{row.status}</td></tr>) : <tr><td colSpan="6"><div className="mc-empty"><b>No blood groups</b>Add groups to start tracking stock.</div></td></tr>}</tbody>
                </table></div>
            </section>
            <section className="mc-card mb-4">
                <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Configuration</h2></div>
                <form onSubmit={saveSettings} className="grid grid-cols-1 gap-4 p-4 md:grid-cols-3 md:items-end">
                    <Field label="Low-stock threshold" error={errors.low_stock_threshold} value={form.low_stock_threshold} onChange={(value) => setForm({ ...form, low_stock_threshold: value })} min="0" />
                    <Field label="Minimum donation interval (days)" error={errors.min_donation_days} value={form.min_donation_days} onChange={(value) => setForm({ ...form, min_donation_days: value })} min="1" />
                    <button className="mc-btn" type="submit">Save settings</button>
                </form>
            </section>
            <section className="mc-card">
                <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Available / reserved bags</h2></div>
                <div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>#</th><th>Bag</th><th>Donor</th><th>Group</th><th>Qty</th><th>Expiry</th><th>Status</th></tr></thead>
                    <tbody>{bags.data.length ? bags.data.map((bag, index) => <tr key={bag.id}><td className="mc-idx">{(bags.firstItem || 1) + index}</td><td>{bag.bagNumber}</td><td>{bag.donor}</td><td>{bag.group}</td><td>{bag.quantity} {bag.unit}</td><td>{bag.expiryDate}</td><td>{bag.status}</td></tr>) : <tr><td colSpan="7"><div className="mc-empty"><b>Pool is empty</b>No available or reserved bags right now.</div></td></tr>}</tbody>
                </table></div>
                <Pagination page={bags} />
            </section>
        </AdminLayout>
    );
}

function Field({ label, error, value, onChange, min }) {
    return <div><label className="mb-1.5 block text-xs font-bold text-ink-2">{label}</label><input type="number" min={min} className="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm" value={value} onChange={(event) => onChange(event.target.value)} />{error && <p role="alert" className="mt-1 text-xs text-red-t">{error}</p>}</div>;
}

function Pagination({ page }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label="Inventory pages" className="flex items-center gap-1">{page.previousPageUrl ? <a href={page.previousPageUrl} className="page-link">‹</a> : <span className="page-link opacity-50">‹</span>}<span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>{page.nextPageUrl ? <a href={page.nextPageUrl} className="page-link">›</a> : <span className="page-link opacity-50">›</span>}</nav></div>;
}
