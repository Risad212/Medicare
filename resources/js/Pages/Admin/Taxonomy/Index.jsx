import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function TaxonomyIndex({ kind, records, routes }) {
    const { csrfToken } = usePage().props;
    const isCategories = kind === 'categories';
    const label = isCategories ? 'Categories' : 'Tags';
    const active = kind;

    return (
        <AdminLayout title={label} active={active} routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Taxonomy</p><h1 className="mc-title">All <em>{label}</em></h1>
                    <p className="mc-sub">{isCategories ? 'Group blog posts by topic.' : 'Keywords used to classify blog posts.'}</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add new {isCategories ? 'category' : 'tag'}</a></div>
            </div>
            <section className="mc-card" aria-label={`${isCategories ? 'Category' : 'Tag'} register`}>
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Name</th><th>Slug</th><th className="text-right">Actions</th></tr></thead>
                        <tbody>{records.length ? records.map((record, index) => (
                            <tr key={record.id}>
                                <td className="mc-idx">{String(index + 1).padStart(2, '0')}</td><td>{record.name}</td><td>{record.slug}</td>
                                <td><div className="mc-acts">
                                    <a href={`${routes.editBase}/${record.id}/edit`} className="mc-btn sm dark"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a>
                                    <form action={`${routes.deleteBase}/${record.id}`} method="post" className="inline" onSubmit={(event) => { if (!window.confirm('Are you sure?')) event.preventDefault(); }}>
                                        <input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="DELETE" />
                                        <button type="submit" className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                                    </form>
                                </div></td>
                            </tr>
                        )) : <tr><td colSpan="4"><div className="mc-empty"><b>Nothing on this chart</b>No {label.toLowerCase()} found. Add the first one to start grouping.</div></td></tr>}</tbody>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
