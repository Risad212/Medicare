import AdminLayout from '../../../Components/AdminLayout';

const statusPill = {
    available: 'p-active',
    low: 'p-low',
    out: 'p-cancelled',
    pending: 'p-pending',
    fulfilled: 'p-active',
    approved: 'p-active',
    partially_approved: 'p-progress',
    rejected: 'p-inactive',
    cancelled: 'p-inactive',
};

export default function BloodBankDashboard({ totalDonors, totalDonations, availableQty, pendingRequests, approvedRequests, emergencyRequests, issuedUnits, expiredUnits, inventory, lowStock, recentDonations, recentRequests, emergencies, settings, routes }) {
    return (
        <AdminLayout title="Blood bank dashboard" active="blood-bank" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Blood bank <em>dashboard</em></h1><p className="mc-sub">Stock at a glance, urgent needs, and recent activity.</p></div>
                <div className="mc-head-acts"><a href={routes.inventory} className="mc-btn">Inventory</a><a href={routes.donationCreate} className="mc-btn ghost">Record donation</a></div>
            </div>
            <div className="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <Stat label="Total donors" value={totalDonors} icon="people" />
                <Stat label="Total donations" value={totalDonations} icon="droplet" />
                <Stat label="Available stock" value={`${availableQty} ml`} icon="box-seam" />
                <Stat label="Pending requests" value={pendingRequests} icon="clipboard-plus" detail={`${approvedRequests} approved · ${emergencyRequests} emergencies`} />
                <Stat label="Issued units" value={issuedUnits} icon="eyedropper" />
                <Stat label="Expired units" value={expiredUnits} icon="calendar-x" />
            </div>
            {emergencies.length > 0 && (
                <section className="mc-card mb-4 border border-red-200">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold text-red">Emergency requests</h2></div>
                    <div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Patient</th><th>Group</th><th>Qty</th><th>Required</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>{emergencies.map((request) => <tr key={request.id}><td><b>{request.patient}</b></td><td>{request.group}</td><td>{request.quantity} {request.unit}</td><td>{request.requiredDate}</td><td><span className={`mc-pill ${statusPill[request.status] || ''}`}>{formatStatus(request.status)}</span></td><td><a href={`${routes.requestShowBase}/${request.id}`} className="mc-btn sm">Review</a></td></tr>)}</tbody>
                    </table></div>
                </section>
            )}
            <div className="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <section className="mc-card lg:col-span-2">
                    <div className="flex items-center justify-between border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Stock by group</h2><span className="text-xs text-mut">Low-stock threshold: {settings.lowStockThreshold}</span></div>
                    <div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Group</th><th>Available</th><th>Reserved</th><th>Issued</th><th>Expired</th><th>Status</th></tr></thead>
                        <tbody>{inventory.length ? inventory.map((row) => <tr key={row.group}><td><b>{row.group}</b></td><td>{row.units.available} units <span className="text-xs text-mut">({row.quantity.available} ml)</span></td><td>{row.units.reserved}</td><td>{row.units.issued}</td><td>{row.units.expired}</td><td><span className={`mc-pill ${statusPill[row.status] || ''}`}>{formatStatus(row.status)}</span></td></tr>) : <Empty colSpan={6} title="Nothing on this chart" text="No blood groups defined yet." />}</tbody>
                    </table></div>
                </section>
                <section className="mc-card">
                    <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Low stock</h2></div>
                    {lowStock.length ? lowStock.map((row) => <div key={row.group} className="flex items-center justify-between border-b border-line px-4 py-3 last:border-b-0"><b>{row.group}</b><span>{row.units} available</span><span className={`mc-pill ${statusPill[row.status]}`}>{row.status === 'low' ? 'Low' : 'Out'}</span></div>) : <p className="p-4 text-sm text-mut">All groups are sufficiently stocked.</p>}
                </section>
            </div>
            <section className="mc-card mb-4">
                <div className="flex items-center justify-between border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Recent requests</h2><a href={routes.requests} className="mc-btn sm">All requests</a></div>
                <div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Patient</th><th>Group</th><th>Qty</th><th>Urgency</th><th>Required</th><th>Status</th></tr></thead>
                    <tbody>{recentRequests.length ? recentRequests.map((request) => <tr key={request.id}><td><b>{request.patient}</b></td><td>{request.group}</td><td>{request.quantity} ml</td><td>{request.urgency}</td><td>{request.requiredDate}</td><td><span className={`mc-pill ${statusPill[request.status] || ''}`}>{formatStatus(request.status)}</span></td></tr>) : <Empty colSpan={6} title="No requests" text="No blood requests yet." />}</tbody>
                </table></div>
            </section>
            <section className="mc-card">
                <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Recent donations</h2></div>
                <div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Donor</th><th>Group</th><th>Date</th><th>Quantity</th><th>Status</th></tr></thead>
                    <tbody>{recentDonations.length ? recentDonations.map((donation) => <tr key={donation.id}><td>{donation.donor}</td><td>{donation.group}</td><td>{donation.date}</td><td>{donation.quantity} ml</td><td><span className={`mc-pill ${statusPill[donation.status] || ''}`}>{formatStatus(donation.status)}</span></td></tr>) : <Empty colSpan={5} title="No donations" text="No donations yet." />}</tbody>
                </table></div>
            </section>
        </AdminLayout>
    );
}

function Stat({ label, value, icon, detail }) {
    return <div className="mc-card p-4"><div className="flex items-start justify-between"><div><p className="mb-1 text-sm text-mut">{label}</p><p className="mb-0 font-display text-2xl font-bold">{value}</p>{detail && <span className="text-xs text-mut">{detail}</span>}</div><i aria-hidden="true" className={`bi bi-${icon} text-2xl text-bright`} /></div></div>;
}

function Empty({ colSpan, title, text }) {
    return <tr><td colSpan={colSpan}><div className="mc-empty"><b>{title}</b>{text}</div></td></tr>;
}

function formatStatus(status) {
    return status.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}
