import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

export default function BloodDonorsIndex({ donors, bloodGroups, filters, routes }) {
    const { flash = {} } = usePage().props;

    function updateFilter(name, value) {
        const next = { ...filters, [name]: value };
        const params = new URLSearchParams();
        if (next.search) params.set('search', next.search);
        if (next.bloodGroupId) params.set('blood_group_id', next.bloodGroupId);
        if (next.gender) params.set('gender', next.gender);
        if (next.status !== '') params.set('status', next.status);
        router.get(routes.index, Object.fromEntries(params), { preserveState: true, replace: true });
    }

    return (
        <AdminLayout title="Blood donors" active="blood-donors" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Blood <em>donors</em></h1><p className="mc-sub">Registered donors, their groups, and donation readiness.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add donor</a></div>
            </div>
            {flash.error && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{flash.error}</div>}
            <div className="mc-ecg"><span>Live register</span><span>{donors.total} donors</span></div>
            <div className="mc-bar">
                <form className="mc-search flex-1" onSubmit={(event) => { event.preventDefault(); updateFilter('search', event.currentTarget.elements.search.value); }}>
                    <label htmlFor="donor-search" className="sr-only">Search donors</label><input id="donor-search" name="search" type="search" defaultValue={filters.search} placeholder="Search name, phone or email…" />
                    <button type="submit" className="mc-btn sm">Search</button>
                </form>
                <select className="mc-sel" aria-label="Filter by blood group" value={filters.bloodGroupId} onChange={(event) => updateFilter('bloodGroupId', event.target.value)}><option value="">All blood groups</option>{bloodGroups.map((group) => <option key={group.id} value={group.id}>{group.name}</option>)}</select>
                <select className="mc-sel" aria-label="Filter by gender" value={filters.gender} onChange={(event) => updateFilter('gender', event.target.value)}><option value="">All genders</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
                <select className="mc-sel" aria-label="Filter by status" value={filters.status} onChange={(event) => updateFilter('status', event.target.value)}><option value="">All statuses</option><option value="1">Active</option><option value="0">Inactive</option></select>
            </div>
            <section className="mc-card">
                <div className="overflow-x-auto"><table className="mc-tbl">
                    <thead><tr><th>#</th><th>Donor</th><th>Blood group</th><th>Phone</th><th>Last donation</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>{donors.data.length ? donors.data.map((donor, index) => <tr key={donor.id}>
                        <td className="mc-idx">{(donors.firstItem || 1) + index}</td><td><b>{donor.name}</b><span className="mc-sub2 block">{donor.email || ''}</span></td><td><span className="mc-av r sm">{donor.bloodGroup || '—'}</span></td><td className="mc-num">{donor.phone}</td><td>{donor.lastDonationDate || 'Never'}</td><td><span className={`mc-pill ${donor.status ? 'p-active' : 'p-inactive'}`}>{donor.status ? 'Active' : 'Inactive'}</span></td>
                        <td><div className="mc-acts"><a href={`${routes.showBase}/${donor.id}`} className="mc-btn sm">View</a><a href={`${routes.editBase}/${donor.id}/edit`} className="mc-btn sm dark">Edit</a><button type="button" className="mc-btn sm danger-ghost" onClick={() => { if (window.confirm('Delete this donor and their donation history?')) router.delete(`${routes.deleteBase}/${donor.id}`); }}>Delete</button></div></td>
                    </tr>) : <tr><td colSpan="7"><div className="mc-empty"><b>No donors</b>No donors match your criteria.</div></td></tr>}</tbody>
                </table></div>
                <Pagination page={donors} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label="Donor pages" className="flex items-center gap-1">{page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl}>‹</a> : <span className="page-link opacity-50">‹</span>}<span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>{page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl}>›</a> : <span className="page-link opacity-50">›</span>}</nav></div>;
}
