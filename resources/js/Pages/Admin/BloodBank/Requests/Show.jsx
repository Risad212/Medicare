import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const terminal = ['fulfilled', 'rejected', 'cancelled'];

export default function BloodRequestShow({ bloodRequest, availableUnits, availableQty, reservedUnits, routes }) {
    const { flash = {} } = usePage().props;
    function confirmAction(message, action) {
        if (window.confirm(message)) action();
    }
    const remaining = Math.max(0, bloodRequest.quantity - bloodRequest.issuedQuantity);

    return (
        <AdminLayout title={`Blood request #${bloodRequest.id}`} active="blood-requests" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Request <em>#{bloodRequest.id}</em></h1><p className="mc-sub">Requested vs available, reservation state, and issue history.</p></div>
                <div className="mc-head-acts">
                    {bloodRequest.status === 'pending' ? <>
                        <button type="button" className="mc-btn" onClick={() => confirmAction('Approve and reserve blood for this request?', () => router.post(routes.approve))}>Approve &amp; reserve</button>
                        <button type="button" className="mc-btn danger-ghost" onClick={() => confirmAction('Reject this request?', () => router.post(routes.reject))}>Reject</button>
                        <button type="button" className="mc-btn ghost" onClick={() => confirmAction('Permanently delete this pending request?', () => router.delete(routes.delete))}>Delete</button>
                    </> : <>
                        {['approved', 'partially_approved'].includes(bloodRequest.status) && <a href={routes.issue} className="mc-btn">Issue blood</a>}
                        {!terminal.includes(bloodRequest.status) && <button type="button" className="mc-btn ghost" onClick={() => confirmAction('Cancel this request and release reservations?', () => router.post(routes.cancel))}>Cancel</button>}
                    </>}
                </div>
            </div>
            {flash.success && <div role="status" className="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{flash.success}</div>}
            {flash.error && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{flash.error}</div>}
            <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <section className="mc-card p-4"><Info label="Patient" value={bloodRequest.patientName} /><Info label="Blood group" value={bloodRequest.group} /><Info label="Quantity" value={`${bloodRequest.quantity} ${bloodRequest.unit}`} /><Info label="Doctor" value={bloodRequest.doctorName} /></section>
                <section className="mc-card p-4"><Info label="Urgency" value={capitalize(bloodRequest.urgency)} /><Info label="Required date" value={bloodRequest.requiredDate} /><Info label="Request date" value={bloodRequest.createdAt} /><Info label="Status" value={formatStatus(bloodRequest.status)} /></section>
            </div>
            <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                <Card label={`Available (${bloodRequest.group})`} value={`${availableUnits} units (${availableQty} ml)`} />
                <Card label="Issued so far" value={`${bloodRequest.issuedQuantity} ${bloodRequest.unit}`} />
                <Card label="Remaining need" value={`${remaining} ${bloodRequest.unit}`} />
            </div>
            <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Clinical context</h2></div><div className="p-4"><Info label="Department" value={bloodRequest.department} /><Info label="Reason" value={bloodRequest.reason} /><Info label="Notes" value={bloodRequest.notes} /><Info label="Requested by" value={bloodRequest.requesterName} /></div></section>
                <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Reserved bags</h2></div><div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Bag</th><th>Qty</th><th>Expiry</th><th>Status</th></tr></thead>
                    <tbody>{reservedUnits.length ? reservedUnits.map((bag) => <tr key={bag.id}><td>{bag.bagNumber}</td><td>{bag.quantity} {bag.unit}</td><td>{bag.expiryDate}</td><td>{capitalize(bag.status)}</td></tr>) : <tr><td colSpan="4"><div className="mc-empty"><b>No reservations</b>Nothing is held against this request.</div></td></tr>}</tbody>
                </table></div></section>
            </div>
            <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Issue history</h2></div><div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>#</th><th>Issue date</th><th>Bag</th><th>Qty</th><th>Receiver</th><th>Action</th></tr></thead>
                <tbody>{bloodRequest.issues.length ? bloodRequest.issues.map((issue) => <tr key={issue.id}><td className="mc-idx">#{issue.id}</td><td>{issue.date}</td><td>{issue.bagNumber}</td><td>{issue.quantity} {issue.unit}</td><td>{issue.receiverName}</td><td><a href={`${routes.issueShowBase}/${issue.id}`} className="mc-btn sm">View</a></td></tr>) : <tr><td colSpan="6"><div className="mc-empty"><b>Not yet issued</b>No blood has been handed over for this request.</div></td></tr>}</tbody>
            </table></div></section>
        </AdminLayout>
    );
}

function Card({ label, value }) { return <div className="mc-card p-4"><p className="mb-1 text-sm font-semibold text-mut">{label}</p><p className="mb-0 text-lg font-bold">{value}</p></div>; }
function Info({ label, value }) { return <div className="mb-3 last:mb-0"><p className="mb-1 text-xs font-semibold text-mut">{label}</p><p className="mb-0 whitespace-pre-wrap">{value || '—'}</p></div>; }
function capitalize(value) { return value.charAt(0).toUpperCase() + value.slice(1); }
function formatStatus(value) { return value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase()); }
