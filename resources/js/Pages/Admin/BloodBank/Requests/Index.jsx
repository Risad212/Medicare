import AdminLayout from '../../../../Components/AdminLayout';

const statuses = ['pending', 'approved', 'partially_approved', 'fulfilled', 'rejected', 'cancelled'];
const statusStyle = { pending: 'p-pending', approved: 'p-active', partially_approved: 'p-progress', fulfilled: 'p-active', rejected: 'p-inactive', cancelled: 'p-inactive' };

export default function BloodRequestsIndex({ requests, bloodGroups, filters, routes }) {
    return (
        <AdminLayout title="Blood requests" active="blood-requests" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Blood <em>requests</em></h1><p className="mc-sub">Patient needs, urgency levels, and approval status.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> New request</a></div>
            </div>
            <div className="mc-ecg"><span>Live register</span><span>{requests.total} requests</span></div>
            <form action={routes.index} method="get" className="mc-bar">
                <div className="mc-search flex-1"><label htmlFor="request-search" className="sr-only">Search patient</label><input id="request-search" type="search" name="search" defaultValue={filters.search} placeholder="Search patient…" /></div>
                <select name="blood_group_id" className="mc-sel" defaultValue={filters.bloodGroupId} aria-label="Filter by blood group"><option value="">All groups</option>{bloodGroups.map((group) => <option key={group.id} value={group.id}>{group.name}</option>)}</select>
                <select name="urgency" className="mc-sel" defaultValue={filters.urgency} aria-label="Filter by urgency"><option value="">All urgencies</option>{['normal', 'urgent', 'emergency'].map((urgency) => <option key={urgency} value={urgency}>{capitalize(urgency)}</option>)}</select>
                <select name="status" className="mc-sel" defaultValue={filters.status} aria-label="Filter by status"><option value="">All statuses</option>{statuses.map((status) => <option key={status} value={status}>{formatStatus(status)}</option>)}</select>
                <button type="submit" className="mc-btn sm">Filter</button>
                {Object.values(filters).some(Boolean) && <a href={routes.index} className="mc-btn sm ghost">Clear</a>}
            </form>
            <section className="mc-card"><div className="overflow-x-auto"><table className="mc-tbl">
                <thead><tr><th>#</th><th>Patient</th><th>Group</th><th>Qty</th><th>Urgency</th><th>Required</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>{requests.data.length ? requests.data.map((request) => <tr key={request.id}>
                    <td className="mc-idx">#{request.id}</td><td><b>{request.patientName}</b><span className="mc-sub2 block">{request.department || 'General'}</span></td><td><span className="mc-av r sm">{request.bloodGroup}</span></td><td>{request.quantity} {request.unit}</td>
                    <td><span className={`mc-pill ${request.urgency === 'emergency' ? 'p-inactive' : request.urgency === 'normal' ? 'p-active' : ''}`}>{capitalize(request.urgency)}</span></td><td>{request.requiredDate}</td><td><span className={`mc-pill ${statusStyle[request.status]}`}>{formatStatus(request.status)}</span></td>
                    <td><a href={`${routes.showBase}/${request.id}`} className="mc-btn sm">View</a></td>
                </tr>) : <tr><td colSpan="8"><div className="mc-empty"><b>No requests</b>No blood requests match your filters.</div></td></tr>}</tbody>
            </table></div><Pagination page={requests} /></section>
        </AdminLayout>
    );
}

function capitalize(value) { return value.charAt(0).toUpperCase() + value.slice(1); }
function formatStatus(value) { return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase()); }
function Pagination({ page }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label="Blood request pages" className="flex items-center gap-1">{page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl}>‹</a> : <span className="page-link opacity-50">‹</span>}<span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>{page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl}>›</a> : <span className="page-link opacity-50">›</span>}</nav></div>;
}
