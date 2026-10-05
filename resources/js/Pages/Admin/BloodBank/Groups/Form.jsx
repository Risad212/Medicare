import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function BloodGroupForm({ mode, bloodGroup = null, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({ name: bloodGroup?.name ?? '', status: bloodGroup ? bloodGroup.status : true });

    function submit(event) {
        event.preventDefault();
        const data = form.data.status ? { name: form.data.name, status: '1' } : { name: form.data.name };
        if (isEdit) form.transform(() => data).put(routes.update);
        else form.transform(() => data).post(routes.store);
    }

    return (
        <AdminLayout title={`${isEdit ? 'Edit' : 'New'} blood group`} active="blood-groups" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">{isEdit ? <>Edit blood <em>group</em></> : <>New blood <em>group</em></>}</h1><p className="mc-sub">Register a type such as O+ or AB- to label donations.</p></div></div>
            <section className="mc-card max-w-3xl">
                <form onSubmit={submit} className="p-4.5">
                    {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted fields.</div>}
                    <div className="mb-4"><label htmlFor="blood-group-name" className="mb-1.5 block text-xs font-bold text-ink-2">Name <span aria-hidden="true">*</span></label><input id="blood-group-name" className={inputClass} value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} maxLength="10" placeholder="e.g. O+" required />{form.errors.name && <p role="alert" className="mt-1 text-xs text-red-t">{form.errors.name}</p>}</div>
                    <div className="mb-5"><label className="mc-check"><input type="checkbox" checked={form.data.status} onChange={(event) => form.setData('status', event.target.checked)} /><span>Active</span></label></div>
                    <div className="mc-formacts"><button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : `${isEdit ? 'Update' : 'Save'} blood group`}</button><a href={routes.index} className="mc-btn ghost">Cancel</a></div>
                </form>
            </section>
        </AdminLayout>
    );
}
