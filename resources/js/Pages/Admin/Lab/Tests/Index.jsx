import { router } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

export default function LabTestsIndex({ labTests, filters, routes }) {
    function deleteTest(test) {
        if (window.confirm(`Delete "${test.name}"?`)) {
            router.delete(`${routes.deleteBase}/${test.id}`);
        }
    }

    return (
        <AdminLayout title="Lab tests" active="lab-tests" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Laboratory</p>
                    <h1 className="mc-title">Lab <em>tests</em></h1>
                    <p className="mc-sub">Registered tests, categories, pricing, and reference ranges.</p>
                </div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add new test</a></div>
            </div>
            <div className="mc-bar">
                <form action={routes.index} method="get" className="mc-search flex-1">
                    <i aria-hidden="true" className="bi bi-search text-faint" />
                    <label htmlFor="lab-test-search" className="sr-only">Search tests or categories</label>
                    <input id="lab-test-search" type="search" name="search" placeholder="Search test or category…" defaultValue={filters.search} autoComplete="off" />
                    <button type="submit" className="mc-btn sm">Search</button>
                </form>
            </div>
            <section className="mc-card" aria-label="Laboratory tests">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Name</th><th>Category</th><th>Price</th><th>Normal range</th><th>Unit</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            {labTests.data.length ? labTests.data.map((test, index) => (
                                <tr key={test.id}>
                                    <td className="mc-idx">{(labTests.firstItem || 1) + index}</td>
                                    <td><b className="text-ink">{test.name}</b>{test.description && <div className="text-xs text-mut">{test.description}</div>}</td>
                                    <td>{test.category || 'N/A'}</td>
                                    <td className="mc-num">{test.price}</td>
                                    <td>{test.normalRange || 'N/A'}</td>
                                    <td>{test.unit || 'N/A'}</td>
                                    <td><span className={`mc-pill ${test.status ? 'p-active' : 'p-inactive'}`}><i />{test.status ? 'Active' : 'Inactive'}</span></td>
                                    <td><div className="mc-acts">
                                        <a href={`${routes.editBase}/${test.id}/edit`} className="mc-btn sm"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a>
                                        <button type="button" onClick={() => deleteTest(test)} className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                                    </div></td>
                                </tr>
                            )) : <tr><td colSpan="8"><div className="mc-empty"><b>No tests found</b>Try another search or add a laboratory test.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={labTests} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    return (
        <div className="mc-pg">
            <span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span>
            <nav aria-label="Laboratory test pages" className="flex items-center gap-1">
                {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
                {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
            </nav>
        </div>
    );
}
