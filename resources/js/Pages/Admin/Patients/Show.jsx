import AdminLayout from '../../../Components/AdminLayout';

function Status({ status }) {
    const labels = {
        0: ['Pending', 'p-pending'],
        1: ['Approved', 'p-active'],
        2: ['Completed', 'p-completed'],
        3: ['Cancelled', 'p-cancelled'],
    };
    const [label, className] = labels[status] || ['Unknown', 'p-cancelled'];

    return <span className={`mc-pill ${className}`}><i />{label}</span>;
}

function Detail({ label, value, wide = false }) {
    return (
        <div className={wide ? 'col-span-2' : ''}>
            <span className="text-xs font-bold uppercase tracking-wide text-mut">{label}</span>
            <p className="mt-1 whitespace-pre-line text-ink">{value || 'N/A'}</p>
        </div>
    );
}

export default function PatientShow({ patient, visits, routes }) {
    return (
        <AdminLayout title="Patient details" active="patients" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Records</p>
                    <h1 className="mc-title">Patient <em>details</em></h1>
                    <p className="mc-sub">Profile overview and visits matched by email or phone.</p>
                </div>
                <div className="mc-head-acts">
                    <a href={routes.edit} className="mc-btn"><i aria-hidden="true" className="bi bi-pencil" /> Edit patient</a>
                    <a href={routes.index} className="mc-btn ghost">Back</a>
                </div>
            </div>

            <div className="grid gap-4 lg:grid-cols-2">
                <section className="mc-card p-5">
                    <h2 className="mb-4 text-[15px] font-bold">Personal information</h2>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Detail label="Name" value={patient.name} />
                        <Detail label="Email" value={patient.email} />
                        <Detail label="Phone" value={patient.phone} />
                        <Detail label="Date of birth" value={patient.dateOfBirth} />
                        <Detail label="Gender" value={patient.gender ? `${patient.gender[0].toUpperCase()}${patient.gender.slice(1)}` : null} />
                        <Detail label="Blood group" value={patient.bloodGroup} />
                        <Detail label="Address" value={patient.address} wide />
                    </div>
                </section>

                <section className="mc-card">
                    <div className="border-b border-line-2 px-4.5 py-3"><h2 className="text-[15px] font-bold">Appointment history</h2></div>
                    {visits.length ? (
                        <div className="overflow-x-auto">
                            <table className="mc-tbl">
                                <thead><tr><th>#</th><th>Doctor</th><th>Date</th><th>Time</th><th>Visit type</th><th>Status</th></tr></thead>
                                <tbody>{visits.map((visit, index) => (
                                    <tr key={visit.id}>
                                        <td className="mc-idx">{index + 1}</td><td>{visit.doctor}</td><td className="mc-num">{visit.date}</td><td className="mc-num">{visit.time}</td><td>{visit.visitType}</td><td><Status status={visit.status} /></td>
                                    </tr>
                                ))}</tbody>
                            </table>
                        </div>
                    ) : <div className="mc-empty"><b>Nothing on this chart</b>No appointments found for this patient.</div>}
                </section>
            </div>
        </AdminLayout>
    );
}
