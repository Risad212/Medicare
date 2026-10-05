import AdminLayout from '../../../Components/AdminLayout';

const statusStyles = {
    paid: ['Paid', 'p-paid'],
    void: ['Void', 'p-void'],
    pending: ['Pending', 'p-pending'],
};

export default function InvoicesIndex({ invoices, filters, features, routes }) {
    return (
        <AdminLayout title="Invoices" active="invoices" routes={routes} features={features}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Billing</p>
                    <h1 className="mc-title">In<em>voices</em></h1>
                    <p className="mc-sub">Invoices with status, patient, and billing details.</p>
                </div>
            </div>

            <div className="mc-bar">
                <form method="get" action={routes.index} className="flex flex-1 flex-wrap items-center gap-2.5">
                    <div className="mc-search min-h-[42px] min-w-[200px] flex-1">
                        <i aria-hidden="true" className="bi bi-search text-faint" />
                        <label htmlFor="invoice-search" className="sr-only">Search invoice number or patient</label>
                        <input id="invoice-search" type="search" name="search" defaultValue={filters.search} placeholder="Invoice number or patient…" autoComplete="off" />
                    </div>
                    <label htmlFor="invoice-status" className="sr-only">Filter by invoice status</label>
                    <select id="invoice-status" name="status" defaultValue={filters.status} className="h-[42px] min-w-[130px] rounded-lg border border-line bg-white px-2.5 text-[13px] text-ink outline-none focus:border-teal">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="void">Void</option>
                    </select>
                    <button type="submit" className="mc-btn sm h-[42px]">Filter</button>
                    {(filters.search || filters.status) && <a href={routes.index} className="mc-btn sm ghost h-[42px]">Reset</a>}
                </form>
            </div>

            <div className="mc-ecg"><span>Billing register</span><span>{invoices.total} invoices</span></div>
            <section className="mc-card" aria-label="Invoices">
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>Invoice no.</th><th>Patient</th><th>Lab order</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                        <tbody>
                            {invoices.data.length ? invoices.data.map((invoice) => {
                                const [label, style] = statusStyles[invoice.status] || statusStyles.pending;
                                return (
                                    <tr key={invoice.id}>
                                        <td className="mc-num"><b>{invoice.number}</b></td>
                                        <td>{invoice.patientName}</td>
                                        <td>{invoice.orderId && routes.labOrderBase
                                            ? <a href={`${routes.labOrderBase}/${invoice.orderId}`} className="font-semibold text-teal-dk hover:underline">#{invoice.orderId}</a>
                                            : 'N/A'}</td>
                                        <td className="mc-num">${invoice.total}</td>
                                        <td><span className={`mc-pill ${style}`}><i />{label}</span></td>
                                        <td className="mc-num">{invoice.createdAt || '—'}</td>
                                        <td>
                                            <div className="mc-acts">
                                                <a href={`${routes.showBase}/${invoice.id}`} className="mc-btn sm"><i aria-hidden="true" className="bi bi-eye" /> View</a>
                                                <a href={`${routes.pdfBase}/${invoice.id}/pdf`} className="mc-btn sm ghost" target="_blank" rel="noreferrer"><i aria-hidden="true" className="bi bi-file-earmark-pdf" /> PDF</a>
                                            </div>
                                        </td>
                                    </tr>
                                );
                            }) : <tr><td colSpan="7"><div className="mc-empty"><b>No invoices found</b>Adjust the filters, or generate an invoice from a completed lab order.</div></td></tr>}
                        </tbody>
                    </table>
                </div>
                <Pagination page={invoices} />
            </section>
        </AdminLayout>
    );
}

function Pagination({ page }) {
    return (
        <div className="mc-pg">
            <span>Showing {page.firstItem || 0}–{page.lastItem || 0} of {page.total}</span>
            <nav aria-label="Invoice pages" className="flex items-center gap-1">
                {page.previousPageUrl ? <a className="page-link" href={page.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                <span className="px-2 text-[11px] text-mut">Page {page.currentPage} of {page.lastPage}</span>
                {page.nextPageUrl ? <a className="page-link" href={page.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
            </nav>
        </div>
    );
}
