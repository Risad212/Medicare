import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AdminLayout from '../../../Components/AdminLayout';

const statusClass = {
    0: 'p-pending',
    1: 'p-progress',
    2: 'p-confirmed',
    3: 'p-cancelled',
};

export default function AmbulanceIndex({ requests, routes }) {
    const { errors = {} } = usePage().props;
    const [statuses, setStatuses] = useState({});
    const errorMessages = Object.values(errors);

    function updateStatus(request) {
        router.put(`${routes.updateBase}/${request.id}`, {
            status: statuses[request.id] ?? request.transitions[0]?.value,
        }, {
            preserveScroll: true,
            onSuccess: () => setStatuses((current) => {
                const next = { ...current };
                delete next[request.id];
                return next;
            }),
        });
    }

    return (
        <AdminLayout title="Ambulance requests" active="ambulance" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Emergency</p><h1 className="mc-title">Ambulance <em>requests</em></h1><p className="mc-sub">Newest emergencies on top — tap the phone number to call back.</p></div>
            </div>
            {errorMessages.length > 0 && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{errorMessages.join(' ')}</div>}
            <div className="mc-ecg"><span>Emergency queue</span><span>{requests.total} requests</span></div>
            <section className="mc-card">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Requester</th><th>Phone</th><th>Pickup</th><th>Emergency</th><th>Status</th><th>Received</th><th>Action</th></tr></thead>
                        <tbody>
                            {requests.data.length ? requests.data.map((request, index) => (
                                <tr key={request.id}>
                                    <td className="mc-idx">{(requests.firstItem || 1) + index}</td>
                                    <td><b>{request.requesterName}</b>{request.destination && <span className="mc-sub2 block">Destination: {request.destination}</span>}</td>
                                    <td className="mc-num"><a href={`tel:${request.requesterPhone}`}>{request.requesterPhone}</a></td>
                                    <td>{request.pickupAddress.length > 60 ? `${request.pickupAddress.slice(0, 57)}…` : request.pickupAddress}</td>
                                    <td>{request.emergencyType || '—'}</td>
                                    <td><span className={`mc-pill ${statusClass[request.status] || 'p-pending'}`}>{request.statusLabel}</span></td>
                                    <td className="mc-num whitespace-nowrap">{request.receivedAt}</td>
                                    <td>{request.transitions.length ? (
                                        <form className="flex min-w-44 gap-2" onSubmit={(event) => { event.preventDefault(); updateStatus(request); }}>
                                            <label htmlFor={`status-${request.id}`} className="sr-only">Status for request by {request.requesterName}</label>
                                            <select id={`status-${request.id}`} className="min-w-0 rounded-lg border border-line bg-white px-2 py-1 text-xs" value={statuses[request.id] ?? request.transitions[0].value} onChange={(event) => setStatuses({ ...statuses, [request.id]: Number(event.target.value) })}>
                                                {request.transitions.map((transition) => <option key={transition.value} value={transition.value}>{transition.label}</option>)}
                                            </select>
                                            <button type="submit" className="mc-btn sm dark">Set</button>
                                        </form>
                                    ) : <span className="text-mut">Terminal</span>}</td>
                                </tr>
                            )) : <tr><td colSpan="8"><div className="mc-empty"><b>No requests</b>No ambulance requests yet.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={requests} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    if (page.lastPage <= 1) return null;
    return <div className="mc-pg"><span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span><nav aria-label="Ambulance request pages" className="flex items-center gap-1">
        {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
        <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
        {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
    </nav></div>;
}
