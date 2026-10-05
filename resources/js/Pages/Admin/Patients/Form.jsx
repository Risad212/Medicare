import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-teal focus:ring-2 focus:ring-teal/15';
const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

function Field({ form, name, label, type = 'text', children, full = false }) {
    const id = `patient-${name}`;
    const error = form.errors[name];

    return (
        <div className={`mc-f ${full ? 'full' : ''}`}>
            <label htmlFor={id}>{label}</label>
            {children || (
                <input id={id} className={`${inputClass} ${error ? 'border-red-500' : ''}`} name={name} type={type}
                    value={form.data[name] ?? ''} onChange={(event) => form.setData(name, event.target.value)}
                    aria-invalid={Boolean(error)} aria-describedby={error ? `${id}-error` : undefined} />
            )}
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
        </div>
    );
}

export default function PatientForm({ mode, patient = null, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        name: patient?.name ?? '',
        email: patient?.email ?? '',
        phone: patient?.phone ?? '',
        gender: patient?.gender ?? '',
        date_of_birth: patient?.dateOfBirth ?? '',
        blood_group: patient?.bloodGroup ?? '',
        address: patient?.address ?? '',
    });
    const initials = (patient?.name || 'Patient').split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();

    function submit(event) {
        event.preventDefault();
        if (isEdit) {
            form.put(routes.update);
        } else {
            form.post(routes.store);
        }
    }

    return (
        <AdminLayout title={isEdit ? 'Edit patient' : 'Add patient'} active="patients" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Records</p>
                    <h1 className="mc-title">{isEdit ? <>Patient <em>record</em></> : <>New pati<em>ent</em></>}</h1>
                    <p className="mc-sub">{isEdit ? 'Contact and clinical basics. Linked visits stay untouched.' : 'Add a patient to the clinic register. No login account is created.'}</p>
                </div>
            </div>

            <form onSubmit={submit} noValidate>
                <div className="mc-grid">
                    <aside className="mc-side">
                        <div className="mc-avbig">{initials}</div>
                        <div className="k">{isEdit ? 'Currently editing' : 'New record'}</div>
                        <h2>{patient?.name || 'Unsaved patient'}</h2>
                        <p>{isEdit ? `${patient.email || 'No email'} · since ${patient.registeredYear}` : 'Record only — no login is created.'}</p>
                        <ul className="mc-steps"><li><span className="n">1</span>Identity</li><li><span className="n">2</span>Details</li></ul>
                    </aside>

                    <div>
                        {Object.keys(form.errors).length > 0 && (
                            <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the highlighted fields and try again.</div>
                        )}
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">01</span><h3>Identity</h3><p>Who this patient is.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="name" label="Patient name" />
                                <Field form={form} name="email" label="Email" type="email" />
                                <Field form={form} name="phone" label="Phone" />
                            </div>
                        </section>

                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">02</span><h3>Details</h3><p>Clinical basics for the chart.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="gender" label="Gender">
                                    <select id="patient-gender" className={inputClass} name="gender" value={form.data.gender} onChange={(event) => form.setData('gender', event.target.value)}>
                                        <option value="">Select gender</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option>
                                    </select>
                                </Field>
                                <Field form={form} name="date_of_birth" label="Date of birth" type="date" />
                                <Field form={form} name="blood_group" label="Blood group">
                                    <select id="patient-blood_group" className={inputClass} name="blood_group" value={form.data.blood_group} onChange={(event) => form.setData('blood_group', event.target.value)}>
                                        <option value="">Select blood group</option>{bloodGroups.map((group) => <option key={group} value={group}>{group}</option>)}
                                    </select>
                                </Field>
                                <Field form={form} name="address" label="Address" full>
                                    <textarea id="patient-address" className={inputClass} name="address" rows="3" value={form.data.address} onChange={(event) => form.setData('address', event.target.value)} />
                                </Field>
                            </div>
                        </section>

                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update patient' : 'Save patient'}</button>
                            <a href={routes.index} className="mc-btn ghost">Back</a>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
