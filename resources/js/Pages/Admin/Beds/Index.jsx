import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '../../../Components/AdminLayout';

const bedColors = {
    0: 'border-green-200 bg-green-50 text-green-900',
    1: 'border-red-200 bg-red-50 text-red-900',
    2: 'border-slate-200 bg-slate-100 text-slate-700',
};

export default function BedsIndex({ wards, patients, routes }) {
    const { errors = {} } = usePage().props;
    const [selectedPatients, setSelectedPatients] = useState({});
    const errorMessages = Object.values(errors);

    function removeBed(bed) {
        if (window.confirm(`Delete bed ${bed.bedNumber}?`)) {
            router.delete(bed.routes.delete, { preserveScroll: true });
        }
    }

    return (
        <AdminLayout title="Bed dashboard" active="beds" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · In-patient</p><h1 className="mc-title">Bed <em>dashboard</em></h1><p className="mc-sub">Live occupancy per ward — green is free, red is taken, gray is maintenance.</p></div>
                <div className="mc-head-acts">
                    <a href={routes.wards} className="mc-btn ghost">Wards</a>
                    <a href={routes.rooms} className="mc-btn ghost">Rooms</a>
                    <a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add bed</a>
                </div>
            </div>
            {errorMessages.length > 0 && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{errorMessages.join(' ')}</div>}
            {wards.length ? wards.map((ward) => (
                <section key={ward.id} className="mc-card mb-4">
                    <div className="flex flex-wrap items-center justify-between gap-2 border-b border-line px-4.5 py-3.5">
                        <h2 className="m-0 text-base font-bold">{ward.name}</h2>
                        <span className="mc-pill p-progress">{ward.availableBedsCount}/{ward.bedsCount} available</span>
                    </div>
                    <div className="px-4.5 py-4">
                        {ward.rooms.length ? ward.rooms.map((room) => (
                            <div key={room.id} className="mb-4 last:mb-0">
                                <p className="mb-2 text-xs font-bold text-mut">Room {room.roomNumber} · {room.roomType}</p>
                                {room.beds.length ? (
                                    <div className="flex flex-wrap gap-2">
                                        {room.beds.map((bed) => (
                                            <div key={bed.id} className={`min-w-40 rounded-xl border px-3 py-2 ${bedColors[bed.status] || bedColors[2]}`}>
                                                <p className="m-0 font-bold">{bed.bedNumber}</p>
                                                <p className="m-0 text-xs">{bed.statusLabel}</p>
                                                {bed.status === 1 && <p className="m-0 text-xs font-semibold">{bed.patientName || '—'}</p>}
                                                {bed.status === 1 && <button type="button" className="mc-btn sm mt-2" onClick={() => {
                                                    if (window.confirm('Discharge this patient?')) router.post(bed.routes.discharge, {}, { preserveScroll: true });
                                                }}>Discharge</button>}
                                                {bed.status === 0 && (
                                                    <form className="mt-2 flex gap-1" onSubmit={(event) => {
                                                        event.preventDefault();
                                                        router.post(bed.routes.assign, { patient_user_id: selectedPatients[bed.id] }, { preserveScroll: true });
                                                    }}>
                                                        <label htmlFor={`patient-${bed.id}`} className="sr-only">Patient for bed {bed.bedNumber}</label>
                                                        <select id={`patient-${bed.id}`} className="min-w-0 rounded-md border border-line bg-white px-2 py-1 text-xs" value={selectedPatients[bed.id] || ''} onChange={(event) => setSelectedPatients({ ...selectedPatients, [bed.id]: event.target.value })} required>
                                                            <option value="">Patient…</option>
                                                            {patients.map((patient) => <option key={patient.id} value={patient.id}>{patient.name}</option>)}
                                                        </select>
                                                        <button type="submit" className="mc-btn sm" disabled={!patients.length}>Assign</button>
                                                    </form>
                                                )}
                                                <div className="mt-2 flex gap-2">
                                                    <a href={bed.routes.edit} className="text-xs font-semibold underline">Edit</a>
                                                    <button type="button" className="border-0 bg-transparent p-0 text-xs font-semibold text-red-700 underline" onClick={() => removeBed(bed)}>Delete</button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : <p className="m-0 text-xs text-mut">No beds in this room yet.</p>}
                            </div>
                        )) : <p className="m-0 text-xs text-mut">No rooms in this ward yet.</p>}
                    </div>
                </section>
            )) : <div className="mc-card"><div className="mc-empty"><b>No wards</b>Create wards, rooms and beds to see live availability here.</div></div>}
        </AdminLayout>
    );
}
