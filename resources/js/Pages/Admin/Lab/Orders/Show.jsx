import { router, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const statuses = {
    pending: ['Pending', 'p-pending'],
    'in-progress': ['In progress', 'p-info'],
    completed: ['Completed', 'p-completed'],
    cancelled: ['Cancelled', 'p-cancelled'],
};

function InfoRow({ label, value }) {
    return <tr><th className="w-1/4 font-bold text-ink-2">{label}</th><td>{value || 'N/A'}</td></tr>;
}

function ResultForm({ item, baseUrl }) {
    const form = useForm({ result: item.result ?? '' });
    function submit(event) {
        event.preventDefault();
        form.put(`${baseUrl}/${item.id}/result`, { preserveScroll: true });
    }
    return (
        <form onSubmit={submit} className="mt-1 flex flex-wrap items-start gap-2">
            <label htmlFor={`lab-result-${item.id}`} className="sr-only">Result for {item.testName}</label>
            <input id={`lab-result-${item.id}`} type="text" value={form.data.result} onChange={(event) => form.setData('result', event.target.value)} className="max-w-[200px] rounded-lg border border-line bg-white px-3 py-2 text-[13px] text-ink outline-none focus:border-teal" placeholder="Enter reading…" aria-invalid={Boolean(form.errors.result)} />
            <button type="submit" className="mc-btn sm" disabled={form.processing}>{form.processing ? 'Saving…' : 'Save'}</button>
            {form.errors.result && <span role="alert" className="w-full text-xs text-red-t">{form.errors.result}</span>}
        </form>
    );
}

function ReportForm({ orderId, action }) {
    const form = useForm({ report_name: '', report_file: null, notes: '' });
    function submit(event) {
        event.preventDefault();
        form.post(action, { forceFormData: true, preserveScroll: true, onSuccess: () => form.reset() });
    }
    return (
        <form onSubmit={submit}>
            {form.hasErrors && <div role="alert" className="mb-3 rounded-lg bg-red-bg px-3 py-2 text-xs text-red-t">Please correct the report fields and try again.</div>}
            <div className="mb-4">
                <label htmlFor={`report-name-${orderId}`} className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Report name <span className="text-red-600">*</span></label>
                <input id={`report-name-${orderId}`} value={form.data.report_name} onChange={(event) => form.setData('report_name', event.target.value)} className="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-teal" required maxLength="255" />
                {form.errors.report_name && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.report_name}</p>}
            </div>
            <div className="mb-4">
                <label htmlFor={`report-file-${orderId}`} className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">File (PDF / image) <span className="text-red-600">*</span></label>
                <input id={`report-file-${orderId}`} type="file" accept=".pdf,.jpg,.jpeg,.png" onChange={(event) => form.setData('report_file', event.target.files?.[0] ?? null)} className="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-teal" required />
                {form.errors.report_file && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.report_file}</p>}
            </div>
            <div className="mb-4">
                <label htmlFor={`report-notes-${orderId}`} className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Notes</label>
                <textarea id={`report-notes-${orderId}`} value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)} rows="2" className="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm outline-none focus:border-teal" maxLength="2000" />
                {form.errors.notes && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.notes}</p>}
            </div>
            <button type="submit" className="mc-btn" disabled={form.processing}><i aria-hidden="true" className="bi bi-cloud-upload" /> {form.processing ? 'Uploading…' : 'Upload report'}</button>
        </form>
    );
}

