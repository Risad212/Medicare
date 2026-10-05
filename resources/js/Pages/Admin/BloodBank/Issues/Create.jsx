import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function BloodIssueCreate({ bloodRequest, reservedBags, defaults, routes }) {
    const { flash = {} } = usePage().props;
    const form = useForm({ donation_id: '', issue_date: defaults.issueDate, receiver_name: bloodRequest.patientName, receiver_phone: '', notes: '' });
    function submit(event) { event.preventDefault(); form.post(routes.store); }

    return (
        <AdminLayout title={`Issue blood — #${bloodRequest.id}`} active="blood-issues" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Issue blood — <em>#{bloodRequest.id}</em></h1><p className="mc-sub">Choose a reserved bag and record the handover details.</p></div></div>
            {flash.error && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{flash.error}</div>}
            {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
            <div className="mc-bar"><span className="text-mut">Patient: <b>{bloodRequest.patientName}</b> · Group: <b>{bloodRequest.group}</b> · Required: {bloodRequest.quantity} ml · Issued: {bloodRequest.issuedQuantity} ml · Remaining: {Math.max(0, bloodRequest.quantity - bloodRequest.issuedQuantity)} ml</span></div>
            <form onSubmit={submit} className="space-y-4">
                <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Reserved unit</h2></div><div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                    <div className="md:col-span-2"><label htmlFor="issue-donation" className="mb-1.5 block text-xs font-bold text-ink-2">Bag <span aria-hidden="true">*</span></label><select id="issue-donation" className={inputClass} value={form.data.donation_id} onChange={(event) => form.setData('donation_id', event.target.value)} required><option value="">Select bag…</option>{reservedBags.map((bag) => <option key={bag.id} value={bag.id}>{bag.bagNumber} — {bag.quantity} ml — expires {bag.expiryDate}</option>)}</select>{form.errors.donation_id && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.donation_id}</p>}</div>
                    <Field form={form} name="issue_date" label="Issue date" type="date" required />
                </div></section>
                <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Receiver</h2></div><div className="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                    <Field form={form} name="receiver_name" label="Receiver name" />
                    <Field form={form} name="receiver_phone" label="Receiver phone" />
                    <Field form={form} name="notes" label="Notes" textarea />
                </div></section>
                <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Issuing…' : 'Issue blood'}</button><a href={routes.cancel} className="mc-btn ghost">Cancel</a></div>
            </form>
        </AdminLayout>
    );
}

function Field({ form, name, label, type = 'text', required = false, textarea = false }) {
    const id = `issue-${name}`;
    const props = { id, className: inputClass, value: form.data[name], onChange: (event) => form.setData(name, event.target.value), required };
    return <div className={textarea ? 'md:col-span-2' : ''}><label htmlFor={id} className="mb-1.5 block text-xs font-bold text-ink-2">{label}{required && <span aria-hidden="true"> *</span>}</label>{textarea ? <textarea {...props} rows="3" /> : <input {...props} type={type} />}{form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors[name]}</p>}</div>;
}
