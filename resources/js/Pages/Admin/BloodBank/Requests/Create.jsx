import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function BloodRequestCreate({ patients, doctors, bloodGroups, routes }) {
    const { flash = {} } = usePage().props;
    const form = useForm({ patient_id: '', blood_group_id: '', quantity: '450', required_date: new Date().toISOString().slice(0, 10), urgency: 'normal', department: '', reason: '', doctor_id: '', notes: '' });
    function submit(event) { event.preventDefault(); form.post(routes.store); }

    return (
        <AdminLayout title="New blood request" active="blood-requests" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">New blood <em>request</em></h1><p className="mc-sub">Raise a need for a patient — approve and reserve from the request page.</p></div></div>
            {flash.error && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{flash.error}</div>}
            <form onSubmit={submit} className="space-y-4">
                {form.hasErrors && <div role="alert" className="rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Patient &amp; group</h2></div>
                    <div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                        <Select form={form} name="patient_id" label="Patient" required placeholder="Select patient…" options={patients.map((patient) => [patient.id, `${patient.name} — ${patient.email || ''}`])} />
                        <Select form={form} name="blood_group_id" label="Blood group" required placeholder="Select group…" options={bloodGroups.map((group) => [group.id, group.name])} />
                        <Field form={form} name="quantity" label="Quantity (ml)" type="number" min="1" required hint="450 ml ≈ one standard unit." />
                        <Field form={form} name="required_date" label="Required date" type="date" required />
                    </div>
                </section>
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Clinical context</h2></div>
                    <div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                        <Select form={form} name="doctor_id" label="Doctor" placeholder="No doctor assigned…" options={doctors.map((doctor) => [doctor.id, doctor.name])} />
                        <Field form={form} name="department" label="Department" placeholder="e.g. Surgery" />
                        <Field form={form} name="reason" label="Reason" textarea />
                        <Field form={form} name="notes" label="Notes" textarea />
                    </div>
                </section>
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Urgency</h2></div>
                    <div className="max-w-md p-4"><Select form={form} name="urgency" label="Urgency" required options={ [['normal', 'Normal'], ['urgent', 'Urgent'], ['emergency', 'Emergency']] } /></div>
                    <p className="px-4 pb-4 text-xs text-mut">New requests begin Pending. Stock is checked when an admin approves.</p>
                </section>
                <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : 'Save request'}</button><a href={routes.index} className="mc-btn ghost">Cancel</a></div>
            </form>
        </AdminLayout>
    );
}

function Field({ form, name, label, type = 'text', required = false, min, placeholder, hint, textarea = false }) {
    const id = `blood-request-${name}`;
    const props = { id, className: inputClass, value: form.data[name], onChange: (event) => form.setData(name, event.target.value), required, min, placeholder };
    return <div className={textarea ? 'md:col-span-2' : ''}><label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label>{textarea ? <textarea {...props} rows="3" /> : <input {...props} type={type} />}{hint && <p className="mc-hint">{hint}</p>}{form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}</div>;
}
function Select({ form, name, label, options, placeholder = null, required = false }) {
    const id = `blood-request-${name}`;
    return <div><label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label><select id={id} className={inputClass} value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)} required={required}>{placeholder !== null && <option value="">{placeholder}</option>}{options.map(([value, text]) => <option key={value} value={value}>{text}</option>)}</select>{form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}</div>;
}
