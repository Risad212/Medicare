import { router, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Info({ label, value }) {
    return (
        <tr className="border-b border-line-2 last:border-b-0">
            <th className="w-1/4 whitespace-nowrap px-4 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">{label}</th>
            <td className="px-4 py-2.5">{value || 'N/A'}</td>
        </tr>
    );
}

function DispenseForm({ item, medicines, action }) {
    const form = useForm({ medicine_id: '', quantity: 1 });

    function submit(event) {
        event.preventDefault();
        form.post(action, { preserveScroll: true });
    }

    return (
        <form onSubmit={submit} className="flex min-w-[260px] flex-wrap items-center gap-1.5">
            <label htmlFor={`medicine-${item.id}`} className="sr-only">Select stock medicine for {item.medicineName}</label>
            <select id={`medicine-${item.id}`} value={form.data.medicine_id} onChange={(event) => form.setData('medicine_id', event.target.value)} className="min-w-[140px] rounded border border-line bg-white px-2 py-1.5 text-xs" required>
                <option value="">Stock…</option>
                {medicines.map((medicine) => <option key={medicine.id} value={medicine.id}>{medicine.name} ({medicine.stockQuantity})</option>)}
            </select>
            <label htmlFor={`quantity-${item.id}`} className="sr-only">Quantity to dispense</label>
            <input id={`quantity-${item.id}`} type="number" min="1" value={form.data.quantity} onChange={(event) => form.setData('quantity', event.target.value)} className="w-[72px] rounded border border-line px-2 py-1.5 text-xs" required />
            <button type="submit" className="mc-btn sm" disabled={form.processing}>{form.processing ? '…' : 'Dispense'}</button>
            {Object.values(form.errors).map((message) => <span key={message} role="alert" className="w-full text-xs text-red-t">{message}</span>)}
        </form>
    );
}

export default function PrescriptionShow({ prescription, medicines, features, routes }) {
    function deletePrescription() {
        if (window.confirm('Delete this prescription? This cannot be undone.')) {
            router.delete(routes.delete);
        }
    }

    return (
        <AdminLayout title={`Prescription #${prescription.id}`} active="prescriptions" routes={routes} features={features}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Medical records</p>
                    <h1 className="mc-title">Prescription <em>#{prescription.id}</em></h1>
                    <p className="mc-sub">Patient, diagnosis, and prescribed medicines.</p>
                </div>
                <div className="mc-head-acts">
                    <a href={routes.index} className="mc-btn ghost">Back to list</a>
                    <a href={routes.pdf} className="mc-btn ghost" target="_blank" rel="noreferrer"><i aria-hidden="true" className="bi bi-file-earmark-pdf" /> PDF</a>
                    <button type="button" onClick={deletePrescription} className="mc-btn ghost danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                </div>
            </div>

            <div className="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3.5"><h2 className="m-0 text-[15px] font-bold">Patient information</h2></div>
                    <table className="w-full text-sm"><tbody>
                        <Info label="Name" value={prescription.patientName} />
                        <Info label="Age / gender" value={`${prescription.age ? `${prescription.age} yrs` : 'N/A'} / ${prescription.genderLabel}`} />
                        <Info label="Phone" value={prescription.phone} />
                        <Info label="Email" value={prescription.email} />
                        <Info label="Account" value={prescription.patientAccount} />
                    </tbody></table>
                </section>
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3.5"><h2 className="m-0 text-[15px] font-bold">Visit information</h2></div>
                    <table className="w-full text-sm"><tbody>
                        <Info label="Prescribed" value={prescription.createdAt} />
                        <Info label="Doctor" value={`Dr. ${prescription.doctorName} (${prescription.doctorSpecialist})`} />
                        <Info label="Appointment" value={prescription.appointmentId
                            ? `#${prescription.appointmentId} — ${prescription.appointmentDate || ''}${prescription.appointmentTime ? ` · ${prescription.appointmentTime}` : ''}`
                            : 'Walk-in'} />
                    </tbody></table>
                </section>
            </div>

            <section className="mc-card mb-4">
                <div className="border-b border-line px-4 py-3.5"><h2 className="m-0 text-[15px] font-bold">Clinical notes</h2></div>
                <div className="grid grid-cols-1 gap-3 px-4 py-4 md:grid-cols-2">
                    <div><h3 className="text-sm font-bold">Symptoms</h3><p className="text-mut">{prescription.symptoms || 'Not recorded'}</p></div>
                    <div><h3 className="text-sm font-bold">Diagnosis</h3><p>{prescription.diagnosis}</p></div>
                    {prescription.advice && <div className="md:col-span-2"><h3 className="text-sm font-bold">Advice / notes</h3><p className="text-mut">{prescription.advice}</p></div>}
                </div>
            </section>

            <section className="mc-card">
                <div className="border-b border-line px-4 py-3.5"><h2 className="m-0 text-[15px] font-bold">Prescribed medicines ({prescription.items.length})</h2></div>
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Quantity</th><th>Instructions</th>{features.pharmacy && <th>Dispensing</th>}</tr></thead>
                        <tbody>
                            {prescription.items.length ? prescription.items.map((item, index) => (
                                <tr key={item.id}>
                                    <td><b>{index + 1}. {item.medicineName}</b></td>
                                    <td>{item.dosage || '—'}</td>
                                    <td>{item.frequency || '—'}</td>
                                    <td>{item.duration || '—'}</td>
                                    <td>{item.quantity || '—'}</td>
                                    <td className="text-mut">{item.instructions || '—'}</td>
                                    {features.pharmacy && (
                                        <td>
                                            {item.isDispensed
                                                ? <><span className="mc-pill p-confirmed">Dispensed</span><div className="mc-sub2">{item.dispensedQuantity} × {item.medicineNameDispensed || item.medicineName} · {item.dispenserName || '—'}</div></>
                                                : routes.dispenseBase && <DispenseForm item={item} medicines={medicines} action={`${routes.dispenseBase}/${item.id}/dispense`} />}
                                        </td>
                                    )}
                                </tr>
                            )) : <tr><td colSpan={features.pharmacy ? 7 : 6}><div className="mc-empty"><b>No medicines</b>This prescription has no medicine rows.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
