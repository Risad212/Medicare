import { router, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const statusStyles = {
    paid: ['Paid', 'p-paid'],
    void: ['Void', 'p-void'],
    pending: ['Pending', 'p-pending'],
};

function Detail({ label, children }) {
    return <tr><th className="w-1/4 font-bold text-ink-2">{label}</th><td>{children || 'N/A'}</td></tr>;
}

export default function InvoiceShow({ invoice, routes }) {
    const statusForm = useForm({ status: invoice.status });
    const [label, style] = statusStyles[invoice.status] || statusStyles.pending;

    function updateStatus(event) {
        event.preventDefault();
        statusForm.patch(routes.status, { preserveScroll: true });
    }

    function deleteInvoice() {
        if (window.confirm('Delete this invoice permanently?')) {
            router.delete(routes.delete);
        }
    }

    return (
        <AdminLayout title={`Invoice ${invoice.number}`} active="invoices" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Billing</p>
                    <h1 className="mc-title">Invoice <em>{invoice.number}</em></h1>
                    <p className="mc-sub">Full invoice breakdown and payment status.</p>
                </div>
                <div className="mc-head-acts">
                    <a href={routes.pdf} className="mc-btn ghost" target="_blank" rel="noreferrer"><i aria-hidden="true" className="bi bi-file-earmark-pdf" /> PDF</a>
                    <a href={routes.index} className="mc-btn ghost">Back to list</a>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section className="mc-card">
                    <div className="border-b border-line-2 px-4 py-3"><h2 className="m-0 text-[15px] font-bold">Invoice</h2></div>
                    <div className="overflow-x-auto">
                        <table className="mc-tbl"><tbody>
                            <Detail label="Invoice no.">{invoice.number}</Detail>
                            <Detail label="Patient">{invoice.patientName}</Detail>
                            <Detail label="Phone">{invoice.phone}</Detail>
                            <Detail label="Email">{invoice.email}</Detail>
                            <Detail label="Lab order">{invoice.orderId ? <>{routes.labOrder && <a href={routes.labOrder} className="font-semibold text-teal-dk hover:underline">#{invoice.orderId}</a>}{invoice.orderDoctor ? ` by Dr. ${invoice.orderDoctor}` : ''}</> : null}</Detail>
                            <Detail label="Created">{invoice.createdAt} by {invoice.createdBy}</Detail>
                            {invoice.paidAt && <Detail label="Paid on">{invoice.paidAt}</Detail>}
                        </tbody></table>
                    </div>
                </section>

                <section className="mc-card">
                    <div className="border-b border-line-2 px-4 py-3"><h2 className="m-0 text-[15px] font-bold">Status</h2></div>
                    <div className="p-4">
                        <p className="mb-3 text-sm text-mut">Current: <span className={`mc-pill ${style}`}><i />{label}</span></p>
                        {statusForm.errors.status && <p role="alert" className="text-xs text-red-t">{statusForm.errors.status}</p>}
                        <form onSubmit={updateStatus} className="mb-4 flex flex-wrap items-center gap-2">
                            <label htmlFor="invoice-new-status" className="sr-only">Invoice status</label>
                            <select id="invoice-new-status" value={statusForm.data.status} onChange={(event) => statusForm.setData('status', event.target.value)} className="max-w-[200px] rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal">
                                <option value="pending">Pending</option><option value="paid">Paid</option><option value="void">Void</option>
                            </select>
                            <button type="submit" className="mc-btn sm" disabled={statusForm.processing}>{statusForm.processing ? 'Updating…' : 'Update'}</button>
                        </form>
                        <hr className="mb-4 border-line-2" />
                        <button type="button" onClick={deleteInvoice} className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete invoice</button>
                    </div>
                </section>
            </div>

            <section className="mc-card mt-4">
                <div className="border-b border-line-2 px-4 py-3"><h2 className="m-0 text-[15px] font-bold">Items</h2></div>
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Description</th><th className="text-right">Qty</th><th className="text-right">Unit price</th><th className="text-right">Amount</th></tr></thead>
                        <tbody>
                            {invoice.items.length ? invoice.items.map((item, index) => (
                                <tr key={item.id}><td className="mc-idx">{index + 1}</td><td>{item.description}</td><td className="mc-num text-right">{item.quantity}</td><td className="mc-num text-right">${item.unitPrice}</td><td className="mc-num text-right">${item.lineTotal}</td></tr>
                            )) : <tr><td colSpan="5" className="py-6 text-center text-mut">No line items recorded for this invoice.</td></tr>}
                        </tbody>
                        <tfoot>
                            <tr className="border-t border-line font-bold"><td colSpan="4" className="text-right text-ink-2">Subtotal</td><td className="mc-num text-right">${invoice.subtotal}</td></tr>
                            <tr className="font-bold"><td colSpan="4" className="text-right text-ink-2">Tax</td><td className="mc-num text-right">${invoice.tax}</td></tr>
                            <tr className="font-bold"><td colSpan="4" className="text-right text-ink-2">Discount</td><td className="mc-num text-right">-${invoice.discount}</td></tr>
                            <tr className="font-bold"><td colSpan="4" className="text-right text-ink-2">Total</td><td className="mc-num text-right">${invoice.total}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
