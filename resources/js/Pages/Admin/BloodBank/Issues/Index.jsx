import AdminLayout from '../../../../Components/AdminLayout';

export default function BloodIssuesIndex({ issues, bloodGroups, filters, routes }) {
    return (
        <AdminLayout title="Blood issues" active="blood-issues" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Blood <em>issues</em></h1><p className="mc-sub">Complete handover history — every unit, receiver, and date.</p></div></div>
            <div className="mc-ecg"><span>Live register</span><span>{issues.total} issues</span></div>
            <form action={routes.index} method="get" className="mc-bar">
                <div className="mc-search flex-1"><label htmlFor="issue-search" className="sr-only">Search patient</label><input id="issue-search" type="search" name="search" defaultValue={filters.search} placeholder="Search patient…" /></div>
                <select name="blood_group_id" className="mc-sel" defaultValue={filters.bloodGroupId} aria-label="Filter by blood group"><option value="">All groups</option>{bloodGroups.map((group) => <option key={group.id} value={group.id}>{group.name}</option>)}</select>
                <input type="date" name="from" className="mc-date" defaultValue={filters.from} aria-label="From date" />
                <input type="date" name="to" className="mc-date" defaultValue={filters.to} aria-label="To date" />
                <button type="submit" className="mc-btn sm">Filter</button>
                {Object.values(filters).some(Boolean) && <a href={routes.index} className="mc-btn sm ghost">Clear</a>}
            </form>
            <section className="mc-card"><div className="overflow-x-auto"><table className="mc-tbl">
                <thead><tr><th>#</th><th>Patient</th><th>Group</th><th>Qty</th><th>Issue date</th><th>Receiver</th><th>Action</th></tr></thead>
                <tbody>{issues.data.length ? issues.data.map((issue) => <tr key={issue.id}><td className="mc-idx">#{issue.id}</td><td><b>{issue.patientName}</b><span className="mc-sub2 block">Request #{issue.requestId}</span></td><td>{issue.bloodGroup}</td><td>{issue.quantity} {issue.unit}</td><td>{issue.issueDate}</td><td>{issue.receiverName}</td><td><a href={`${routes.showBase}/${issue.id}`} className="mc-btn sm">View</a></td></tr>) : <tr><td colSpan="7"><div className="mc-empty"><b>No issues</b>No blood has been issued yet.</div></td></tr>}</tbody>
            </table></div><Pagination page={issues} /></section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label="Blood issue pages" className="flex items-center gap-1">{page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl}>‹</a> : <span className="page-link opacity-50">‹</span>}<span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>{page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl}>›</a> : <span className="page-link opacity-50">›</span>}</nav></div>;
}
