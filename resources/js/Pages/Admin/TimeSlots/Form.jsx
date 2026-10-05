import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function TimeSlotForm({ mode, timeSlot = null, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        time: timeSlot?.time ?? '',
        status: timeSlot ? timeSlot.status : true,
    });

    function submit(event) {
        event.preventDefault();
        form.transform(({ status, ...data }) => status ? { ...data, status: '1' } : data);
        if (isEdit) {
            form.put(routes.update);
        } else {
            form.post(routes.store);
        }
    }

    return (
        <AdminLayout title={isEdit ? 'Edit time slot' : 'Add time slot'} active="time-slots" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Scheduling</p><h1 className="mc-title">{isEdit ? <>Edit time <em>slot</em></> : <>New time <em>slot</em></>}</h1>
                <p className="mc-sub">{isEdit ? "Update this time slot's details." : 'Add a new time slot for appointments.'}</p></div></div>
            <section className="mc-card max-w-3xl">
                <div className="border-b border-line-2 px-4.5 py-3"><h2 className="text-[15px] font-bold">{isEdit ? 'Edit time slot' : 'Add new time slot'}</h2></div>
                <div className="p-4.5">
                    {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                    <form onSubmit={submit}>
                        <div className="mb-4">
                            <label htmlFor="time-slot-time" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Time</label>
                            <input id="time-slot-time" type="text" className={`${inputClass} ${form.errors.time ? 'border-red-500' : ''}`} placeholder="e.g. 09:00 AM"
                                value={form.data.time} onChange={(event) => form.setData('time', event.target.value)} required
                                aria-invalid={Boolean(form.errors.time)} aria-describedby={form.errors.time ? 'time-slot-time-error' : undefined} />
                            {form.errors.time && <p id="time-slot-time-error" role="alert" className="mt-1 text-xs text-red-t">{form.errors.time}</p>}
                        </div>
                        <div className="mb-4"><label className="mc-check"><input type="checkbox" checked={form.data.status} onChange={(event) => form.setData('status', event.target.checked)} /><span>Active</span></label></div>
                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update time slot' : 'Save time slot'}</button>
                            <a href={routes.index} className="mc-btn ghost">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </AdminLayout>
    );
}
