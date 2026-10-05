import AdminLayout from '../../../../Components/AdminLayout';

export default function BloodDonationShow({ donation, routes }) {
    return (
        <AdminLayout title="Donation detail" active="blood-bank" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Donation <em>detail</em></h1><p className="mc-sub">Unit facts and every issue linked to this bag.</p></div>
                <div className="mc-head-acts"><a href={routes.index} className="mc-btn ghost">Back to donations</a><a href={routes.edit} className="mc-btn">Edit</a></div>
            </div>
            <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <section className="mc-card p-4"><Info label="Donor" value={donation.donorName} /><Info label="Blood group" value={donation.bloodGroup} /><Info label="Quantity" value={`${donation.quantity} ${donation.unit}`} /><Info label="Bag number" value={donation.bagNumber} /></section>
                <section className="mc-card p-4"><Info label="Collection location" value={donation.collectionLocation} /><Info label="Donation date" value={donation.donationDate} /><Info label="Expiry date" value={donation.expiryDate} /><Info label="Status" value={capitalize(donation.status)} />{donation.creatorName && <Info label="Recorded by" value={donation.creatorName} />}</section>
            </div>
            <section className="mc-card">
                <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Issue history</h2></div>
                <div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>#</th><th>Issue date</th><th>Request</th><th>Patient</th><th>Quantity</th></tr></thead>
                    <tbody>{donation.issues.length ? donation.issues.map((issue, index) => <tr key={issue.id}><td className="mc-idx">{index + 1}</td><td>{issue.date}</td><td>#{issue.requestId}</td><td>{issue.patientName || '—'}</td><td>{issue.quantity} {issue.unit}</td></tr>) : <tr><td colSpan="5"><div className="mc-empty"><b>Not yet issued</b>This bag has no issue records.</div></td></tr>}</tbody>
                </table></div>
            </section>
        </AdminLayout>
    );
}

function Info({ label, value }) {
    return <div className="mb-3 last:mb-0"><p className="mb-1 text-xs font-semibold text-mut">{label}</p><p className="mb-0">{value || '—'}</p></div>;
}

function capitalize(value) {
    return value.charAt(0).toUpperCase() + value.slice(1);
}
