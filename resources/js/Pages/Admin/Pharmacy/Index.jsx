import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function PharmacyIndex({ medicines, lowStockCount, filters, canManage, routes }) {
    const { errors = {} } = usePage().props;

    function deleteMedicine(medicine) {
        if (window.confirm(`Delete ${medicine.name}?`)) {
            router.delete(`${routes.deleteBase}/${medicine.id}`);
        }
    }

    return (
        <AdminLayout title="Medicine stock" active="medicines" routes={routes} features={{ pharmacy: true }}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Pharmacy</p><h1 className="mc-title">Medi<em>cines</em></h1><p className="mc-sub">Stock levels, prices, and expiry dates.</p></div>
                {canManage && <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add medicine</a></div>}
            </div>
            {Object.values(errors).length > 0 && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{Object.values(errors).join(' ')}</div>}
            <div className="mc-ecg"><span>Pharmacy stock</span><span>{lowStockCount} low-stock items</span></div>
            <div className="mc-bar">
                <form action={routes.index} method="get" className="mc-search flex-1">
                    <i aria-hidden="true" className="bi bi-search text-faint" />
                    <label htmlFor="medicine-search" className="sr-only">Search medicine name</label>
                    <input id="medicine-search" type="search" name="search" placeholder="Search medicine…" defaultValue={filters.search} autoComplete="off" />
                    {filters.lowStock && <input type="hidden" name="low_stock" value="1" />}
                    <button type="submit" className="mc-btn sm">Search</button>
                </form>
                <a href={`${routes.index}?low_stock=1`} className="mc-btn sm ghost">Low stock only</a>
                {filters.lowStock && <a href={routes.index} className="mc-btn sm ghost">Clear</a>}
            </div>

            <section className="mc-card" aria-label="Pharmacy stock">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Medicine</th><th>Stock</th><th>Price</th><th>Expiry</th><th>Action</th></tr></thead>
                        <tbody>
                            {medicines.data.length ? medicines.data.map((medicine, index) => (
                                <tr key={medicine.id}>
                                    <td className="mc-idx">{(medicines.firstItem || 1) + index}</td>
                                    <td><b>{medicine.name}</b>{medicine.genericName && <span className="mc-sub2 block">{medicine.genericName}</span>}</td>
                                    <td className="mc-num">{medicine.stockQuantity} {medicine.unit}{medicine.isLowStock && <div><span className="mc-pill p-pending">Low stock</span></div>}</td>
                                    <td className="mc-num">{medicine.unitPrice}</td>
                                    <td className="mc-num">{medicine.expiryDate || '—'}{medicine.isExpired && <div><span className="mc-pill p-cancelled">Expired</span></div>}</td>
                                    <td>{canManage
                                        ? <div className="mc-acts">
                                            <a href={`${routes.editBase}/${medicine.id}/edit`} className="mc-btn sm dark">Edit</a>
                                            <button type="button" onClick={() => deleteMedicine(medicine)} className="mc-btn sm danger-ghost">Delete</button>
                                        </div>
                                        : <span className="text-mut">—</span>}</td>
                                </tr>
                            )) : <tr><td colSpan="6"><div className="mc-empty"><b>Empty shelf</b>No medicines match these stock filters.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={medicines} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    if (page.lastPage <= 1) return null;

    return (
        <div className="mc-pg">
            <span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span>
            <nav aria-label="Medicine pages" className="flex items-center gap-1">
                {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
                {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
            </nav>
        </div>
    );
}
