import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Status({ status }) {
    return status === 1
        ? <span className="mc-pill p-active"><i />Active</span>
        : <span className="mc-pill p-inactive"><i />Inactive</span>;
}

export default function TimeSlotsIndex({ timeSlots, routes }) {
    const { csrfToken } = usePage().props;

    return (
        <AdminLayout title="Time slots" active="time-slots" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Scheduling</p><h1 className="mc-title">Time <em>slots</em></h1><p className="mc-sub">All available time slots for appointments.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add new</a></div>
            </div>

            <section className="mc-card" aria-label="Time slot register">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Time</th><th>Status</th><th className="text-right">Actions</th></tr></thead>
                        <tbody>{timeSlots.length ? timeSlots.map((slot, index) => (
                            <tr key={slot.id}>
                                <td className="mc-idx">{index + 1}</td><td className="mc-num">{slot.time}</td><td><Status status={slot.status} /></td>
                                <td><div className="mc-acts">
                                    <a href={`${routes.editBase}/${slot.id}/edit`} className="mc-btn sm"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a>
                                    <form action={`${routes.deleteBase}/${slot.id}`} method="post" className="m-0"
                                        onSubmit={(event) => { if (!window.confirm('Are you sure?')) event.preventDefault(); }}>
                                        <input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="DELETE" />
                                        <button type="submit" className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                                    </form>
                                </div></td>
                            </tr>
                        )) : <tr><td colSpan="4"><div className="mc-empty"><b>Nothing on this chart</b>No time slots found.</div></td></tr>}</tbody>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
