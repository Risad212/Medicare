import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Icon({ name }) {
    return <i aria-hidden="true" className={`bi bi-${name}`} />;
}

export default function PatientsIndex({ patients, filters, routes }) {
    const { csrfToken } = usePage().props;

    return (
        <AdminLayout title="Patients" active="patients" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Records</p>
                    <h1 className="mc-title">Pati<em>ents</em></h1>
                    <p className="mc-sub">Registered patients and how active their care is.</p>
                </div>
                <div className="mc-head-acts">
                    <a href={routes.create} className="mc-btn"><Icon name="plus-lg" /> Add patient</a>
                    <a href={routes.export} className="mc-btn ghost"><Icon name="download" /> Export CSV</a>
                </div>
            </div>

            <div className="mc-ecg"><span>Live register</span><span>{patients.total} records</span></div>

            <div className="mc-bar">
                <form action={routes.index} method="get" className="mc-search flex-1">
                    <Icon name="search" />
                    <label htmlFor="patient-search" className="sr-only">Search patients</label>
                    <input id="patient-search" type="search" name="search" placeholder="Search patient…" defaultValue={filters.search} autoComplete="off" />
                    <button type="submit" className="mc-btn sm">Search</button>
                </form>
            </div>

            <section className="mc-card" aria-label="Patient register">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead>
                            <tr><th>#</th><th>Patient</th><th>Phone</th><th>Gender</th><th>Date of birth</th><th>Registered</th><th className="text-right">Action</th></tr>
                        </thead>
                        <tbody>
                            {patients.data.length ? patients.data.map((patient, index) => (
                                <tr key={patient.id}>
                                    <td className="mc-idx">{(patients.firstItem || 1) + index}</td>
                                    <td>
                                        <div className="mc-who">
                                            <span className={`mc-av ${['t', 'a', 'b', 'r', ''][patient.id % 5]}`}>
                                                {(patient.name || '?').split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase()}
                                            </span>
                                            <span><b>{patient.name}</b><span className="mc-sub2">{patient.email || 'No email'}</span></span>
                                        </div>
                                    </td>
                                    <td className="mc-num">{patient.phone || 'N/A'}</td>
                                    <td>{patient.gender ? `${patient.gender[0].toUpperCase()}${patient.gender.slice(1)}` : 'N/A'}</td>
                                    <td className="mc-num">{patient.dateOfBirth || 'N/A'}</td>
                                    <td className="mc-num">{patient.registered || 'N/A'}</td>
                                    <td>
                                        <div className="mc-acts">
                                            <a href={`${routes.showBase}/${patient.id}`} className="mc-btn sm">View</a>
                                            <a href={`${routes.editBase}/${patient.id}/edit`} className="mc-btn sm dark">Edit</a>
                                            <form action={`${routes.deleteBase}/${patient.id}`} method="post" className="m-0"
                                                onSubmit={(event) => {
                                                    if (!window.confirm('Are you sure you want to delete this patient?')) event.preventDefault();
                                                }}>
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <input type="hidden" name="_method" value="DELETE" />
                                                <button type="submit" className="mc-btn sm danger-ghost">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            )) : (
                                <tr><td colSpan="7"><div className="mc-empty"><b>Nothing on this chart</b>No patients found.</div></td></tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="mc-pg">
                    <span>Showing {patients.firstItem || 0}–{patients.lastItem || 0} of {patients.total}</span>
                    <nav aria-label="Patient pages" className="flex items-center gap-1">
                        {patients.previousPageUrl
                            ? <a className="page-link" href={patients.previousPageUrl} aria-label="Previous page">‹</a>
                            : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                        <span className="px-2 text-[11px] text-mut">Page {patients.currentPage} of {patients.lastPage}</span>
                        {patients.nextPageUrl
                            ? <a className="page-link" href={patients.nextPageUrl} aria-label="Next page">›</a>
                            : <span className="page-link opacity-50" aria-disabled="true">›</span>}
                    </nav>
                </div>
            </section>
        </AdminLayout>
    );
}
