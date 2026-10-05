import AdminLayout from '../../../Components/AdminLayout';

export default function RoomShow({ room, routes }) {
    return (
        <AdminLayout title={`Room ${room.roomNumber}`} active="beds" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · In-patient</p><h1 className="mc-title">Room {room.roomNumber}</h1><p className="mc-sub">{room.wardName || 'No ward'} · {room.roomType}</p></div>
                <div className="mc-head-acts"><a href={routes.index} className="mc-btn ghost">Back to rooms</a><a href={routes.edit} className="mc-btn"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a></div>
            </div>
            <section className="mc-card">
                <div className="border-b border-line px-4.5 py-3.5"><h2 className="m-0 text-[15px] font-bold">Beds ({room.beds.length})</h2></div>
                <div className="overflow-x-auto"><table className="mc-tbl">
                    <thead><tr><th>Bed</th><th>Status</th><th>Patient</th><th>Admitted</th></tr></thead>
                    <tbody>{room.beds.length ? room.beds.map((bed) => (
                        <tr key={bed.id}><td><b>{bed.bedNumber}</b></td><td>{bed.statusLabel}</td><td>{bed.patientName || '—'}</td><td className="mc-num">{bed.admittedAt || '—'}</td></tr>
                    )) : <tr><td colSpan="4"><div className="mc-empty"><b>No beds</b>Add beds to this room.</div></td></tr>}</tbody>
                </table></div>
            </section>
        </AdminLayout>
    );
}
