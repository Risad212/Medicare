import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function BedForm({ mode, bed = null, rooms, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        room_id: bed?.roomId ?? '',
        bed_number: bed?.bedNumber ?? '',
        status: String(bed?.status ?? 0),
    });

    function submit(event) {
        event.preventDefault();
        if (isEdit) form.put(routes.update);
        else form.post(routes.store);
    }

    return (
        <AdminLayout title={isEdit ? 'Edit bed' : 'Add bed'} active="beds" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · In-patient</p><h1 className="mc-title">{isEdit ? <>Edit <em>bed</em></> : <>Add <em>bed</em></>}</h1><p className="mc-sub">Assign a bed to a room and set its availability.</p></div></div>
            <section className="mc-card max-w-3xl">
                <div className="border-b border-line-2 px-4.5 py-3"><h2 className="text-[15px] font-bold">Bed details</h2></div>
                <form onSubmit={submit} className="p-4.5">
                    {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                    <div className="mb-4">
                        <label htmlFor="bed-room" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Room <span aria-hidden="true">*</span></label>
                        <select id="bed-room" className={inputClass} value={form.data.room_id} onChange={(event) => form.setData('room_id', event.target.value)} required>
                            <option value="">-- Select room --</option>
                            {rooms.map((room) => <option key={room.id} value={room.id}>{room.wardName || ''} · Room {room.roomNumber}</option>)}
                        </select>
                        {form.errors.room_id && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.room_id}</p>}
                    </div>
                    <div className="mb-4">
                        <label htmlFor="bed-number" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Bed number <span aria-hidden="true">*</span></label>
                        <input id="bed-number" className={inputClass} value={form.data.bed_number} onChange={(event) => form.setData('bed_number', event.target.value)} placeholder="e.g. B-01" required />
                        {form.errors.bed_number && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.bed_number}</p>}
                    </div>
                    <div className="mb-4">
                        <label htmlFor="bed-status" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Status <span aria-hidden="true">*</span></label>
                        <select id="bed-status" className={inputClass} value={form.data.status} onChange={(event) => form.setData('status', event.target.value)}>
                            <option value="0">Available</option><option value="2">Under maintenance</option>
                        </select>
                        <p className="mc-hint">Occupied is set only via patient assignment.</p>
                    </div>
                    <div className="mc-formacts">
                        <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Save changes' : 'Create bed'}</button>
                        <a href={routes.index} className="mc-btn ghost">Cancel</a>
                    </div>
                </form>
            </section>
        </AdminLayout>
    );
}
