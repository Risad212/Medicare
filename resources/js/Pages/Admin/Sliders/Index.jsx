import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function SlidersIndex({ sliders, routes, storageUrl }) {
    const { csrfToken } = usePage().props;

    return (
        <AdminLayout title="Sliders" active="sliders" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Frontend</p><h1 className="mc-title">All <em>Sliders</em></h1><p className="mc-sub">Homepage hero banners and call-to-action slides.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add slide</a></div></div>
            <section className="mc-card" aria-label="Homepage sliders">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Image</th><th>Title</th><th>Description</th><th>Button text</th><th className="text-right">Actions</th></tr></thead>
                        <tbody>{sliders.length ? sliders.map((slider, index) => (
                            <tr key={slider.id}>
                                <td className="mc-idx">{String(index + 1).padStart(2, '0')}</td>
                                <td>{slider.backgroundImage ? <img src={`${storageUrl}/${slider.backgroundImage.replace(/^\/+/, '')}`} className="h-16 w-28 rounded-lg border border-line object-cover" alt="" /> : <span className="mc-pill p-draft"><i />No image</span>}</td>
                                <td>{slider.title}</td><td>{slider.description ? `${slider.description.slice(0, 60)}${slider.description.length > 60 ? '…' : ''}` : '–'}</td><td>{slider.buttonText || '–'}</td>
                                <td><div className="mc-acts">
                                    <a href={`${routes.editBase}/${slider.id}/edit`} className="mc-btn sm dark"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a>
                                    <form action={`${routes.deleteBase}/${slider.id}`} method="post" className="inline" onSubmit={(event) => { if (!window.confirm('Are you sure?')) event.preventDefault(); }}>
                                        <input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="DELETE" />
                                        <button type="submit" className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                                    </form>
                                </div></td>
                            </tr>
                        )) : <tr><td colSpan="6"><div className="mc-empty"><b>Nothing on this chart</b>No sliders found. Add the first one to populate the homepage hero.</div></td></tr>}</tbody>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
