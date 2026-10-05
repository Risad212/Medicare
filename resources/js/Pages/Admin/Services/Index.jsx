import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Status({ status }) {
    return status === 1
        ? <span className="mc-pill p-active"><i />Active</span>
        : <span className="mc-pill p-inactive"><i />Inactive</span>;
}

export default function ServicesIndex({ services, routes, storageUrl }) {
    const { csrfToken } = usePage().props;

    return (
        <AdminLayout title="Services" active="services" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Content</p><h1 className="mc-title">Serv<em>ices</em></h1><p className="mc-sub">Cards shown on the home and services pages.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add service</a><a href={routes.settings} className="mc-btn ghost">Content &amp; SEO</a></div>
            </div>
            <div className="mc-ecg"><span>Live register</span><span>{services.length} records</span></div>
            <section className="mc-card" aria-label="Service register">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Service</th><th>Description</th><th>Order</th><th>Status</th><th className="text-right">Actions</th></tr></thead>
                        <tbody>{services.length ? services.map((service, index) => (
                            <tr key={service.id}>
                                <td className="mc-idx">{String(index + 1).padStart(2, '0')}</td>
                                <td><div className="mc-who">
                                    {service.icon ? <img src={`${storageUrl}/${service.icon.replace(/^\/+/, '')}`} alt={service.title} className="mc-av b bg-white object-contain" /> : <span className="mc-av b"><i aria-hidden="true" className="bi bi-heart-pulse" /></span>}
                                    <span><b>{service.title}</b></span>
                                </div></td>
                                <td>{service.description ? `${service.description.slice(0, 60)}${service.description.length > 60 ? '…' : ''}` : '–'}</td>
                                <td>{service.order ?? 0}</td><td><Status status={service.status} /></td>
                                <td><div className="mc-acts">
                                    <a href={`${routes.editBase}/${service.id}/edit`} className="mc-btn sm dark"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a>
                                    <form action={`${routes.deleteBase}/${service.id}`} method="post" className="m-0" onSubmit={(event) => { if (!window.confirm('Are you sure?')) event.preventDefault(); }}>
                                        <input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="DELETE" />
                                        <button type="submit" className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                                    </form>
                                </div></td>
                            </tr>
                        )) : <tr><td colSpan="6"><div className="mc-empty"><b>Nothing on this chart</b>No services found. Add the first card to populate the site.</div></td></tr>}</tbody>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
