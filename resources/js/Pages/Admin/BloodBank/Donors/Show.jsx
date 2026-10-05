import AdminLayout from '../../../../Components/AdminLayout';

const statusPill = {
    available: 'p-active',
    reserved: 'p-active',
    issued: 'p-active',
    expired: 'p-inactive',
    rejected: 'p-inactive',
};

export default function BloodDonorShow({ donor, minDonationDays, routes }) {
    return (
        <AdminLayout title={`${donor.name} profile`} active="blood-donors" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">{donor.name}<em> profile</em></h1><p className="mc-sub">Blood group, donation history and current eligibility.</p></div>
                <div className="mc-head-acts"><a href={routes.index} className="mc-btn ghost">Back to donors</a><a href={routes.edit} className="mc-btn">Edit</a></div>
            </div>
            <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                <Card label="Blood group" value={donor.bloodGroup || '—'} />
                <Card label="Total donations" value={donor.totalDonations} />
                <div className="mc-card p-4"><p className="mb-1 text-sm font-semibold text-mut">Eligibility</p><span className={`mc-pill ${donor.eligible ? 'p-active' : 'p-inactive'}`}>{donor.eligible ? 'Eligible' : 'Not eligible (interval)'}</span>{!donor.eligible && <small className="mt-1 block text-mut">Minimum interval: {minDonationDays} days</small>}</div>
            </div>
            <div className="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <section className="mc-card p-4"><Info label="Phone" value={donor.phone} /><Info label="Email" value={donor.email} /><Info label="Gender" value={donor.gender} /><Info label="Date of birth" value={donor.dateOfBirth} /></section>
                <section className="mc-card p-4"><Info label="Address" value={donor.address} /><Info label="Last donation" value={donor.lastDonationDate || 'Never'} /><Info label="Notes" value={donor.notes} /><Info label="Status" value={donor.status ? 'Active' : 'Inactive'} /></section>
            </div>
            <section className="mc-card">
                <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Donation history</h2></div>
                <div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>#</th><th>Donation date</th><th>Bag #</th><th>Quantity</th><th>Expiry</th><th>Status</th></tr></thead>
                    <tbody>{donor.donations.length ? donor.donations.map((donation, index) => <tr key={donation.id}><td className="mc-idx">{index + 1}</td><td>{donation.date}</td><td>{donation.bagNumber || '—'}</td><td>{donation.quantity} {donation.unit}</td><td>{donation.expiryDate}</td><td><span className={`mc-pill ${statusPill[donation.status] || ''}`}>{donation.status}</span></td></tr>) : <tr><td colSpan="6"><div className="mc-empty"><b>No donations</b>This donor has no recorded donation history.</div></td></tr>}</tbody>
                </table></div>
            </section>
        </AdminLayout>
    );
}

function Card({ label, value }) {
    return <div className="mc-card p-4"><p className="mb-1 text-sm font-semibold text-mut">{label}</p><p className="mb-0 text-lg font-bold">{value}</p></div>;
}

function Info({ label, value }) {
    return <div className="mb-3 last:mb-0"><p className="mb-1 text-xs font-semibold text-mut">{label}</p><p className="mb-0 whitespace-pre-wrap">{value || '—'}</p></div>;
}
