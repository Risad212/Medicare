import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15';

export default function TaxonomyForm({ kind, record = null, routes }) {
    const isCategories = kind === 'categories';
    const label = isCategories ? 'category' : 'tag';
    const form = useForm({ name: record?.name ?? '' });

    function submit(event) {
        event.preventDefault();
        if (record) form.put(routes.update);
        else form.post(routes.store);
    }

    return (
        <AdminLayout title={`${record ? 'Edit' : 'New'} ${label}`} active={kind} routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Taxonomy</p><h1 className="mc-title">{record ? <>Edit <em>{label}</em></> : <>New <em>{label}</em></>}</h1>
                <p className="mc-sub">{record ? 'Rename or keep it as-is.' : `A ${isCategories ? 'topic group' : 'keyword'} for blog posts.`}</p></div></div>
            <form onSubmit={submit}>
                <div className="mc-grid">
                    <aside className="mc-side"><div className="mc-avbig">{isCategories ? 'CA' : 'TG'}</div><div className="k">{record ? 'Currently editing' : 'New record'}</div>
                        <h2>{record?.name || `Untitled ${label}`}</h2><p>{isCategories ? 'Give it a clear, short name.' : 'A short keyword — keep it concise.'}</p>
                        <ul className="mc-steps"><li><span className="n">1</span>Profile</li></ul>
                    </aside>
                    <div>
                        {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please correct the highlighted field.</div>}
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">01</span><h3>Profile</h3><p>Name the {label}.</p></div>
                            <div className="mc-sec-bd"><div className="mc-f full">
                                <label htmlFor={`${kind}-name`}>Name <i className="req">*</i></label>
                                <input id={`${kind}-name`} className={`${inputClass} ${form.errors.name ? 'border-red-500' : ''}`} value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} required aria-invalid={Boolean(form.errors.name)} aria-describedby={form.errors.name ? `${kind}-name-error` : undefined} />
                                {form.errors.name && <p id={`${kind}-name-error`} role="alert" className="mt-1 text-xs text-red-t">{form.errors.name}</p>}
                            </div></div>
                        </section>
                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : `${record ? 'Update' : 'Save'} ${label}`}</button>
                            <a href={routes.index} className="mc-btn ghost">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
