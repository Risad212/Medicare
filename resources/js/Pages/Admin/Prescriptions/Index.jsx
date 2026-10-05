import AdminLayout from '../../../Components/AdminLayout';

export default function PrescriptionsIndex({ prescriptions, filters, routes }) {
    return (
        <AdminLayout title="Prescriptions" active="prescriptions" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Medical records</p>
                    <h1 className="mc-title">All <em>prescriptions</em></h1>
                    <p className="mc-sub">Prescriptions written across the clinical team.</p>
                </div>
            </div>
            <div className="mc-bar">
                <form action={routes.index} method="get" className="mc-search flex-1">
                    <i aria-hidden="true" className="bi bi-search text-faint" />
                    <label htmlFor="prescription-search" className="sr-only">Search patient, phone, or doctor</label>
                    <input id="prescription-search" type="search" name="search" placeholder="Patient, phone, or doctor…" defaultValue={filters.search} autoComplete="off" />
                    <button type="submit" className="mc-btn sm">Search</button>
                    {filters.search && <a href={routes.index} className="mc-btn sm ghost">Clear</a>}
                </form>
            </div>
            <section className="mc-card" aria-label="Prescriptions">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Follow-up</th><th>Action</th></tr></thead>
                        <tbody>
                            {prescriptions.data.length ? prescriptions.data.map((prescription) => (
                                <tr key={prescription.id}>
                                    <td className="mc-idx">{prescription.id}</td>
                                    <td className="mc-num">{prescription.createdAt || '—'}</td>
                                    <td><b>{prescription.patientName}</b>{prescription.phone && <span className="mc-sub2 block">{prescription.phone}</span>}</td>
                                    <td><b>Dr. {prescription.doctorName}</b>{prescription.doctorSpecialist && <span className="mc-sub2 block">{prescription.doctorSpecialist}</span>}</td>
                                    <td>{prescription.diagnosis.length > 45 ? `${prescription.diagnosis.slice(0, 45)}…` : prescription.diagnosis}</td>
                                    <td>{prescription.followUpDate ? <span className="mc-pill p-pending">{prescription.followUpDate}</span> : <span className="text-mut">—</span>}</td>
                                    <td><a href={`${routes.showBase}/${prescription.id}`} className="mc-btn sm"><i aria-hidden="true" className="bi bi-eye" /> View</a></td>
                                </tr>
                            )) : <tr><td colSpan="7"><div className="mc-empty"><b>No prescriptions found</b>Try adjusting your search.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={prescriptions} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    return (
        <div className="mc-pg">
            <span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span>
            <nav aria-label="Prescription pages" className="flex items-center gap-1">
                {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
                {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
            </nav>
        </div>
    );
}
