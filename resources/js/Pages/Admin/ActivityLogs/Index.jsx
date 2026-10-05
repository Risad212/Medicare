import AdminLayout from '../../../Components/AdminLayout';

function ActionBadge({ action, label }) {
    const style = action.endsWith('.created')
        ? 'p-active'
        : action.endsWith('.updated')
            ? 'p-completed'
            : action.endsWith('.deleted')
                ? 'p-cancelled'
                : 'p-info';

    return <><span className={`mc-pill ${style}`}><i />{label}</span><div className="text-xs text-mut">{action}</div></>;
}

export default function ActivityLogsIndex({ logs, filters, availableActions, routes }) {
    return (
        <AdminLayout title="Activity logs" active="activity-logs" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · System</p>
                    <h1 className="mc-title">Activity <em>logs</em></h1>
                    <p className="mc-sub">Audit trail of actions performed in the administration panel.</p>
                </div>
            </div>

            <div className="mc-bar">
                <form action={routes.index} method="get" className="flex flex-1 flex-wrap items-center gap-2.5">
                    <div className="mc-search min-h-[42px] min-w-[200px] flex-1">
                        <i aria-hidden="true" className="bi bi-search text-faint" />
                        <label htmlFor="activity-search" className="sr-only">Search by user</label>
                        <input id="activity-search" type="search" name="search" placeholder="Search by user…" defaultValue={filters.search} autoComplete="off" />
                    </div>
                    <label htmlFor="activity-action" className="sr-only">Filter activity by action</label>
                    <select id="activity-action" name="action" className="h-[42px] min-w-[150px] rounded-lg border border-line bg-white px-2.5 text-[13px] text-ink outline-none focus:border-teal" value={filters.action} onChange={(event) => event.currentTarget.form.requestSubmit()}>
                        <option value="">All actions</option>
                        {availableActions.map((action) => <option key={action} value={action}>{action}</option>)}
                    </select>
                    <button type="submit" className="mc-btn sm">Search</button>
                    {(filters.search || filters.action) && <a href={routes.index} className="mc-btn sm ghost h-[42px]">Clear</a>}
                </form>
            </div>

            <section className="mc-card" aria-label="Audit log">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Time</th><th>User</th><th>Action</th><th>Record</th><th>Details</th><th>IP address</th></tr></thead>
                        <tbody>
                            {logs.data.length ? logs.data.map((log) => (
                                <tr key={log.id}>
                                    <td className="mc-idx">{log.id}</td>
                                    <td className="mc-num text-[13px]">{log.createdAt || '—'}</td>
                                    <td>{log.userName}</td>
                                    <td><ActionBadge action={log.action} label={log.actionLabel} /></td>
                                    <td className="text-[13px]">{log.recordType}{log.recordId && <span className="mc-pill p-low ml-1">#{log.recordId}</span>}</td>
                                    <td className="text-[13px]">
                                        {log.detailKeys.map((key) => <span key={key} className="mc-pill p-low mr-1">{key}</span>)}
                                        {log.detailCount > log.detailKeys.length && <span className="text-mut">+{log.detailCount - log.detailKeys.length} more</span>}
                                    </td>
                                    <td className="mc-num text-[13px]">{log.ipAddress}</td>
                                </tr>
                            )) : <tr><td colSpan="7"><div className="mc-empty"><b>No activity found</b>There are no log entries matching these filters.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={logs} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    return (
        <div className="mc-pg">
            <span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span>
            <nav aria-label="Activity log pages" className="flex items-center gap-1">
                {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
                {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
            </nav>
        </div>
    );
}
