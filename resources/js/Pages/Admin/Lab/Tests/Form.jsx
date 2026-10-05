import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

function Field({ form, name, label, type = 'text', full = false, children, ...attributes }) {
    const id = `lab-test-${name}`;
    const error = form.errors[name];

    return (
        <div className={`mc-f ${full ? 'full' : ''}`}>
            <label htmlFor={id}>{label}</label>
            {children || <input
                id={id}
                name={name}
                type={type}
                className={`${inputClass} ${error ? 'border-red-500' : ''}`}
                value={type === 'file' ? undefined : form.data[name] ?? ''}
                onChange={(event) => form.setData(name, event.target.value)}
                aria-invalid={Boolean(error)}
                aria-describedby={error ? `${id}-error` : undefined}
                {...attributes}
            />}
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
        </div>
    );
}

export default function LabTestForm({ mode, labTest = null, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        name: labTest?.name ?? '',
        category: labTest?.category ?? '',
        description: labTest?.description ?? '',
        price: labTest?.price ?? '',
        normal_range: labTest?.normalRange ?? '',
        unit: labTest?.unit ?? '',
        status: labTest?.status ? '1' : '0',
    });

    function submit(event) {
        event.preventDefault();
        if (isEdit) {
            form.put(routes.update);
        } else {
            form.post(routes.store);
        }
    }

    return (
        <AdminLayout title={isEdit ? 'Edit lab test' : 'New lab test'} active="lab-tests" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Laboratory</p><h1 className="mc-title">{isEdit ? <>Edit lab <em>test</em></> : <>New lab <em>test</em></>}</h1><p className="mc-sub">Test details, pricing, and reference ranges.</p></div></div>
            {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
            <form onSubmit={submit}>
                <section className="mc-sec">
                    <div className="mc-sec-hd"><span className="no">01</span><h3>Test details</h3><p>Clinical name and description.</p></div>
                    <div className="mc-sec-bd">
                        <Field form={form} name="name" label="Test name" required placeholder="e.g. Complete Blood Count (CBC)" />
                        <Field form={form} name="category" label="Category" placeholder="e.g. Hematology" />
                        <Field form={form} name="description" label="Description" full>
                            <textarea id="lab-test-description" className={inputClass} rows="4" value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} aria-invalid={Boolean(form.errors.description)} />
                        </Field>
                    </div>
                </section>
                <section className="mc-sec">
                    <div className="mc-sec-hd"><span className="no">02</span><h3>Reference & price</h3><p>Patient-facing cost and clinical ranges.</p></div>
                    <div className="mc-sec-bd">
                        <Field form={form} name="price" label="Price" type="number" min="0" step="0.01" required placeholder="450.00" />
                        <Field form={form} name="normal_range" label="Normal range" placeholder="13.0 - 17.0" />
                        <Field form={form} name="unit" label="Unit" placeholder="g/dL, mg/dL" />
                        <Field form={form} name="status" label="Status">
                            <select id="lab-test-status" className={inputClass} value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} aria-invalid={Boolean(form.errors.status)}>
                                <option value="1">Active</option><option value="0">Inactive</option>
                            </select>
                        </Field>
                    </div>
                </section>
                <div className="mc-formacts">
                    <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update lab test' : 'Save lab test'}</button>
                    <a href={routes.index} className="mc-btn ghost">Back</a>
                </div>
            </form>
        </AdminLayout>
    );
}
