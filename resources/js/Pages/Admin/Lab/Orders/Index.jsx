import AdminLayout from '../../../../Components/AdminLayout';

const statuses = {
    pending: ['Pending', 'p-pending'],
    'in-progress': ['In progress', 'p-info'],
    completed: ['Completed', 'p-completed'],
    cancelled: ['Cancelled', 'p-cancelled'],
};

export default function LabOrdersIndex({ orders, filters, routes }) {
    return (
        <AdminLayout title="Laboratory orders" active="laboratory" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Laboratory</p><h1 className="mc-title">Lab <em>orders</em></h1><p className="mc-sub">Search, filter, and manage hospital laboratory orders.</p></div>
                <div className="mc-head-acts"><a href={routes.export} className="mc-btn ghost"><i aria-hidden="true" className="bi bi-download" /> Export CSV</a></div>
            </div>
            <div className="mc-bar">
                <form action={routes.index} method="get" className="flex flex-1 flex-wrap items-center gap-2.5">
                    <div className="mc-search min-h-[42px] min-w-[200px] flex-1">
                        <i aria-hidden="true" className="bi bi-search text-faint" />
                        <label htmlFor="lab-order-search" className="sr-only">Search patient or order number</label>
                        <input id="lab-order-search" type="search" name="search" placeholder="Patient or order #…" defaultValue={filters.search} autoComplete="off" />
                    </div>
                    <label htmlFor="lab-order-status" className="sr-only">Filter by lab order status</label>
                    <select id="lab-order-status" name="status" defaultValue={filters.status} className="h-[42px] min-w-[140px] rounded-lg border border-line bg-white px-2.5 text-[13px] text-ink outline-none focus:border-teal">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option><option value="in-progress">In progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option>
                    </select>
                    <button type="submit" className="mc-btn sm h-[42px]">Filter</button>
                    {(filters.search || filters.status) && <a href={routes.index} className="mc-btn sm ghost h-[42px]">Reset</a>}
                </form>
            </div>
            <div className="mc-ecg"><span>Laboratory register</span><span>{orders.total} orders</span></div>
            <section className="mc-card" aria-label="Laboratory orders">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Date</th><th>Patient</th><th>Doctor</th><th>Tests</th><th>Priority</th><th>Total</th><th>Reports</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            {orders.data.length ? orders.data.map((order) => {
                                const [label, style] = statuses[order.status] || statuses.cancelled;
                                return (
                                    <tr key={order.id}>
                                        <td className="mc-idx">{order.id}</td>
                                        <td className="mc-num">{order.createdAt || '—'}</td>
                                        <td><b className="text-ink">{order.patientName}</b>{order.phone && <div className="text-xs text-mut">{order.phone}</div>}</td>
                                        <td>{order.doctorName}</td>
                                        <td><div className="flex flex-wrap gap-1">{order.tests.map((test, index) => <span key={`${order.id}-${index}`} className="mc-pill p-active text-[11px]">{test}</span>)}</div></td>
                                        <td><span className={`mc-pill ${order.priority === 'urgent' ? 'p-urgent' : 'p-cancelled'}`}><i />{order.priority === 'urgent' ? 'Urgent' : 'Normal'}</span></td>
                                        <td className="mc-num">${order.total}</td>
                                        <td className="mc-num">{order.reportsCount} <i aria-hidden="true" className="bi bi-file-earmark-pdf" /></td>
                                        <td><span className={`mc-pill ${style}`}><i />{label}</span></td>
                                        <td><a href={`${routes.showBase}/${order.id}`} className="mc-btn sm"><i aria-hidden="true" className="bi bi-eye" /> Manage</a></td>
                                    </tr>
                                );
                            }) : <tr><td colSpan="10"><div className="mc-empty"><b>No lab orders found</b>Adjust your filters or search terms.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={orders} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    return (
        <div className="mc-pg">
            <span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span>
            <nav aria-label="Laboratory order pages" className="flex items-center gap-1">
                {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
                {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
            </nav>
        </div>
    );
}