export default function LabOrderShow({ order, routes }) {
    const statusForm = useForm({ status: order.status });
    const [label, style] = statuses[order.status] || statuses.cancelled;

    function updateStatus(event) {
        event.preventDefault();
        statusForm.put(routes.status, { preserveScroll: true });
    }

    function createInvoice() {
        router.post(routes.invoiceCreate);
    }

    function deleteReport(report) {
        if (window.confirm(`Delete report "${report.name}"?`)) {
            router.delete(`${routes.reportDeleteBase}/${report.id}`);
        }
    }

    return (
        <AdminLayout title={`Lab order #${order.id}`} active="laboratory" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Laboratory</p><h1 className="mc-title">Lab order <em>#{order.id}</em></h1><p className="mc-sub">{order.patientName} — tests, results, and reports.</p></div>
                <div className="mc-head-acts">
                    {order.status === 'completed' && (order.invoiceId
                        ? <a href={`${routes.invoiceShowBase}/${order.invoiceId}`} className="mc-btn"><i aria-hidden="true" className="bi bi-receipt" /> Invoice {order.invoiceNumber}</a>
                        : <button type="button" onClick={createInvoice} className="mc-btn"><i aria-hidden="true" className="bi bi-receipt" /> Create invoice</button>)}
                    <a href={routes.pdf} className="mc-btn ghost" target="_blank" rel="noreferrer"><i aria-hidden="true" className="bi bi-file-earmark-pdf" /> Report PDF</a>
                    <a href={routes.index} className="mc-btn ghost">Back to list</a>
                </div>
            </div>
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section className="mc-card">
                    <div className="border-b border-line-2 px-4 py-3"><h2 className="m-0 text-[15px] font-bold">Patient &amp; order</h2></div>
                    <div className="overflow-x-auto"><table className="mc-tbl"><tbody>
                        <InfoRow label="Patient" value={order.patientName} /><InfoRow label="Phone" value={order.phone} /><InfoRow label="Email" value={order.email} />
                        <InfoRow label="Account" value={order.accountName} /><InfoRow label="Requested by" value={`Dr. ${order.doctorName}`} />
                        <InfoRow label="Appointment" value={order.appointmentId ? `#${order.appointmentId} — ${order.appointmentDate || ''}` : 'N/A'} />
                        <InfoRow label="Created" value={order.createdAt} /><InfoRow label="Priority" value={order.priority === 'urgent' ? <span className="mc-pill p-urgent"><i />Urgent</span> : <span className="mc-pill p-cancelled">Normal</span>} />
                    </tbody></table></div>
                </section>
                <section className="mc-card">
                    <div className="border-b border-line-2 px-4 py-3"><h2 className="m-0 text-[15px] font-bold">Status</h2></div>
                    <div className="p-4">
                        <p className="mb-3 text-sm text-mut">Current: <span className={`mc-pill ${style}`}><i />{label}</span></p>
                        {statusForm.errors.status && <p role="alert" className="mb-2 text-xs text-red-t">{statusForm.errors.status}</p>}
                        <form onSubmit={updateStatus} className="flex flex-wrap items-center gap-2">
                            <label htmlFor="lab-order-new-status" className="sr-only">New order status</label>
                            <select id="lab-order-new-status" value={statusForm.data.status} onChange={(event) => statusForm.setData('status', event.target.value)} className="max-w-[220px] rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal">
                                <option value="pending">Pending</option><option value="in-progress">In progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option>
                            </select>
                            <button type="submit" className="mc-btn sm" disabled={statusForm.processing}>{statusForm.processing ? 'Updating…' : 'Update'}</button>
                        </form>
                    </div>
                </section>
            </div>

            <section className="mc-card mt-4">
                <div className="border-b border-line-2 px-4 py-3"><h2 className="m-0 text-[15px] font-bold">Tests &amp; results</h2></div>
                <div className="overflow-x-auto">
                    <table className="mc-tbl">
                        <thead><tr><th>Test</th><th>Normal range</th><th>Unit</th><th>Result</th><th>Price</th></tr></thead>
                        <tbody>{order.items.map((item) => (
                            <tr key={item.id}>
                                <td><b className="text-ink">{item.testName}</b></td><td>{item.normalRange || 'N/A'}</td><td>{item.unit || 'N/A'}</td>
                                <td>{item.result ? <span className="mc-pill p-active"><i />{item.result}</span> : <span className="text-sm text-mut">Not entered</span>}<ResultForm item={item} baseUrl={routes.itemResultBase} /></td>
                                <td className="mc-num">${item.price}</td>
                            </tr>
                        ))}</tbody>
                        <tfoot><tr className="border-t border-line font-bold"><td colSpan="3" className="text-right text-ink-2">Total</td><td className="mc-num">${order.total}</td><td /></tr></tfoot>
                    </table>
                </div>
                {order.note && <div className="border-t border-line-2 px-4 py-3"><h3 className="mb-1 text-xs font-bold uppercase tracking-wide text-ink-2">Doctor note</h3><p className="text-sm text-mut">{order.note}</p></div>}
            </section>

            <div className="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-5">
                <section className="mc-card lg:col-span-2">
                    <div className="border-b border-line-2 px-4 py-3"><h2 className="m-0 text-[15px] font-bold">Upload report</h2></div>
                    <div className="p-4"><ReportForm orderId={order.id} action={routes.reportsStore} /></div>
                </section>
                <section className="mc-card lg:col-span-3">
                    <div className="border-b border-line-2 px-4 py-3"><h2 className="m-0 text-[15px] font-bold">Uploaded reports ({order.reports.length})</h2></div>
                    <div>
                        {order.reports.length ? order.reports.map((report) => (
                            <div key={report.id} className="flex flex-wrap items-center justify-between gap-3 border-b border-line-2 px-4 py-3 last:border-b-0">
                                <div><b className="text-ink">{report.name}</b><div className="text-xs text-mut">Uploaded {report.createdAt}{report.uploaderName && ` by ${report.uploaderName}`}{report.notes && <><br />{report.notes}</>}</div></div>
                                <div className="mc-acts">
                                    <a href={`${routes.reportDownloadBase}/${report.id}/download`} className="mc-btn sm" aria-label={`Download ${report.name}`}><i aria-hidden="true" className="bi bi-download" /></a>
                                    <button type="button" onClick={() => deleteReport(report)} className="mc-btn sm danger-ghost" aria-label={`Delete ${report.name}`}><i aria-hidden="true" className="bi bi-trash" /></button>
                                </div>
                            </div>
                        )) : <div className="p-4 text-sm text-mut">No reports uploaded yet.</div>}
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
