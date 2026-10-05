import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function VaccinationShow({ vaccination, routes }) {
    const { flash = {} } = usePage().props;
    return (
        <AdminLayout title={`Vaccination #${vaccination.id}`} active="vaccinations" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Immunization</p><h1 className="mc-title">Vaccination <em>#{vaccination.id}</em></h1><p className="mc-sub">{vaccination.vaccineName} · Dose {vaccination.doseNumber}</p></div>
                <div className="mc-head-acts"><a href={routes.index} className="mc-btn ghost">Back to list</a><a href={routes.edit} className="mc-btn">Edit</a><button type="button" className="mc-btn danger-ghost" onClick={() => { if (window.confirm('Delete this vaccination record?')) router.delete(routes.delete); }}>Delete</button></div>
            </div>
            {flash.success && <div role="status" className="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{flash.success}</div>}
            {vaccination.isOverdue && <div role="alert" className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"><b>Overdue:</b> this dose was due on {vaccination.nextDueDateLong} and is still scheduled.</div>}
            <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Record</h2></div>
                <dl className="divide-y divide-line-2">
                    <Detail label="Subject" value={vaccination.subjectName} />
                    <Detail label="Account" value={vaccination.userName} />
                    <Detail label="Register entry" value={vaccination.patientName} />
                    <Detail label="Vaccine" value={`${vaccination.vaccineName} (Dose ${vaccination.doseNumber})`} />
                    <Detail label="Status" value={vaccination.status} />
                    <Detail label="Date given" value={vaccination.dateGiven} />
                    <Detail label="Next due" value={vaccination.nextDueDate} />
                    <Detail label="Administered by" value={vaccination.administeredBy} />
                    <Detail label="Notes" value={vaccination.notes} />
                    <Detail label="Recorded by" value={vaccination.creatorName} />
                </dl>
            </section>
        </AdminLayout>
    );
}

function Detail({ label, value }) {
    return <div className="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-4"><dt className="text-xs font-bold uppercase tracking-wider text-mut">{label}</dt><dd className="m-0 whitespace-pre-wrap font-medium sm:col-span-3">{value || '—'}</dd></div>;
}
