import AdminLayout from '../../../Components/AdminLayout';

export default function UsersIndex({ users, filters, routes }) {
    return (
        <AdminLayout title="Staff and users" active="users" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Access control</p>
                    <h1 className="mc-title">Staff <em>&amp; users</em></h1>
                    <p className="mc-sub">Hospital staff accounts only. Patient records are managed separately.</p>
                </div>
            </div>

            <div className="mc-ecg"><span>Staff register</span><span>{users.total} accounts</span></div>
            <div className="mc-bar">
                <form action={routes.index} method="get" className="mc-search flex-1">
                    <i aria-hidden="true" className="bi bi-search text-faint" />
                    <label htmlFor="staff-search" className="sr-only">Search staff by name or email</label>
                    <input id="staff-search" type="search" name="search" placeholder="Search name or email…" defaultValue={filters.search} autoComplete="off" />
                    <button type="submit" className="mc-btn sm">Search</button>
                </form>
            </div>

            <section className="mc-card" aria-label="Staff accounts">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>User</th><th>Staff role</th><th>Joined</th><th className="text-right">Action</th></tr></thead>
                        <tbody>
                            {users.data.length ? users.data.map((user, index) => (
                                <tr key={user.id}>
                                    <td className="mc-idx">{(users.firstItem || 1) + index}</td>
                                    <td>
                                        <div className="mc-who">
                                            <span className={`mc-av ${['t', 'a', 'b', 'r', ''][user.id % 5]}`}>{(user.name || '?').slice(0, 1).toUpperCase()}</span>
                                            <span><b>{user.name}</b><span className="mc-sub2 block">{user.email}</span></span>
                                        </div>
                                    </td>
                                    <td><span className="mc-pill p-info"><i />{user.role}</span></td>
                                    <td className="mc-num">{user.joinedAt || '—'}</td>
                                    <td><div className="mc-acts"><a href={`${routes.editBase}/${user.id}/edit`} className="mc-btn sm"><i aria-hidden="true" className="bi bi-pencil" /> Access</a></div></td>
                                </tr>
                            )) : <tr><td colSpan="5"><div className="mc-empty"><b>No staff accounts found</b>Try a different name or email.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={users} label="Staff pages" />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page, label }) {
    return (
        <div className="mc-pg">
            <span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span>
            <nav aria-label={label} className="flex items-center gap-1">
                {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
                {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
            </nav>
        </div>
    );
}
