import AdminLayout from '../../../../Components/AdminLayout';

const numberFormat = new Intl.NumberFormat();

export default function BloodBankReports({ donationStats, issuedByGroup, monthly, groupStats, stats, from, to, routes }) {
    const query = `?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
    const groups = Object.fromEntries(groupStats.map((group) => [group.id, group.name]));

    return (
        <AdminLayout title="Blood reports" active="blood-reports" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Blood <em>reports</em></h1><p className="mc-sub">Donation, request and issue statistics — exportable to CSV.</p></div></div>
            <section className="mc-card mb-4 p-4">
                <form action={routes.index} method="get" className="flex flex-wrap items-end gap-3">
                    <div><label htmlFor="report-from" className="mb-1 block text-xs font-medium text-mut">From</label><input id="report-from" type="date" name="from" className="rounded-lg border border-line bg-white px-3 py-2 text-sm" defaultValue={from} /></div>
                    <div><label htmlFor="report-to" className="mb-1 block text-xs font-medium text-mut">To</label><input id="report-to" type="date" name="to" className="rounded-lg border border-line bg-white px-3 py-2 text-sm" defaultValue={to} /></div>
                    <button type="submit" className="mc-btn sm">Filter</button>
                    <div className="ml-auto flex flex-wrap gap-2">
                        <a href={`${routes.exportDonations}${query}`} className="mc-btn sm ghost">Donations CSV</a>
                        <a href={`${routes.exportRequests}${query}`} className="mc-btn sm ghost">Requests CSV</a>
                        <a href={`${routes.exportIssues}${query}`} className="mc-btn sm ghost">Issues CSV</a>
                    </div>
                </form>
            </section>
            <div className="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Stat label="Donated" value={`${numberFormat.format(stats.periodDonations)} ml`} />
                <Stat label="Issued" value={`${numberFormat.format(stats.periodIssued)} ml`} />
                <Stat label="Requests created" value={numberFormat.format(stats.periodRequestsCreated)} />
                <Stat label="Requests fulfilled" value={numberFormat.format(stats.periodRequestsFulfilled)} />
            </div>
            <section className="mc-card mb-4"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Last 6 months</h2></div><div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Month</th><th>Donated (ml)</th><th>Issued (ml)</th></tr></thead>
                <tbody>{monthly.map((month) => <tr key={month.label}><td><b>{month.label}</b></td><td>{numberFormat.format(month.donations)}</td><td>{numberFormat.format(month.issued)}</td></tr>)}</tbody>
            </table></div></section>
            <section className="mc-card mb-4"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Group-wise inventory</h2></div><div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Group</th><th>Currently available (ml)</th><th>Donations</th><th>Issues</th></tr></thead>
                <tbody>{groupStats.length ? groupStats.map((group) => <tr key={group.id}><td><span className="mc-av r sm">{group.name}</span></td><td>{numberFormat.format(group.availableQuantity)}</td><td>{numberFormat.format(group.donationCount)}</td><td>{numberFormat.format(group.issuedCount)}</td></tr>) : <Empty colSpan={4} title="No data" text="No blood groups recorded." />}</tbody>
            </table></div></section>
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Issued by group ({from} – {to})</h2></div><div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Group</th><th>Issued (ml)</th></tr></thead>
                    <tbody>{issuedByGroup.length ? issuedByGroup.map((row) => <tr key={row.group}><td><span className="mc-av r sm">{row.group}</span></td><td>{numberFormat.format(row.quantity)}</td></tr>) : <Empty colSpan={2} title="Nothing issued" text="No blood issued in this period." />}</tbody>
                </table></div></section>
                <section className="mc-card"><div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Donation status breakdown</h2></div><div className="overflow-x-auto"><table className="mc-tbl"><thead><tr><th>Group</th><th>Status</th><th>Total (ml)</th></tr></thead>
                    <tbody>{donationStats.length ? donationStats.map((row, index) => <tr key={`${row.bloodGroupId}-${row.status}-${index}`}><td>{groups[row.bloodGroupId] || 'Unknown'}</td><td>{capitalize(row.status)}</td><td>{numberFormat.format(row.totalQuantity)}</td></tr>) : <Empty colSpan={3} title="No donations" text="Nothing collected in this period." />}</tbody>
                </table></div></section>
            </div>
        </AdminLayout>
    );
}

function Stat({ label, value }) {
    return <div className="mc-card p-4"><p className="mb-1 text-sm text-mut">{label}</p><p className="mb-0 font-display text-2xl font-bold">{value}</p></div>;
}
function Empty({ colSpan, title, text }) {
    return <tr><td colSpan={colSpan}><div className="mc-empty"><b>{title}</b>{text}</div></td></tr>;
}
function capitalize(value) { return value ? value.charAt(0).toUpperCase() + value.slice(1) : 'Unknown'; }
