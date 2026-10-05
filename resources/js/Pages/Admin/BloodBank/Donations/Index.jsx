import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const statusStyle = {
    available: 'p-active',
    reserved: 'p-active',
    issued: 'p-active',
    expired: 'p-inactive',
    rejected: 'p-inactive',
    testing: 'p-progress',
    collected: 'p-pending',
};
const statuses = ['collected', 'testing', 'available', 'reserved', 'issued', 'expired', 'rejected'];

export default function BloodDonationsIndex({ donations, bloodGroups, filters, routes }) {
    const { flash = {} } = usePage().props;

    return (
        <AdminLayout title="Blood donations" active="blood-bank" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Blood <em>donations</em></h1><p className="mc-sub">Every collected unit — from draw to issue or expiry.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Record donation</a></div>
            </div>
            {flash.error && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{flash.error}</div>}
            <div className="mc-ecg"><span>Live register</span><span>{donations.total} donations</span></div>
            <form action={routes.index} method="get" className="mc-bar">
                <div className="mc-search flex-1"><label htmlFor="donation-search" className="sr-only">Search donor or bag number</label><input id="donation-search" type="search" name="search" defaultValue={filters.search} placeholder="Search donor or bag no…" /></div>
                <select name="blood_group_id" className="mc-sel" defaultValue={filters.bloodGroupId} aria-label="Filter by blood group"><option value="">All groups</option>{bloodGroups.map((group) => <option key={group.id} value={group.id}>{group.name}</option>)}</select>
                <select name="status" className="mc-sel" defaultValue={filters.status} aria-label="Filter by status"><option value="">All statuses</option>{statuses.map((status) => <option key={status} value={status}>{capitalize(status)}</option>)}</select>
                <input type="date" name="from" className="mc-date" defaultValue={filters.from} aria-label="From date" />
                <input type="date" name="to" className="mc-date" defaultValue={filters.to} aria-label="To date" />
                <button type="submit" className="mc-btn sm">Filter</button>
                {Object.values(filters).some(Boolean) && <a href={routes.index} className="mc-btn sm ghost">Clear</a>}
            </form>
            <section className="mc-card">
                <div className="overflow-x-auto"><table className="mc-tbl">
                    <thead><tr><th>#</th><th>Donor</th><th>Group</th><th>Qty</th><th>Donation date</th><th>Expiry</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>{donations.data.length ? donations.data.map((donation, index) => <tr key={donation.id}>
                        <td className="mc-idx">{(donations.firstItem || 1) + index}</td><td>{donation.donorName}</td><td>{donation.bloodGroup}</td><td>{donation.quantity} {donation.unit}</td><td>{donation.donationDate}</td><td>{donation.expiryDate}</td><td><span className={`mc-pill ${statusStyle[donation.status] || ''}`}>{capitalize(donation.status)}</span></td>
                        <td><div className="mc-acts">
                            <a href={`${routes.showBase}/${donation.id}`} className="mc-btn sm" aria-label={`View donation ${donation.id}`}>View</a>
                            {donation.canSetAvailable && <button type="button" className="mc-btn sm" onClick={() => { if (window.confirm('Mark this donation as available?')) router.patch(`${routes.statusBase}/${donation.id}/status`, { status: 'available' }); }}>Available</button>}
                            <a href={`${routes.editBase}/${donation.id}/edit`} className="mc-btn sm dark" aria-label={`Edit donation ${donation.id}`}>Edit</a>
                            {donation.canDelete && <button type="button" className="mc-btn sm danger-ghost" onClick={() => { if (window.confirm('Delete this donation?')) router.delete(`${routes.deleteBase}/${donation.id}`); }}>Delete</button>}
                        </div></td>
                    </tr>) : <tr><td colSpan="8"><div className="mc-empty"><b>No donations</b>No donations match your filters.</div></td></tr>}</tbody>
                </table></div>
                <Pagination page={donations} />
            </section>
        </AdminLayout>
    );
}

function capitalize(value) {
    return value.charAt(0).toUpperCase() + value.slice(1);
}

function Pagination({ page }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label="Donation pages" className="flex items-center gap-1">{page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl}>‹</a> : <span className="page-link opacity-50">‹</span>}<span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>{page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl}>›</a> : <span className="page-link opacity-50">›</span>}</nav></div>;
}
