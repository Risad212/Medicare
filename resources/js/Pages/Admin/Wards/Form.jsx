import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function WardForm({ mode, ward = null, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({ name: ward?.name ?? '', description: ward?.description ?? '' });

    function submit(event) {
        event.preventDefault();
        if (isEdit) form.put(routes.update);
        else form.post(routes.store);
    }

    return (
        <AdminLayout title={isEdit ? 'Edit ward' : 'Add ward'} active="beds" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · In-patient</p><h1 className="mc-title">{isEdit ? <>Edit <em>ward</em></> : <>New <em>ward</em></>}</h1><p className="mc-sub">Manage ward details and capacity grouping.</p></div></div>
            <section className="mc-card max-w-3xl">
                <div className="border-b border-line-2 px-4.5 py-3"><h2 className="text-[15px] font-bold">Ward details</h2></div>
                <form onSubmit={submit} className="p-4.5">
                    {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                    <div className="mb-4"><label htmlFor="ward-name" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Name <span aria-hidden="true">*</span></label><input id="ward-name" className={inputClass} value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="e.g. General Ward, ICU" required />{form.errors.name && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.name}</p>}</div>
                    <div className="mb-4"><label htmlFor="ward-description" className="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Description</label><textarea id="ward-description" className={inputClass} rows="3" value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Floor, capacity notes..." />{form.errors.description && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.description}</p>}</div>
                    <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Save changes' : 'Create ward'}</button><a href={routes.index} className="mc-btn ghost">Cancel</a></div>
                </form>
            </section>
        </AdminLayout>
    );
}
