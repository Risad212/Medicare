import { useEffect, useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';

const fieldClass = 'w-full rounded-xl border border-line bg-white px-4 py-3 text-sm text-ink placeholder:text-muted/75 focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/15';
const noSlots = [];

function Field({ name, label, type = 'text', form, children, ...props }) {
    const id = `booking-${name}`;
    const error = form.errors[name];
    return (
        <div>
            <label htmlFor={id} className="mb-2 block text-sm font-semibold text-ink">{label}</label>
            {children || (
                <input id={id} name={name} type={type} className={fieldClass} value={form.data[name] ?? ''}
                    onChange={(event) => form.setData(name, event.target.value)}
                    aria-invalid={Boolean(error)} aria-describedby={error ? `${id}-error` : undefined} {...props} />
            )}
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-700">{error}</p>}
        </div>
    );
}

export default function PublicAppointmentForm({
    doctors = [],
    availableSlots = noSlots,
    selectedDoctorId = '',
    minDate,
}) {
    const { frontendRoutes } = usePage().props;
    const form = useForm({
        patient_name: '',
        phone: '',
        email: '',
        doctor_id: String(selectedDoctorId),
        visit_type: '1',
        age: '',
        gender: '',
        appointment_date: '',
        time_slot_id: '',
    });
    const [slots, setSlots] = useState(availableSlots);
    const [bookedSlotIds, setBookedSlotIds] = useState([]);
    const [unavailableSlotIds, setUnavailableSlotIds] = useState([]);
    const [slotError, setSlotError] = useState('');
    const [loadingSlots, setLoadingSlots] = useState(false);

    useEffect(() => {
        if (!form.data.doctor_id || !form.data.appointment_date) {
            setSlots(availableSlots);
            setBookedSlotIds([]);
            setUnavailableSlotIds([]);
            setSlotError('');
            return undefined;
        }

        const controller = new AbortController();
        const params = new URLSearchParams({
            doctor_id: form.data.doctor_id,
            date: form.data.appointment_date,
        });

        async function loadSlots() {
            setLoadingSlots(true);
            setSlotError('');
            try {
                const response = await fetch(`${frontendRoutes.availableSlots}?${params}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('Could not load available appointment times. Please try again.');
                const result = await response.json();
                setSlots(result.slots || []);
                setBookedSlotIds(result.bookedSlotIds || []);
                setUnavailableSlotIds(result.unavailableSlotIds || []);
            } catch (error) {
                if (error.name !== 'AbortError') setSlotError(error.message);
            } finally {
                if (!controller.signal.aborted) setLoadingSlots(false);
            }
        }

        loadSlots();
        return () => controller.abort();
    }, [form.data.doctor_id, form.data.appointment_date, availableSlots, frontendRoutes.availableSlots]);

    function submit(event) {
        event.preventDefault();
        form.post(frontendRoutes.appointmentStore);
    }

    const slotOptions = slots.map((slot) => {
        const booked = bookedSlotIds.includes(slot.id);
        const unavailable = unavailableSlotIds.includes(slot.id);
        return [String(slot.id), `${slot.time}${booked ? ' · Booked' : unavailable ? ' · Unavailable' : ''}`, booked || unavailable];
    });

    return (
        <form onSubmit={submit} className="rounded-2xl border border-line bg-white p-6 shadow-sm sm:p-9">
            <div className="mb-6">
                <p className="text-xs font-bold uppercase tracking-[.2em] text-teal">Start here</p>
                <h2 className="mt-2 font-display text-3xl">Request an appointment</h2>
                <p className="mt-2 text-sm leading-6 text-muted">Share a few details and choose a time that works for you.</p>
            </div>
            {form.wasSuccessful && <div role="status" className="mb-5 rounded-xl bg-teal-pale p-4 text-sm text-teal-dark">Your appointment request has been saved.</div>}
            {form.hasErrors && <div role="alert" className="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-800">Please review the highlighted fields.</div>}
            {slotError && <div role="alert" className="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-800">{slotError}</div>}

            <div className="grid gap-5 sm:grid-cols-2">
                <Field name="patient_name" label="Patient name" form={form} autoComplete="name" required />
                <Field name="phone" label="Phone number" type="tel" form={form} autoComplete="tel" required />
                <Field name="email" label="Email (for your cancellation link)" type="email" form={form} autoComplete="email" />
                <Field name="doctor_id" label="Doctor" form={form}>
                    <select id="booking-doctor_id" name="doctor_id" className={fieldClass} value={form.data.doctor_id}
                        onChange={(event) => {
                            form.setData({ ...form.data, doctor_id: event.target.value, time_slot_id: '' });
                        }} required>
                        <option value="">Select a doctor</option>
                        {doctors.map((doctor) => <option key={doctor.id} value={doctor.id}>{doctor.name}</option>)}
                    </select>
                </Field>
                <Field name="visit_type" label="Visit type" form={form}>
                    <select id="booking-visit_type" name="visit_type" className={fieldClass} value={form.data.visit_type}
                        onChange={(event) => form.setData('visit_type', event.target.value)} required>
                        <option value="1">First visit</option>
                        <option value="2">Second visit</option>
                        <option value="3">Report review</option>
                    </select>
                </Field>
                <Field name="age" label="Age" type="number" min="0" max="120" form={form} />
                <Field name="gender" label="Gender" form={form}>
                    <select id="booking-gender" name="gender" className={fieldClass} value={form.data.gender}
                        onChange={(event) => form.setData('gender', event.target.value)} required>
                        <option value="">Select gender</option>
                        <option value="1">Male</option>
                        <option value="2">Female</option>
                        <option value="3">Other</option>
                    </select>
                </Field>
                <Field name="appointment_date" label="Appointment date" type="date" min={minDate} form={form} required />
                <Field name="time_slot_id" label="Available time" form={form}>
                    <select id="booking-time_slot_id" name="time_slot_id" className={fieldClass} value={form.data.time_slot_id}
                        onChange={(event) => form.setData('time_slot_id', event.target.value)} required
                        disabled={!form.data.doctor_id || !form.data.appointment_date || loadingSlots}>
                        <option value="">{loadingSlots ? 'Checking times…' : form.data.doctor_id && form.data.appointment_date ? 'Select an available time' : 'Choose a doctor and date first'}</option>
                        {slotOptions.map(([id, label, disabled]) => <option key={id} value={id} disabled={disabled}>{label}</option>)}
                    </select>
                </Field>
            </div>
            {form.errors.time_slot_id && <p role="alert" className="mt-1 text-xs text-red-700">{form.errors.time_slot_id}</p>}
            <button type="submit" disabled={form.processing || loadingSlots} className="mt-7 rounded-full bg-teal px-7 py-3.5 text-sm font-bold text-white transition hover:bg-teal-dark disabled:opacity-60">
                {form.processing ? 'Sending request…' : 'Request appointment'} <span className="ml-2" aria-hidden="true">↗</span>
            </button>
        </form>
    );
}
