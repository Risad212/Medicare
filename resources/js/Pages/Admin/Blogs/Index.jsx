import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Status({ status }) {
    return status === 1
        ? <span className="mc-pill p-published"><i />Published</span>
        : <span className="mc-pill p-draft"><i />Draft</span>;
}

export default function BlogsIndex({ blogs, routes, storageUrl }) {
    const { csrfToken } = usePage().props;

    return (
        <AdminLayout title="Blog posts" active="blogs" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Publishing</p><h1 className="mc-title">Bl<em>og</em></h1><p className="mc-sub">Patient education pipeline — drafts become trust.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> New post</a></div>
            </div>
            <div className="mc-ecg"><span>Live register</span><span>{blogs.total} records</span></div>
            <section className="mc-card" aria-label="Blog post register">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Post</th><th>Author</th><th>Category</th><th>Tag</th><th>Status</th><th>Date</th><th className="text-right">Actions</th></tr></thead>
                        <tbody>{blogs.data.length ? blogs.data.map((blog, index) => (
                            <tr key={blog.id}>
                                <td className="mc-idx">{String((blogs.firstItem || 1) + index).padStart(2, '0')}</td>
                                <td><div className="mc-who">
                                    {blog.image ? <img className="mc-av object-cover" src={`${storageUrl}/${blog.image.replace(/^\/+/, '')}`} alt="" /> : <span className="mc-av a"><i aria-hidden="true" className="bi bi-plus-lg" /></span>}
                                    <span><b>{blog.title.length > 50 ? `${blog.title.slice(0, 50)}…` : blog.title}</b><span className="mc-sub2">{blog.excerpt ? `${blog.excerpt.slice(0, 60)}${blog.excerpt.length > 60 ? '…' : ''}` : ''}</span></span>
                                </div></td>
                                <td>{blog.author || '–'}</td><td>{blog.category || '–'}</td><td>{blog.tags || '–'}</td><td><Status status={blog.status} /></td><td className="mc-num">{blog.date}</td>
                                <td><div className="mc-acts">
                                    <a href={`${routes.editBase}/${blog.id}/edit`} className="mc-btn sm dark"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a>
                                    <form action={`${routes.deleteBase}/${blog.id}`} method="post" className="m-0" onSubmit={(event) => { if (!window.confirm('Are you sure?')) event.preventDefault(); }}>
                                        <input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="DELETE" />
                                        <button type="submit" className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                                    </form>
                                </div></td>
                            </tr>
                        )) : <tr><td colSpan="8"><div className="mc-empty"><b>Nothing on this chart</b>No posts yet. Publish the first one to start building trust.</div></td></tr>}</tbody>
                    </table>
                </div>
                <div className="mc-pg"><span>Showing {blogs.firstItem || 0}–{blogs.lastItem || 0} of {blogs.total}</span>
                    <nav aria-label="Blog pages" className="flex items-center gap-1">
                        {blogs.previousPageUrl ? <a className="page-link" href={blogs.previousPageUrl} aria-label="Previous page">‹</a> : <span className="page-link opacity-50" aria-disabled="true">‹</span>}
                        <span className="px-2 text-[11px] text-mut">Page {blogs.currentPage} of {blogs.lastPage}</span>
                        {blogs.nextPageUrl ? <a className="page-link" href={blogs.nextPageUrl} aria-label="Next page">›</a> : <span className="page-link opacity-50" aria-disabled="true">›</span>}
                    </nav>
                </div>
            </section>
        </AdminLayout>
    );
}
