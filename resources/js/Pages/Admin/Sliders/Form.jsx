import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-teal focus:ring-2 focus:ring-teal/15';

function Field({ form, name, label, type = 'text', children, hint, ...props }) {
    const id = `slider-${name}`;
    const error = form.errors[name];

    return (
        <div className="mc-f full">
            <label htmlFor={id}>{label}</label>
            {children || <input id={id} className={`${inputClass} ${error ? 'border-red-500' : ''}`} type={type}
                value={type === 'file' ? undefined : form.data[name] ?? ''} onChange={(event) => form.setData(name, type === 'file' ? event.target.files[0] : event.target.value)}
                aria-invalid={Boolean(error)} aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined} {...props} />}
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
            {!error && hint && <span id={`${id}-hint`} className="mc-hint">{hint}</span>}
        </div>
    );
}

export default function SliderForm({ mode, slider = null, routes, storageUrl }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        title: slider?.title ?? '',
        description: slider?.description ?? '',
        button_text: slider?.buttonText ?? '',
        bg_image: null,
    });
    const imageUrl = slider?.backgroundImage ? `${storageUrl}/${slider.backgroundImage.replace(/^\/+/, '')}` : null;

    function submit(event) {
        event.preventDefault();
        form.transform((data) => ({ ...data, ...(isEdit ? { _method: 'PUT' } : {}) }))
            .post(isEdit ? routes.update : routes.store);
    }

    return (
        <AdminLayout title={isEdit ? 'Edit slide' : 'New slide'} active="sliders" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Frontend</p><h1 className="mc-title">{isEdit ? <>Edit <em>slide</em></> : <>New <em>slide</em></>}</h1>
                <p className="mc-sub">{isEdit ? 'Update copy, swap the image, or change the CTA.' : 'A homepage hero banner with image and CTA.'}</p></div></div>
            <form onSubmit={submit} encType="multipart/form-data" noValidate>
                <div className="mc-grid">
                    <aside className="mc-side">{imageUrl ? <div className="mc-avbig"><img src={imageUrl} alt="" className="h-full w-full rounded-full object-cover" /></div> : <div className="mc-avbig">SL</div>}
                        <div className="k">{isEdit ? 'Currently editing' : 'New record'}</div><h2>{slider?.title || 'Unsaved slide'}</h2>
                        <p>{isEdit ? (slider.buttonText ? `CTA: ${slider.buttonText}` : 'No CTA set') : 'Upload an image, add copy, set the button text.'}</p>
                        <ul className="mc-steps"><li><span className="n">1</span>Content</li><li><span className="n">2</span>Image</li></ul>
                    </aside>
                    <div>
                        {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the highlighted fields and try again.</div>}
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">01</span><h3>Content</h3><p>Text and button label.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="title" label="Title" required placeholder="e.g. Trusted Healthcare" />
                                <Field form={form} name="description" label="Description">
                                    <textarea id="slider-description" className={`${inputClass} ${form.errors.description ? 'border-red-500' : ''}`} rows="3" value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} aria-invalid={Boolean(form.errors.description)} />
                                </Field>
                                <Field form={form} name="button_text" label="Button text" placeholder="e.g. Learn more" />
                            </div>
                        </section>
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">02</span><h3>Image</h3><p>Background image for the slide.</p></div>
                            <div className="mc-sec-bd">
                                {imageUrl && <div className="mc-f full"><img src={imageUrl} className="mc-imgprev mb-2" alt="" /></div>}
                                <Field form={form} name="bg_image" label="Background image" type="file" accept="image/*" hint="JPG · PNG · WEBP · max 2 MB." />
                            </div>
                        </section>
                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update slide' : 'Save slide'}</button>
                            <a href={routes.index} className="mc-btn ghost">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
