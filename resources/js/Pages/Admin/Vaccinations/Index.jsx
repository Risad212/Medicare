import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const statusClass = { Completed: 'p-confirmed', Missed: 'p-cancelled', Overdue: 'p-pending', Scheduled: 'p-progress' };

export default function VaccinationsIndex({ vaccinations, filters, routes }) {
    const { flash = {} } = usePage().props;

    return (
        <AdminLayout title="Vaccinations" active="vaccinations" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Immunization</p><h1 className="mc-title">Vacci<em>nations</em></h1><p className="mc-sub">Track given doses and upcoming schedules.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add record</a></div>
            </div>
            {flash.success && <div role="status" className="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{flash.success}</div>}
            <div className="mc-ecg"><span>Immunization register</span><span>{vaccinations.total} records</span></div>
            <form action={routes.index} method="get" className="mc-bar">
                <div className="mc-search flex-1"><label htmlFor="vaccination-search" className="sr-only">Search vaccine, child or patient</label><input id="vaccination-search" type="search" name="search" defaultValue={filters.search} placeholder="Search vaccine, child, patient…" /></div>
                <button type="submit" className="mc-btn sm">Search</button>
                {filters.search && <a href={routes.index} className="mc-btn sm ghost">Clear</a>}
            </form>
            <section className="mc-card"><div className="overflow-x-auto"><table className="mc-tbl">
                <thead><tr><th>#</th><th>Subject</th><th>Vaccine</th><th>Dose</th><th>Status</th><th>Next due</th><th>Action</th></tr></thead>
                <tbody>{vaccinations.data.length ? vaccinations.data.map((vaccination, index) => {
                    const label = vaccination.isOverdue ? 'Overdue' : vaccination.statusLabel;
                    return <tr key={vaccination.id}><td className="mc-idx">{(vaccinations.firstItem || 1) + index}</td><td><b>{vaccination.subjectName}</b></td><td>{vaccination.vaccineName}</td><td className="mc-num">{vaccination.doseNumber}</td><td><span className={`mc-pill ${statusClass[label] || ''}`}>{label}</span></td><td>{vaccination.nextDueDate || '—'}</td>
                        <td><div className="mc-acts"><a href={`${routes.showBase}/${vaccination.id}`} className="mc-btn sm">View</a><a href={`${routes.editBase}/${vaccination.id}/edit`} className="mc-btn sm dark">Edit</a><button type="button" className="mc-btn sm danger-ghost" onClick={() => { if (window.confirm('Delete this vaccination record?')) router.delete(`${routes.deleteBase}/${vaccination.id}`); }}>Delete</button></div></td></tr>;
                }) : <tr><td colSpan="7"><div className="mc-empty"><b>No records</b>No vaccination records found.</div></td></tr>}</tbody>
            </table></div><Pagination page={vaccinations} /></section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label="Vaccination pages" className="flex items-center gap-1">{page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl}>‹</a> : <span className="page-link opacity-50">‹</span>}<span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>{page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl}>›</a> : <span className="page-link opacity-50">›</span>}</nav></div>;
}
