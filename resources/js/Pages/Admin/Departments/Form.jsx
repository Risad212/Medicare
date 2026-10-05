import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function DepartmentForm({ mode, department = null, routes }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        name: department?.name ?? '',
        description: department?.description ?? '',
        status: department ? department.status : true,
    });
    const initials = (department?.name || 'Department').split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();

    function submit(event) {
        event.preventDefault();
        form.transform(({ status, ...data }) => status ? { ...data, status: '1' } : data);
        if (isEdit) {
            form.put(routes.update);
        } else {
            form.post(routes.store);
        }
    }

    return (
        <AdminLayout title={isEdit ? 'Edit department' : 'Add department'} active="departments" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Structure</p><h1 className="mc-title">{isEdit ? <>Edit depart<em>ment</em></> : <>New depart<em>ment</em></>}</h1>
                <p className="mc-sub">{isEdit ? 'Rename it, reword it, or take it offline.' : 'A clinical unit: name it, describe it, switch it on.'}</p></div></div>
            <form onSubmit={submit} noValidate>
                <div className="mc-grid">
                    <aside className="mc-side"><div className="mc-avbig">{initials}</div><div className="k">{isEdit ? 'Currently editing' : 'New record'}</div>
                        <h2>{department?.name || 'Unsaved unit'}</h2><p>{isEdit ? (department.status ? 'Active on the site' : 'Hidden from the site') : 'Fill the profile — it appears on the site once active.'}</p>
                        <ul className="mc-steps"><li><span className="n">1</span>Profile</li><li><span className="n">2</span>Visibility</li></ul>
                    </aside>
                    <div>
                        {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the highlighted fields and try again.</div>}
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">01</span><h3>Profile</h3><p>Name and description.</p></div>
                            <div className="mc-sec-bd">
                                <div className="mc-f full"><label htmlFor="department-name">Name <i className="req">*</i></label>
                                    <input id="department-name" type="text" className={`${inputClass} ${form.errors.name ? 'border-red-500' : ''}`} value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required aria-invalid={Boolean(form.errors.name)} aria-describedby={form.errors.name ? 'department-name-error' : undefined} />
                                    {form.errors.name && <p id="department-name-error" role="alert" className="mt-1 text-xs text-red-t">{form.errors.name}</p>}
                                </div>
                                <div className="mc-f full"><label htmlFor="department-description">Description</label>
                                    <textarea id="department-description" className={`${inputClass} ${form.errors.description ? 'border-red-500' : ''}`} rows="4" value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} aria-invalid={Boolean(form.errors.description)} aria-describedby={form.errors.description ? 'department-description-error' : undefined} />
                                    {form.errors.description && <p id="department-description-error" role="alert" className="mt-1 text-xs text-red-t">{form.errors.description}</p>}
                                </div>
                            </div>
                        </section>
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">02</span><h3>Visibility</h3><p>Whether it appears on the site.</p></div>
                            <div className="mc-sec-bd"><div className="mc-f"><span className="mb-2 block text-sm font-semibold">Status</span>
                                <label className="mc-check"><input type="checkbox" checked={form.data.status} onChange={(event) => form.setData('status', event.target.checked)} /> Active</label>
                            </div></div>
                        </section>
                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update department' : 'Save department'}</button>
                            <a href={routes.index} className="mc-btn ghost">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
