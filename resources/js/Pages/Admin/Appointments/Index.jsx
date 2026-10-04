import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Icon({ name }) {
    return <i aria-hidden="true" className={`bi bi-${name}`} />;
}

function Status({ status }) {
    const labels = {
        0: ['Pending', 'p-pending'],
        1: ['Confirmed', 'p-confirmed'],
        2: ['Completed', 'p-completed'],
        3: ['Cancelled', 'p-cancelled'],
    };
    const [label, className] = labels[status] || ['Unknown', 'p-cancelled'];

    return <span className={`mc-pill ${className}`}><i />{label}</span>;
}

export default function AppointmentsIndex({ appointments, filters, routes, features }) {
    const { csrfToken } = usePage().props;
    return (
        <AdminLayout
            title="Appointments"
            active="appointments"
            routes={routes}
            features={features}
        >
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Front desk</p>
                    <h1 className="mc-title">Appoint<em>ments</em></h1>
                    <p className="mc-sub">Every booking in one register. Pending needs a decision; the rest is history.</p>
                </div>
                <div className="mc-head-acts">
                    <a href={routes.create} className="mc-btn"><Icon name="plus-lg" /> Book appointment</a>
                    <a href={routes.export} className="mc-btn ghost"><Icon name="download" /> Export CSV</a>
                </div>
            </div>

            <div className="mc-ecg"><span>Live register</span><span>{appointments.total} records</span></div>

            <div className="mc-bar">
                <form action={routes.index} method="get" className="mc-search flex-1">
                    <Icon name="search" />
                    <label htmlFor="appointment-search" className="sr-only">Search appointments</label>
                    <input
                        id="appointment-search"
                        type="search"
                        name="search"
                        placeholder="Search patient, phone, doctor…"
                        defaultValue={filters.search}
                        autoComplete="off"
                    />
                    <button type="submit" className="mc-btn sm">Search</button>
                </form>
            </div>

            <section className="mc-card" aria-label="Appointment register">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Visit type</th>
                                <th>Date</th>
                                <th>Time slot</th>
                                <th>Status</th>
                                <th className="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {appointments.data.length ? appointments.data.map((appointment, index) => (
                                <tr key={appointment.id}>
                                    <td className="mc-idx">{(appointments.firstItem || 1) + index}</td>
                                    <td>
                                        <div className="mc-who">
                                            <span className={`mc-av ${['t', 'a', 'b', 'r', ''][appointment.id % 5]}`}>
                                                {(appointment.patientName || '?').split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase()}
                                            </span>
                                            <span><b>{appointment.patientName}</b></span>
                                        </div>
                                    </td>
                                    <td><b>{appointment.doctorName}</b></td>
                                    <td className="mc-num">{appointment.age ?? '–'}</td>
                                    <td>{appointment.gender}</td>
                                    <td className="mc-num">{appointment.phone}</td>
                                    <td>{appointment.email}</td>
                                    <td>{appointment.visitType}</td>
                                    <td className="mc-num">{appointment.date}</td>
                                    <td className="mc-num">{appointment.time}</td>
                                    <td><Status status={appointment.status} /></td>
                                    <td>
                                        <div className="mc-acts">
                                            <a href={`${routes.editBase}/${appointment.id}/edit`} className="mc-btn sm dark">Edit</a>
                                            <form action={`${routes.deleteBase}/${appointment.id}`} method="post" className="m-0"
                                                onSubmit={(event) => {
                                                    if (!window.confirm('Are you sure?')) event.preventDefault();
                                                }}>
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="DELETE" />
                                                <button type="submit" className="mc-btn sm danger-ghost">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            )) : (
                                <tr>
                                    <td colSpan="12"><div className="mc-empty"><b>Nothing on this chart</b>No appointments found. Book the first one to get the day moving.</div></td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="mc-pg">
                    <span>Showing {appointments.firstItem || 0}–{appointments.lastItem || 0} of {appointments.total}</span>
                    <nav aria-label="Appointment pages" className="flex items-center gap-1">
                        {appointments.previousPageUrl
                            ? <a className="page-link" href={appointments.previousPageUrl} aria-label="Previous page">‹</a>
                            : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                        <span className="px-2 text-[11px] text-mut">Page {appointments.currentPage} of {appointments.lastPage}</span>
                        {appointments.nextPageUrl
                            ? <a className="page-link" href={appointments.nextPageUrl} aria-label="Next page">›</a>
                            : <span className="page-link opacity-50" aria-disabled="true">›</span>}
                    </nav>
                </div>
            </section>
        </AdminLayout>
    );
}
