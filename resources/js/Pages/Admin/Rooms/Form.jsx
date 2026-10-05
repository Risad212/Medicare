import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';
const roomTypes = ['General', 'Private', 'VIP'];

export default function RoomForm({ mode, room = null, wards, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        ward_id: room?.wardId ?? '',
        room_number: room?.roomNumber ?? '',
        room_type: room?.roomType ?? 'General',
    });

    function submit(event) {
        event.preventDefault();
        if (isEdit) form.put(routes.update);
        else form.post(routes.store);
    }

    return (
        <AdminLayout title={isEdit ? 'Edit room' : 'Add room'} active="beds" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · In-patient</p><h1 className="mc-title">{isEdit ? <>Edit <em>room</em></> : <>New <em>room</em></>}</h1><p className="mc-sub">Place a room within a ward and set its type.</p></div></div>
            <section className="mc-card max-w-3xl">
                <div className="border-b border-line-2 px-4.5 py-3"><h2 className="text-[15px] font-bold">Room details</h2></div>
                <form onSubmit={submit} className="p-4.5">
                    {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                    <div className="mb-4">
                        <label htmlFor="room-ward" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Ward <span aria-hidden="true">*</span></label>
                        <select id="room-ward" className={inputClass} value={form.data.ward_id} onChange={(event) => form.setData('ward_id', event.target.value)} required>
                            <option value="">-- Select ward --</option>{wards.map((ward) => <option key={ward.id} value={ward.id}>{ward.name}</option>)}
                        </select>{form.errors.ward_id && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.ward_id}</p>}
                    </div>
                    <div className="mb-4"><label htmlFor="room-number" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Room number <span aria-hidden="true">*</span></label><input id="room-number" className={inputClass} value={form.data.room_number} onChange={(event) => form.setData('room_number', event.target.value)} placeholder="e.g. 101" required />{form.errors.room_number && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.room_number}</p>}</div>
                    <div className="mb-4">
                        <label htmlFor="room-type" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Room type <span aria-hidden="true">*</span></label>
                        <select id="room-type" className={inputClass} value={form.data.room_type} onChange={(event) => form.setData('room_type', event.target.value)} required>{roomTypes.map((type) => <option key={type} value={type}>{type}</option>)}</select>
                        {form.errors.room_type && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.room_type}</p>}
                    </div>
                    <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Save changes' : 'Create room'}</button><a href={routes.index} className="mc-btn ghost">Cancel</a></div>
                </form>
            </section>
        </AdminLayout>
    );
}
