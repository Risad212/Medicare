import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Status({ status }) {
    return status === 1
        ? <span className="mc-pill p-active"><i />Approved</span>
        : <span className="mc-pill p-pending"><i />Pending</span>;
}

function CommentActions({ comment, routes, csrfToken }) {
    const approve = useForm({});
    const remove = useForm({});

    return (
        <div className="mc-acts">
            {comment.status === 0 && <form onSubmit={(event) => { event.preventDefault(); approve.put(`${routes.approveBase}/${comment.id}`); }}>
                <input type="hidden" name="_token" value={csrfToken} />
                <button type="submit" className="mc-btn sm dark" disabled={approve.processing}><i aria-hidden="true" className="bi bi-check" /> Approve</button>
            </form>}
            <form onSubmit={(event) => { event.preventDefault(); if (window.confirm('Are you sure?')) remove.delete(`${routes.deleteBase}/${comment.id}`); }}>
                <input type="hidden" name="_token" value={csrfToken} />
                <button type="submit" className="mc-btn sm danger-ghost" disabled={remove.processing}><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
            </form>
        </div>
    );
}

export default function CommentsIndex({ comments, routes }) {
    const { csrfToken } = usePage().props;

    return (
        <AdminLayout title="Blog comments" active="comments" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Publishing</p><h1 className="mc-title">Blog <em>Comments</em></h1><p className="mc-sub">Moderate user feedback on published posts.</p></div></div>
            <section className="mc-card" aria-label="Blog comments">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Blog</th><th>Name</th><th>Email</th><th>Comment</th><th>Status</th><th>Date</th><th className="text-right">Actions</th></tr></thead>
                        <tbody>{comments.length ? comments.map((comment, index) => (
                            <tr key={comment.id}>
                                <td className="mc-idx">{String(index + 1).padStart(2, '0')}</td>
                                <td>{comment.blogTitle.length > 30 ? `${comment.blogTitle.slice(0, 30)}…` : comment.blogTitle}</td><td>{comment.name}</td><td>{comment.email}</td>
                                <td>{comment.comment.length > 50 ? `${comment.comment.slice(0, 50)}…` : comment.comment}</td><td><Status status={comment.status} /></td><td className="mc-num">{comment.date}</td>
                                <td><CommentActions comment={comment} routes={routes} csrfToken={csrfToken} /></td>
                            </tr>
                        )) : <tr><td colSpan="8"><div className="mc-empty"><b>Nothing on this chart</b>No comments found. They appear when readers engage with your posts.</div></td></tr>}</tbody>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
