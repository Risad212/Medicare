import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function BloodDonorForm({ mode, donor = null, bloodGroups, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        name: donor?.name ?? '',
        blood_group_id: donor?.bloodGroupId ?? '',
        phone: donor?.phone ?? '',
        email: donor?.email ?? '',
        date_of_birth: donor?.dateOfBirth ?? '',
        gender: donor?.gender ?? '',
        address: donor?.address ?? '',
        last_donation_date: donor?.lastDonationDate ?? '',
        status: donor ? donor.status : true,
        notes: donor?.notes ?? '',
    });

    function submit(event) {
        event.preventDefault();
        const { status, ...data } = form.data;
        const payload = status ? { ...data, status: '1' } : data;
        if (isEdit) form.transform(() => payload).put(routes.update);
        else form.transform(() => payload).post(routes.store);
    }

    return (
        <AdminLayout title={`${isEdit ? 'Edit' : 'Register'} donor`} active="blood-donors" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">{isEdit ? <>Edit <em>donor</em></> : <>Register <em>donor</em></>}</h1><p className="mc-sub">Personal details, blood group and donation readiness.</p></div></div>
            <form onSubmit={submit} className="space-y-4">
                {form.hasErrors && <div role="alert" className="rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Identity</h2></div>
                    <div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                        <Field form={form} name="name" label="Full name" required />
                        <Select form={form} name="blood_group_id" label="Blood group" required options={bloodGroups.map((group) => [group.id, group.name])} placeholder="Select group…" />
                        <Field form={form} name="phone" label="Phone" required />
                        <Field form={form} name="email" label="Email" type="email" />
                    </div>
                </section>
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Details</h2></div>
                    <div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                        <Field form={form} name="date_of_birth" label="Date of birth" type="date" />
                        <Select form={form} name="gender" label="Gender" options={ [['male', 'Male'], ['female', 'Female'], ['other', 'Other']] } placeholder="Select…" />
                        <Field form={form} name="last_donation_date" label="Last donation date" type="date" hint="Used by the administrative eligibility check only." />
                        <div><label className="mc-check mt-7"><input type="checkbox" checked={form.data.status} onChange={(event) => form.setData('status', event.target.checked)} /><span>Active donor</span></label></div>
                        <Field form={form} name="address" label="Address" textarea />
                        <Field form={form} name="notes" label="Notes" textarea />
                    </div>
                </section>
                <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update donor' : 'Save donor'}</button><a href={isEdit ? routes.show : routes.index} className="mc-btn ghost">Cancel</a></div>
            </form>
        </AdminLayout>
    );
}

function Field({ form, name, label, type = 'text', required = false, hint = null, textarea = false }) {
    const id = `donor-${name}`;
    const common = {
        id,
        className: inputClass,
        value: form.data[name],
        onChange: (event) => form.setData(name, event.target.value),
        required,
        'aria-invalid': Boolean(form.errors[name]),
    };
    return <div className={textarea ? 'md:col-span-2' : ''}>
        <label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label>
        {textarea ? <textarea {...common} rows="3" /> : <input {...common} type={type} />}
        {hint && <p className="mc-hint">{hint}</p>}
        {form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}
    </div>;
}

function Select({ form, name, label, options, placeholder, required = false }) {
    const id = `donor-${name}`;
    return <div>
        <label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label>
        <select id={id} className={inputClass} value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)} required={required}>
            <option value="">{placeholder}</option>{options.map(([value, text]) => <option key={value} value={value}>{text}</option>)}
        </select>
        {form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}
    </div>;
}
