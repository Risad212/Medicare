import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function VaccinationForm({ mode, vaccination = null, users, patients, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        user_id: vaccination?.userId ?? '',
        patient_id: vaccination?.patientId ?? '',
        child_name: vaccination?.childName ?? '',
        vaccine_name: vaccination?.vaccineName ?? '',
        dose_number: String(vaccination?.doseNumber ?? 1),
        date_given: vaccination?.dateGiven ?? '',
        next_due_date: vaccination?.nextDueDate ?? '',
        administered_by: vaccination?.administeredBy ?? '',
        notes: vaccination?.notes ?? '',
        status: String(vaccination?.status ?? 0),
    });

    function submit(event) {
        event.preventDefault();
        if (isEdit) form.put(routes.update);
        else form.post(routes.store);
    }

    return (
        <AdminLayout title={isEdit ? 'Edit vaccination' : 'Add vaccination'} active="vaccinations" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Immunization</p><h1 className="mc-title">{isEdit ? <>Edit vaccination <em>record</em></> : <>New vaccination <em>record</em></>}</h1><p className="mc-sub">Record the patient, vaccine dose, and schedule.</p></div></div>
            <form onSubmit={submit} className="space-y-4">
                {form.hasErrors && <div role="alert" className="rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Patient</h2></div><div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                    <Select form={form} name="user_id" label="Patient account (login user)" options={users.map((user) => [user.id, `${user.name} (#${user.id})`])} placeholder="Select account…" hint={'Pick the account holder. For a child, pick the parent account and fill "Child name".'} />
                    <Select form={form} name="patient_id" label="Clinic register patient" options={patients.map((patient) => [patient.id, `${patient.name} (#${patient.id})`])} placeholder="Select register entry…" hint="Use for children without a login account." />
                    <Field form={form} name="child_name" label="Child name (optional)" placeholder="e.g. Aarav Rahman" />
                </div></section>
                <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Vaccine details</h2></div><div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                    <Field form={form} name="vaccine_name" label="Vaccine name" required placeholder="e.g. BCG, Pentavalent" />
                    <Field form={form} name="dose_number" label="Dose number" required type="number" min="1" max="10" />
                    <Select form={form} name="status" label="Status" required options={ [['0', 'Scheduled'], ['1', 'Completed'], ['2', 'Missed']] } />
                    <Field form={form} name="administered_by" label="Administered by" placeholder="Doctor / clinic name" />
                    <Field form={form} name="date_given" label="Date given" type="date" hint="Required when status is Completed." />
                    <Field form={form} name="next_due_date" label="Next due date" type="date" />
                    <Field form={form} name="notes" label="Notes" textarea placeholder="Batch no, reactions, reminders…" />
                </div></section>
                <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Save changes' : 'Save record'}</button><a href={isEdit ? routes.show : routes.index} className="mc-btn ghost">Cancel</a></div>
            </form>
        </AdminLayout>
    );
}

function Field({ form, name, label, type = 'text', required = false, min, max, placeholder, hint, textarea = false }) {
    const id = `vaccination-${name}`;
    const props = { id, className: inputClass, value: form.data[name], onChange: (event) => form.setData(name, event.target.value), required, min, max, placeholder, 'aria-invalid': Boolean(form.errors[name]) };
    return <div className={textarea ? 'md:col-span-2' : ''}><label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label>{textarea ? <textarea {...props} rows="3" /> : <input {...props} type={type} />}{hint && <p className="mc-hint">{hint}</p>}{form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}</div>;
}
function Select({ form, name, label, options, placeholder = null, hint = null, required = false }) {
    const id = `vaccination-${name}`;
    return <div><label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label><select id={id} className={inputClass} value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)} required={required}>{placeholder !== null && <option value="">{placeholder}</option>}{options.map(([value, text]) => <option key={value} value={value}>{text}</option>)}</select>{hint && <p className="mc-hint">{hint}</p>}{form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}</div>;
}
