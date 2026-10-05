import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-teal focus:ring-2 focus:ring-teal/15';

function Field({ form, name, label, type = 'text', children, hint, full = false, ...props }) {
    const id = `service-${name}`;
    const error = form.errors[name];

    return (
        <div className={`mc-f ${full ? 'full' : ''}`}>
            <label htmlFor={id}>{label}</label>
            {children || <input id={id} className={`${inputClass} ${error ? 'border-red-500' : ''}`} name={name} type={type}
                value={type === 'file' ? undefined : form.data[name] ?? ''} onChange={(event) => form.setData(name, type === 'file' ? event.target.files[0] : event.target.value)}
                aria-invalid={Boolean(error)} aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined} {...props} />}
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
            {!error && hint && <span id={`${id}-hint`} className="mc-hint">{hint}</span>}
        </div>
    );
}

export default function ServiceForm({ mode, service = null, routes, storageUrl }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        title: service?.title ?? '',
        description: service?.description ?? '',
        icon: null,
        button_text: service?.buttonText ?? 'Read more',
        button_url: service?.buttonUrl ?? '',
        order: service?.order ?? 0,
        status: service ? service.status : true,
    });
    const iconUrl = service?.icon ? `${storageUrl}/${service.icon.replace(/^\/+/, '')}` : null;

    function submit(event) {
        event.preventDefault();
        form.transform(({ status, ...data }) => ({
            ...(status ? { ...data, status: '1' } : data),
            ...(isEdit ? { _method: 'PUT' } : {}),
        }));
        if (isEdit) {
            form.post(routes.update);
        } else {
            form.post(routes.store);
        }
    }

    return (
        <AdminLayout title={isEdit ? 'Edit service' : 'Add service'} active="services" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Content</p><h1 className="mc-title">{isEdit ? <>Edit serv<em>ice</em></> : <>New serv<em>ice</em></>}</h1>
                <p className="mc-sub">{isEdit ? service.title : 'A card for the home and services pages.'}</p></div></div>
            <form onSubmit={submit} encType="multipart/form-data" noValidate>
                <div className="mc-grid">
                    <aside className="mc-side">
                        {iconUrl ? <img src={iconUrl} alt="" className="mc-avbig bg-white object-contain p-2" /> : <div className="mc-avbig">SV</div>}
                        <div className="k">{isEdit ? 'Editing' : 'New record'}</div><h2>{service?.title || 'Unsaved service'}</h2>
                        <p>Shown as a card on the home and services pages.</p>
                        <ul className="mc-steps"><li><span className="n">1</span>Profile</li><li><span className="n">2</span>Media</li><li><span className="n">3</span>Link</li><li><span className="n">4</span>Visibility</li></ul>
                    </aside>
                    <div>
                        {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the highlighted fields and try again.</div>}
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">01</span><h3>Profile</h3><p>Title and description.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="title" label="Title" full required placeholder="e.g. Heart transplants" />
                                <Field form={form} name="description" label="Description" full hint="Up to 2,000 characters.">
                                    <textarea id="service-description" className={inputClass} rows="6" value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} />
                                </Field>
                            </div>
                        </section>
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">02</span><h3>Media</h3><p>Optional icon image.</p></div>
                            <div className="mc-sec-bd"><Field form={form} name="icon" label="Icon image" type="file" full accept="image/png,image/jpeg,image/webp,image/svg+xml" hint="PNG, JPG, WEBP or SVG, up to 2 MB." /></div>
                        </section>
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">03</span><h3>Link</h3><p>Call-to-action button on the card.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="button_text" label="Button text" placeholder="Read more" />
                                <Field form={form} name="button_url" label="Button URL" placeholder="https://… or /contact" />
                            </div>
                        </section>
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">04</span><h3>Visibility</h3><p>Ordering and whether it appears on the site.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="order" label="Order" type="number" min="0" />
                                <div className="mc-f"><span className="mb-2 block text-sm font-semibold">Status</span><label className="mc-check"><input type="checkbox" checked={form.data.status} onChange={(event) => form.setData('status', event.target.checked)} /> Active</label></div>
                            </div>
                        </section>
                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update service' : 'Save service'}</button>
                            <a href={routes.index} className="mc-btn ghost">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
