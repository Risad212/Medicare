import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

function Field({ form, name, label, type = 'text', hint, ...attributes }) {
    const id = `medicine-${name}`;
    const error = form.errors[name];

    return (
        <div className="mc-f">
            <label htmlFor={id}>{label}</label>
            <input
                id={id}
                name={name}
                type={type}
                className={`${inputClass} ${error ? 'border-red-500' : ''}`}
                value={form.data[name] ?? ''}
                onChange={(event) => form.setData(name, event.target.value)}
                aria-invalid={Boolean(error)}
                aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined}
                {...attributes}
            />
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
            {!error && hint && <span id={`${id}-hint`} className="mc-hint">{hint}</span>}
        </div>
    );
}

export default function MedicineForm({ medicine = null, routes }) {
    const editing = Boolean(medicine);
    const form = useForm({
        name: medicine?.name ?? '',
        generic_name: medicine?.genericName ?? '',
        unit: medicine?.unit ?? 'tablet',
        stock_quantity: medicine?.stockQuantity ?? 0,
        unit_price: medicine?.unitPrice ?? 0,
        low_stock_threshold: medicine?.lowStockThreshold ?? 10,
        expiry_date: medicine?.expiryDate ?? '',
    });

    function submit(event) {
        event.preventDefault();
        if (editing) {
            form.put(routes.update);
        } else {
            form.post(routes.store);
        }
    }

    return (
        <AdminLayout title={editing ? 'Edit medicine' : 'Add medicine'} active="medicines" routes={routes} features={{ pharmacy: true }}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Pharmacy</p><h1 className="mc-title">{editing ? <>Edit <em>medicine</em></> : <>Add <em>medicine</em></>}</h1><p className="mc-sub">{editing ? medicine.name : 'Add a medicine and its current stock details.'}</p></div></div>
            {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the highlighted fields.</div>}
            <form onSubmit={submit}>
                <section className="mc-sec">
                    <div className="mc-sec-hd"><span className="no">01</span><h3>Medicine details</h3><p>Brand, generic name, and packaging.</p></div>
                    <div className="mc-sec-bd">
                        <Field form={form} name="name" label="Brand name" required maxLength="255" placeholder="e.g. Napa 500" />
                        <Field form={form} name="generic_name" label="Generic name" maxLength="255" placeholder="e.g. Paracetamol" />
                        <Field form={form} name="unit" label="Unit" required maxLength="30" placeholder="tablet, bottle, vial" />
                        <Field form={form} name="expiry_date" label="Expiry date" type="date" />
                    </div>
                </section>
                <section className="mc-sec">
                    <div className="mc-sec-hd"><span className="no">02</span><h3>Stock &amp; price</h3><p>Current quantity, unit price, and alert threshold.</p></div>
                    <div className="mc-sec-bd">
                        <Field form={form} name="stock_quantity" label="Stock quantity" type="number" min="0" max="1000000" required />
                        <Field form={form} name="unit_price" label="Unit price" type="number" min="0" max="1000000" step="0.01" required />
                        <Field form={form} name="low_stock_threshold" label="Low-stock alert at" type="number" min="0" max="1000000" required hint="Marked low at or below this quantity." />
                    </div>
                </section>
                <div className="mc-formacts">
                    <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : editing ? 'Save changes' : 'Add medicine'}</button>
                    <a href={routes.index} className="mc-btn ghost">Cancel</a>
                </div>
            </form>
        </AdminLayout>
    );
}
