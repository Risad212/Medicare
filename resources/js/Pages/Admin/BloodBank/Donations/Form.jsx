import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function BloodDonationForm({ mode, donation = null, defaults = {}, donors, bloodGroups, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        donor_id: donation?.donorId ?? '',
        blood_group_id: donation?.bloodGroupId ?? '',
        donation_date: donation?.donationDate ?? defaults.donationDate,
        quantity: String(donation?.quantity ?? defaults.quantity),
        bag_number: donation?.bagNumber ?? '',
        expiry_date: donation?.expiryDate ?? defaults.expiryDate,
        collection_location: donation?.collectionLocation ?? '',
        notes: donation?.notes ?? '',
    });

    function submit(event) {
        event.preventDefault();
        if (isEdit) form.put(routes.update);
        else form.post(routes.store);
    }

    return (
        <AdminLayout title={isEdit ? 'Edit donation' : 'Record donation'} active="blood-bank" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">{isEdit ? <>Edit <em>donation</em></> : <>Record <em>donation</em></>}</h1><p className="mc-sub">A new unit enters the pool: donor, group, quantity, and expiry.</p></div></div>
            <form onSubmit={submit} className="space-y-4">
                {form.hasErrors && <div role="alert" className="rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                {isEdit && <div className="rounded-lg bg-panel px-4 py-3 text-sm text-mut">Bag {donation.bagNumber || `#${donation.id}`} · Status: {donation.status}</div>}
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Donor &amp; group</h2></div>
                    <div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                        <Select form={form} name="donor_id" label="Donor" required placeholder="Select donor…" options={donors.map((donor) => [donor.id, `${donor.name} — ${donor.bloodGroup || 'No group'}`])} />
                        <Select form={form} name="blood_group_id" label="Blood group" required placeholder="Select group…" options={bloodGroups.map((group) => [group.id, group.name])} />
                    </div>
                </section>
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Unit details</h2></div>
                    <div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                        <Field form={form} name="donation_date" label="Donation date" type="date" required />
                        <Field form={form} name="quantity" label="Quantity (ml)" type="number" min="1" required />
                        <Field form={form} name="bag_number" label="Bag number" placeholder="e.g. B-00042" />
                        <Field form={form} name="expiry_date" label="Expiry date" type="date" required />
                        <Field form={form} name="collection_location" label="Collection location" />
                        <Field form={form} name="notes" label="Notes" textarea />
                    </div>
                    {!isEdit && <p className="px-4 pb-4 text-xs text-mut">New donations start as Collected. Mark them available only after testing.</p>}
                </section>
                <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update donation' : 'Save donation'}</button><a href={routes.index} className="mc-btn ghost">Cancel</a></div>
            </form>
        </AdminLayout>
    );
}

function Field({ form, name, label, type = 'text', required = false, min, placeholder, textarea = false }) {
    const id = `donation-${name}`;
    const props = {
        id,
        className: inputClass,
        value: form.data[name],
        onChange: (event) => form.setData(name, event.target.value),
        required,
        min,
        placeholder,
    };
    return <div className={textarea ? 'md:col-span-2' : ''}>
        <label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label>
        {textarea ? <textarea {...props} rows="3" /> : <input {...props} type={type} />}
        {form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}
    </div>;
}

function Select({ form, name, label, required, placeholder, options }) {
    const id = `donation-${name}`;
    return <div>
        <label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}<span aria-hidden="true"> *</span></label>
        <select id={id} className={inputClass} value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)} required={required}><option value="">{placeholder}</option>{options.map(([value, text]) => <option key={value} value={value}>{text}</option>)}</select>
        {form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}
    </div>;
}
