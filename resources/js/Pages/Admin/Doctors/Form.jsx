import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-teal focus:ring-2 focus:ring-teal/15';

function Field({ form, name, label, type = 'text', children, hint }) {
    const id = `doctor-${name}`;
    const error = form.errors[name];

    return (
        <div className="mc-f">
            <label htmlFor={id}>{label}</label>
            {children || <input id={id} className={`${inputClass} ${error ? 'border-red-500' : ''}`} name={name} type={type}
                value={type === 'file' ? undefined : form.data[name] ?? ''} onChange={(event) => form.setData(name, type === 'file' ? event.target.files[0] : event.target.value)}
                aria-invalid={Boolean(error)} aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined} />}
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
            {!error && hint && <span id={`${id}-hint`} className="mc-hint">{hint}</span>}
        </div>
    );
}

export default function DoctorForm({ mode, doctor = null, departments = [], routes, storageUrl }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        name: doctor?.name ?? '',
        phone: doctor?.phone ?? '',
        department: doctor?.department ?? '',
        specialist: doctor?.specialist ?? '',
        degree: doctor?.degree ?? '',
        availability: doctor?.availability ?? '',
        services: doctor?.services ?? '',
        image: null,
        status: String(doctor?.status ?? 1),
        email: doctor?.email ?? '',
        password: '',
    });
    const initials = (doctor?.name || 'Doctor').split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
    const imageUrl = doctor?.image ? `${storageUrl}/${doctor.image.replace(/^\/+/, '')}` : null;

    function submit(event) {
        event.preventDefault();
        if (isEdit) {
            form.transform((data) => ({ ...data, _method: 'PUT' })).post(routes.update);
        } else {
            form.post(routes.store);
        }
    }

    return (
        <AdminLayout title={isEdit ? 'Edit doctor' : 'Add doctor'} active="doctors" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Staff record</p><h1 className="mc-title">{isEdit ? <>Doctor <em>profile</em></> : <>New <em>doctor</em></>}</h1>
                <p className="mc-sub">Identity, credentials and booking status. {isEdit ? 'Changes apply on save — nothing goes live early.' : 'The login account is created together with the profile.'}</p></div></div>

            <form onSubmit={submit} encType="multipart/form-data" noValidate>
                <div className="mc-grid">
                    <aside className="mc-side">
                        <div className="mc-avbig">{imageUrl ? <img src={imageUrl} alt={doctor.name} className="h-full w-full rounded-full object-cover" /> : initials}</div>
                        <div className="k">{isEdit ? 'Currently editing' : 'New record'}</div>
                        <h2>{doctor?.name || 'Unsaved doctor'}</h2>
                        <p>{isEdit ? `${doctor.department || 'General'}${doctor.specialist ? ` · ${doctor.specialist}` : ''} · ${doctor.status === 1 ? 'Active' : 'Inactive'}` : 'Profile + login go live together on save.'}</p>
                        <ul className="mc-steps"><li><span className="n">1</span>Identity</li><li><span className="n">2</span>Credentials</li><li><span className="n">3</span>Account</li></ul>
                    </aside>

                    <div>
                        {Object.keys(form.errors).length > 0 && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the highlighted fields and try again.</div>}
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">01</span><h3>Identity</h3><p>Who this doctor is on the roster.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="name" label="Doctor name" />
                                <Field form={form} name="phone" label="Phone" />
                                <Field form={form} name="department" label="Department">
                                    <select id="doctor-department" className={inputClass} value={form.data.department} onChange={(event) => form.setData('department', event.target.value)}>
                                        <option value="">Select department</option>{departments.map((department) => <option key={department.id} value={department.name}>{department.name}</option>)}
                                    </select>
                                </Field>
                                <Field form={form} name="specialist" label="Specialist" />
                                <Field form={form} name="degree" label="Degree" />
                                <Field form={form} name="availability" label="Availability" />
                                <Field form={form} name="services" label="Services" hint="HTML markup is removed before saving." >
                                    <textarea id="doctor-services" className={inputClass} rows="3" value={form.data.services} onChange={(event) => form.setData('services', event.target.value)} />
                                </Field>
                            </div>
                        </section>

                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">02</span><h3>Credentials</h3><p>Photo and booking status.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="image" label="Doctor image" type="file" hint="JPG · PNG · WEBP · max 2 MB" />
                                <Field form={form} name="status" label="Status">
                                    <select id="doctor-status" className={inputClass} value={form.data.status} onChange={(event) => form.setData('status', event.target.value)}>
                                        <option value="1">Active</option><option value="0">Inactive</option>
                                    </select>
                                </Field>
                            </div>
                        </section>

                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">03</span><h3>Login account</h3><p>How this doctor signs in.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="email" label="Email" type="email" />
                                <Field form={form} name="password" label={isEdit ? 'Password (optional)' : 'Password'} type="password" hint={isEdit ? 'Leave blank to keep the current password.' : 'At least 8 characters.'} />
                            </div>
                        </section>

                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update doctor' : 'Save doctor'}</button>
                            <a href={routes.index} className="mc-btn ghost">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
