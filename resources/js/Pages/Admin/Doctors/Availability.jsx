import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function DoctorAvailability({ doctor, weekdays, slots, openByWeekday, offDays, minDate, routes }) {
    const schedule = useForm({
        schedules: Object.fromEntries(weekdays.map(({ day }) => [day, openByWeekday[day] || []])),
    });
    const offDay = useForm({ date: '', reason: '' });
    const deleteForm = useForm({});

    function toggleSlot(day, slotId, checked) {
        const selected = schedule.data.schedules[day] || [];
        schedule.setData('schedules', {
            ...schedule.data.schedules,
            [day]: checked ? [...selected, slotId] : selected.filter((id) => id !== slotId),
        });
    }

    function addOffDay(event) {
        event.preventDefault();
        offDay.post(routes.offDayStore, { onSuccess: () => offDay.reset() });
    }

    return (
        <AdminLayout title="Doctor availability" active="doctors" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Staff</p><h1 className="mc-title">Avail<em>ability</em></h1><p className="mc-sub">{doctor.name} — weekly schedule, off days and slot control.</p></div>
                <div className="mc-head-acts"><a href={routes.index} className="mc-btn ghost">Back to list</a></div>
            </div>

            {schedule.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the schedule fields and try again.</div>}
            {offDay.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the off-day fields and try again.</div>}
            {Object.entries(schedule.errors).map(([field, error]) => <p key={field} role="alert" className="text-xs text-red-t">{error}</p>)}
            {Object.entries(offDay.errors).map(([field, error]) => <p key={field} role="alert" className="text-xs text-red-t">{error}</p>)}
            {Object.entries(deleteForm.errors).map(([field, error]) => <p key={field} role="alert" className="text-xs text-red-t">{error}</p>)}

            {weekdays.every(({ day }) => !(openByWeekday[day] || []).length) && (
                <div className="mb-4 rounded-lg bg-blue-bg px-4 py-3 text-sm text-blue-t">This doctor has no weekly schedule yet, so every slot is currently open for booking. Tick slots below to set when this doctor sees patients.</div>
            )}

            <section className="mc-card mb-4">
                <div className="border-b border-line-2 px-4.5 py-3"><h2 className="text-[15px] font-bold">Weekly schedule</h2><p className="mt-1 text-xs text-mut">Uncheck a slot to make it unavailable. Days with no slots selected become fully closed for that doctor.</p></div>
                <form onSubmit={(event) => { event.preventDefault(); schedule.post(routes.availabilityUpdate); }}>
                    <div className="overflow-x-auto">
                        <table className="mc-tbl text-left">
                            <thead><tr><th>Day</th>{slots.map((slot) => <th key={slot.id}>{slot.time}</th>)}</tr></thead>
                            <tbody>{weekdays.map(({ day, label }) => (
                                <tr key={day}><td className="font-bold">{label}</td>{slots.map((slot) => (
                                    <td key={slot.id}><input aria-label={`${label} ${slot.time}`} type="checkbox" className="h-[17px] w-[17px] accent-teal"
                                        checked={(schedule.data.schedules[day] || []).includes(slot.id)}
                                        onChange={(event) => toggleSlot(day, slot.id, event.target.checked)} /></td>
                                ))}</tr>
                            ))}</tbody>
                        </table>
                    </div>
                    <div className="px-4.5 py-3"><button type="submit" className="mc-btn" disabled={schedule.processing}>{schedule.processing ? 'Saving…' : 'Save schedule'}</button></div>
                </form>
            </section>

            <section className="mc-card">
                <div className="border-b border-line-2 px-4.5 py-3"><h2 className="text-[15px] font-bold">Off days</h2><p className="mt-1 text-xs text-mut">No appointments can be booked on these days regardless of the weekly schedule.</p></div>
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>Date</th><th>Reason</th><th className="text-right">Action</th></tr></thead>
                        <tbody>{offDays.length ? offDays.map((item) => (
                            <tr key={item.id}><td className="mc-num">{item.displayDate}</td><td>{item.reason || 'N/A'}</td><td><div className="mc-acts">
                                <form onSubmit={(event) => { event.preventDefault(); if (window.confirm('Remove this off-day?')) deleteForm.delete(`${routes.offDayDeleteBase}/${item.id}`); }}>
                                    <button type="submit" className="mc-btn sm danger-ghost" disabled={deleteForm.processing} aria-label={`Remove off-day ${item.displayDate}`}><i aria-hidden="true" className="bi bi-x-lg" /></button>
                                </form>
                            </div></td></tr>
                        )) : <tr><td colSpan="3" className="py-3 text-center text-sm text-mut">No off days scheduled.</td></tr>}</tbody>
                    </table>
                </div>
                <div className="border-t border-line-2 px-4.5 py-3">
                    <form onSubmit={addOffDay} className="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                        <div><label htmlFor="off-day-date" className="mb-1 block text-xs font-bold tracking-wide text-ink-2">Date</label><input id="off-day-date" type="date" name="date" className={inputClass} min={minDate} value={offDay.data.date} onChange={(event) => offDay.setData('date', event.target.value)} required /></div>
                        <div><label htmlFor="off-day-reason" className="mb-1 block text-xs font-bold tracking-wide text-ink-2">Reason</label><input id="off-day-reason" type="text" name="reason" className={inputClass} placeholder="Reason (optional)" value={offDay.data.reason} onChange={(event) => offDay.setData('reason', event.target.value)} /></div>
                        <button type="submit" className="mc-btn sm" disabled={offDay.processing}>{offDay.processing ? 'Adding…' : 'Add off-day'}</button>
                    </form>
                </div>
            </section>
        </AdminLayout>
    );
}
