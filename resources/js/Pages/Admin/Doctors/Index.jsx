import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Status({ status }) {
    return status === 1
        ? <span className="mc-pill p-active"><i />Active</span>
        : <span className="mc-pill p-inactive"><i />Inactive</span>;
}

export default function DoctorsIndex({ doctors, filters, routes, storageUrl }) {
    const { csrfToken } = usePage().props;

    return (
        <AdminLayout title="Doctors" active="doctors" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Staff</p><h1 className="mc-title">Doc<em>tors</em></h1><p className="mc-sub">Who is on the roster, where they sit, and whether they take bookings.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add doctor</a></div>
            </div>

            <div className="mc-ecg"><span>Live register</span><span>{doctors.total} records</span></div>
            <div className="mc-bar">
                <form action={routes.index} method="get" className="mc-search flex-1">
                    <i aria-hidden="true" className="bi bi-search" />
                    <label htmlFor="doctor-search" className="sr-only">Search doctors</label>
                    <input id="doctor-search" type="search" name="search" placeholder="Search doctor…" defaultValue={filters.search} autoComplete="off" />
                    <button type="submit" className="mc-btn sm">Search</button>
                </form>
            </div>

            <section className="mc-card" aria-label="Doctor register">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Doctor</th><th>Department</th><th>Specialist</th><th>Phone</th><th>Status</th><th className="text-right">Actions</th></tr></thead>
                        <tbody>
                            {doctors.data.length ? doctors.data.map((doctor, index) => (
                                <tr key={doctor.id}>
                                    <td className="mc-idx">{String((doctors.firstItem || 1) + index).padStart(2, '0')}</td>
                                    <td><div className="mc-who">
                                        {doctor.image
                                            ? <img className="mc-av object-cover" src={`${storageUrl}/${doctor.image.replace(/^\/+/, '')}`} alt={doctor.name} />
                                            : <span className={`mc-av ${['t', 'a', 'b', 'r', ''][doctor.id % 5]}`}>{doctor.name.split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase()}</span>}
                                        <span><b>{doctor.name}</b></span>
                                    </div></td>
                                    <td>{doctor.department || 'General'}</td><td>{doctor.specialist || '–'}</td><td className="mc-num">{doctor.phone || '–'}</td><td><Status status={doctor.status} /></td>
                                    <td><div className="mc-acts">
                                        <a href={`${routes.availabilityBase}/${doctor.id}/availability`} className="mc-btn sm"><i aria-hidden="true" className="bi bi-calendar2-week" /> Availability</a>
                                        <a href={`${routes.editBase}/${doctor.id}/edit`} className="mc-btn sm dark"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a>
                                        <form action={`${routes.deleteBase}/${doctor.id}`} method="post" className="m-0"
                                            onSubmit={(event) => { if (!window.confirm('Are you sure?')) event.preventDefault(); }}>
                                            <input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="DELETE" />
                                            <button type="submit" className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                                        </form>
                                    </div></td>
                                </tr>
                            )) : <tr><td colSpan="7"><div className="mc-empty"><b>Nothing on this chart</b>No doctors found. Add the first one to open bookings.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <div className="mc-pg">
                    <span>Showing {doctors.firstItem || 0}–{doctors.lastItem || 0} of {doctors.total}</span>
                    <nav aria-label="Doctor pages" className="flex items-center gap-1">
                        {doctors.previousPageUrl ? <a className="page-link" href={doctors.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                        <span className="px-2 text-[11px] text-mut">Page {doctors.currentPage} of {doctors.lastPage}</span>
                        {doctors.nextPageUrl ? <a className="page-link" href={doctors.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
                    </nav>
                </div>
            </section>
        </AdminLayout>
    );
}
