import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function WardsIndex({ wards, routes }) {
    const { errors = {} } = usePage().props;
    function removeWard(ward) {
        if (window.confirm(`Delete ward ${ward.name}?`)) router.delete(`${routes.deleteBase}/${ward.id}`);
    }

    return (
        <AdminLayout title="Wards" active="beds" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · In-patient</p><h1 className="mc-title">Wa<em>rds</em></h1><p className="mc-sub">Hospital wards and their bed capacity.</p></div>
                <div className="mc-head-acts"><a href={routes.bedDashboard} className="mc-btn ghost">Bed dashboard</a><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add ward</a></div>
            </div>
            {Object.values(errors).length > 0 && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{Object.values(errors).join(' ')}</div>}
            <section className="mc-card">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Ward</th><th>Rooms</th><th>Beds</th><th>Action</th></tr></thead>
                        <tbody>
                            {wards.data.length ? wards.data.map((ward, index) => (
                                <tr key={ward.id}>
                                    <td className="mc-idx">{(wards.firstItem || 1) + index}</td>
                                    <td><b>{ward.name}</b>{ward.description && <span className="mc-sub2 block">{ward.description.length > 80 ? `${ward.description.slice(0, 77)}…` : ward.description}</span>}</td>
                                    <td className="mc-num">{ward.roomsCount}</td><td className="mc-num">{ward.bedsCount}</td>
                                    <td><div className="mc-acts"><a href={`${routes.showBase}/${ward.id}`} className="mc-btn sm">View</a><a href={`${routes.editBase}/${ward.id}/edit`} className="mc-btn sm dark">Edit</a><button type="button" className="mc-btn sm danger-ghost" onClick={() => removeWard(ward)}>Delete</button></div></td>
                                </tr>
                            )) : <tr><td colSpan="5"><div className="mc-empty"><b>No wards</b>Create the first ward to start tracking beds.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={wards} label="Ward pages" />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page, label }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label={label} className="flex items-center gap-1">
        {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
        <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
        {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
    </nav></div>;
}
