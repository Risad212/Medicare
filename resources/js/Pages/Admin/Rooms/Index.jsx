import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function RoomsIndex({ rooms, routes }) {
    const { errors = {} } = usePage().props;
    function removeRoom(room) {
        if (window.confirm(`Delete room ${room.roomNumber}?`)) router.delete(`${routes.deleteBase}/${room.id}`);
    }

    return (
        <AdminLayout title="Rooms" active="beds" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · In-patient</p><h1 className="mc-title">Ro<em>oms</em></h1><p className="mc-sub">Rooms within each ward.</p></div>
                <div className="mc-head-acts"><a href={routes.bedDashboard} className="mc-btn ghost">Bed dashboard</a><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add room</a></div>
            </div>
            {Object.values(errors).length > 0 && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{Object.values(errors).join(' ')}</div>}
            <section className="mc-card">
                <div className="overflow-x-auto"><table className="mc-tbl">
                    <thead><tr><th>#</th><th>Room</th><th>Ward</th><th>Type</th><th>Beds</th><th>Action</th></tr></thead>
                    <tbody>{rooms.data.length ? rooms.data.map((room, index) => (
                        <tr key={room.id}>
                            <td className="mc-idx">{(rooms.firstItem || 1) + index}</td><td><b>{room.roomNumber}</b></td><td>{room.wardName || '—'}</td><td>{room.roomType}</td><td className="mc-num">{room.bedsCount}</td>
                            <td><div className="mc-acts"><a href={`${routes.showBase}/${room.id}`} className="mc-btn sm">View</a><a href={`${routes.editBase}/${room.id}/edit`} className="mc-btn sm dark">Edit</a><button type="button" className="mc-btn sm danger-ghost" onClick={() => removeRoom(room)}>Delete</button></div></td>
                        </tr>
                    )) : <tr><td colSpan="6"><div className="mc-empty"><b>No rooms</b>Create the first room.</div></td></tr>}</tbody>
                </table></div>
                <Pagination page={rooms} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label="Room pages" className="flex items-center gap-1">
        {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
        <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
        {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
    </nav></div>;
}
