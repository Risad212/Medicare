import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-teal focus:ring-2 focus:ring-teal/15';

function Field({ label, name, form, type = 'text', children, required = false, hint }) {
    const error = form.errors[name];
    const id = `appointment-${name}`;

    return (
        <div className="mc-f">
            <label htmlFor={id}>{label}{required && <i className="req"> *</i>}</label>
            {children || (
                <input
                    id={id}
                    className={`${inputClass} ${error ? 'border-red-500' : ''}`}
                    name={name}
                    type={type}
                    value={form.data[name] ?? ''}
                    onChange={(event) => form.setData(name, event.target.value)}
                    aria-invalid={Boolean(error)}
                    aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined}
                    required={required}
                />
            )}
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
            {!error && hint && <p id={`${id}-hint`} className="mc-hint">{hint}</p>}
        </div>
    );
}

function SelectField({ label, name, form, options, required = false }) {
    const id = `appointment-${name}`;
    const error = form.errors[name];

    return (
        <Field label={label} name={name} form={form} required={required}>
            <select
                id={id}
                className={`${inputClass} ${error ? 'border-red-500' : ''}`}
                name={name}
                value={form.data[name] ?? ''}
                onChange={(event) => form.setData(name, event.target.value)}
                aria-invalid={Boolean(error)}
                aria-describedby={error ? `${id}-error` : undefined}
                required={required}
            >
                {options.map(([value, text]) => <option key={value} value={value}>{text}</option>)}
            </select>
        </Field>
    );
}

export default function AppointmentForm({
    mode,
    appointment = null,
    defaultDate,
    doctors,
    timeSlots,
    routes,
}) {
    const isEdit = mode === 'edit';
    const form = useForm({
        doctor_id: appointment?.doctorId ?? '',
        time_slot_id: appointment?.timeSlotId ?? '',
        name: appointment?.name ?? '',
        age: appointment?.age ?? '',
        gender: appointment?.gender ?? '1',
        phone: appointment?.phone ?? '',
        email: appointment?.email ?? '',
        visit_type: appointment?.visitType ?? '1',
        date: appointment?.date ?? defaultDate,
        ...(isEdit ? { status: appointment.status } : {}),
    });

    function submit(event) {
        event.preventDefault();
        if (isEdit) {
            form.put(routes.update);
        } else {
            form.post(routes.store);
        }
    }

    const initials = (appointment?.name || 'Appointment')
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    return (
        <AdminLayout
            title={isEdit ? 'Edit appointment' : 'Book appointment'}
            active="appointments"
            routes={routes}
        >
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Front desk</p>
                    <h1 className="mc-title">{isEdit ? <>Edit appoint<em>ment</em></> : <>Book appoint<em>ment</em></>}</h1>
                    <p className="mc-sub">
                        {isEdit
                            ? `${appointment.name} · ${appointment.doctorName} · ${appointment.date}`
                            : 'Patient, doctor and slot. Double-booked slots are refused automatically.'}
                    </p>
                </div>
            </div>

            <form onSubmit={submit} noValidate>
                <div className="mc-grid">
                    <aside className="mc-side">
                        <div className="mc-avbig">{initials}</div>
                        <div className="k">{isEdit ? 'Currently editing' : 'New record'}</div>
                        <h2>{isEdit ? appointment.name : 'Unsaved booking'}</h2>
                        <p>{isEdit ? `${appointment.time} · ${appointment.visitTypeLabel}` : 'Draft — nothing is booked until you save.'}</p>
                        <ul className="mc-steps">
                            <li><span className="n">1</span>Schedule</li>
                            <li><span className="n">2</span>Patient</li>
                            {isEdit && <li><span className="n">3</span>State</li>}
                        </ul>
                    </aside>

                    <div>
                        {Object.keys(form.errors).length > 0 && (
                            <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
                                Please review the highlighted fields and try again.
                            </div>
                        )}

                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">01</span><h3>Schedule</h3><p>Doctor, day and time.</p></div>
                            <div className="mc-sec-bd">
                                <SelectField label="Doctor" name="doctor_id" form={form} required options={[
                                    ['', 'Select doctor'],
                                    ...doctors.map((doctor) => [String(doctor.id), doctor.name]),
                                ]} />
                                <SelectField label="Time slot" name="time_slot_id" form={form} required={!isEdit} options={[
                                    ['', 'Select time slot'],
                                    ...timeSlots.map((slot) => [String(slot.id), slot.time]),
                                ]} />
                                <Field label="Appointment date" name="date" form={form} type="date" required />
                                <SelectField label="Visit type" name="visit_type" form={form} required options={[
                                    ['1', 'First visit'],
                                    ['2', 'Second visit'],
                                    ['3', 'Report review'],
                                ]} />
                            </div>
                        </section>

                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">02</span><h3>Patient</h3><p>Who is coming in.</p></div>
                            <div className="mc-sec-bd">
                                <Field label="Patient name" name="name" form={form} required />
                                <Field label="Phone" name="phone" form={form} required />
                                <Field label="Age" name="age" form={form} type="number" />
                                <SelectField label="Gender" name="gender" form={form} required options={[
                                    ['1', 'Male'],
                                    ['2', 'Female'],
                                    ['3', 'Other'],
                                ]} />
                                <Field label="Email" name="email" form={form} type="email" />
                            </div>
                        </section>

                        {isEdit && (
                            <section className="mc-sec">
                                <div className="mc-sec-hd"><span className="no">03</span><h3>State</h3><p>Where this booking stands.</p></div>
                                <div className="mc-sec-bd">
                                    <SelectField label="Status" name="status" form={form} required options={[
                                        ['0', 'Pending'],
                                        ['1', 'Approved'],
                                        ['2', 'Completed'],
                                        ['3', 'Cancelled'],
                                    ]} />
                                </div>
                            </section>
                        )}

                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>
                                {form.processing ? 'Saving…' : isEdit ? 'Update appointment' : 'Create appointment'}
                            </button>
                            <a href={routes.index} className="mc-btn ghost">Back</a>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
