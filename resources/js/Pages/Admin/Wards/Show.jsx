import AdminLayout from '../../../Components/AdminLayout';

export default function WardShow({ ward, routes }) {
    return (
        <AdminLayout title={ward.name} active="beds" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · In-patient</p><h1 className="mc-title">{ward.name}</h1><p className="mc-sub">{ward.description || 'No description'}</p></div>
                <div className="mc-head-acts"><a href={routes.index} className="mc-btn ghost">Back to wards</a><a href={routes.edit} className="mc-btn"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a></div>
            </div>
            <section className="mc-card">
                <div className="border-b border-line px-4.5 py-3.5"><h2 className="m-0 text-[15px] font-bold">Rooms ({ward.rooms.length})</h2></div>
                <div className="overflow-x-auto"><table className="mc-tbl">
                    <thead><tr><th>Room</th><th>Type</th><th>Beds</th><th>Occupied</th><th>Action</th></tr></thead>
                    <tbody>{ward.rooms.length ? ward.rooms.map((room) => (
                        <tr key={room.id}><td><b>{room.roomNumber}</b></td><td>{room.roomType}</td><td className="mc-num">{room.bedsCount}</td><td className="mc-num">{room.occupiedCount}</td><td><a href={`${routes.roomShowBase}/${room.id}`} className="mc-btn sm">View</a></td></tr>
                    )) : <tr><td colSpan="5"><div className="mc-empty"><b>No rooms</b>Add rooms to this ward first.</div></td></tr>}</tbody>
                </table></div>
            </section>
        </AdminLayout>
    );
}
