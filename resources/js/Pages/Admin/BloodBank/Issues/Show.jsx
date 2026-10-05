import AdminLayout from '../../../../Components/AdminLayout';

export default function BloodIssueShow({ issue, routes }) {
    return (
        <AdminLayout title={`Issue #${issue.id}`} active="blood-issues" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Issue <em>#{issue.id}</em></h1><p className="mc-sub">Recorded handover details for this unit.</p></div><div className="mc-head-acts"><a href={routes.request} className="mc-btn ghost">Back to request</a></div></div>
            <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <section className="mc-card p-4"><Info label="Patient" value={issue.patientName} /><Info label="Request" value={`#${issue.requestId} · ${issue.requestGroup}`} /><Info label="Blood group" value={issue.bloodGroup} /><Info label="Quantity" value={`${issue.quantity} ${issue.unit}`} /></section>
                <section className="mc-card p-4"><Info label="Issue date" value={issue.issueDate} /><Info label="Bag / unit" value={issue.bagNumber} /><Info label="Donor" value={issue.donorName} /><Info label="Issued by" value={issue.issuerName} /></section>
            </div>
            <section className="mc-card p-4"><Info label="Receiver" value={`${issue.receiverName || issue.patientName} · ${issue.receiverPhone || '—'}`} /><Info label="Notes" value={issue.notes} /></section>
        </AdminLayout>
    );
}

function Info({ label, value }) {
    return <div className="mb-3 last:mb-0"><p className="mb-1 text-xs font-semibold text-mut">{label}</p><p className="mb-0 whitespace-pre-wrap">{value || '—'}</p></div>;
}
