import { router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Components/AdminLayout';

export default function BloodGroupsIndex({ bloodGroups, routes }) {
    const { flash = {} } = usePage().props;

    return (
        <AdminLayout title="Blood groups" active="blood-groups" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Blood Bank</p><h1 className="mc-title">Blood <em>groups</em></h1><p className="mc-sub">Blood types shared by every donation and request.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add blood group</a></div>
            </div>
            {flash.error && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{flash.error}</div>}
            <div className="mc-ecg"><span>Type register</span><span>{bloodGroups.length} groups</span></div>
            <section className="mc-card">
                <div className="overflow-x-auto"><table className="mc-tbl">
                    <thead><tr><th>#</th><th>Group</th><th>Donors</th><th>Donations</th><th>Requests</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>{bloodGroups.length ? bloodGroups.map((group, index) => (
                        <tr key={group.id}>
                            <td className="mc-idx">{String(index + 1).padStart(2, '0')}</td><td><b>Blood type {group.name}</b></td><td>{group.donorsCount}</td><td>{group.donationsCount}</td><td>{group.requestsCount}</td>
                            <td><span className={`mc-pill ${group.status ? 'p-active' : 'p-inactive'}`}>{group.status ? 'Active' : 'Disabled'}</span></td>
                            <td><div className="mc-acts">
                                <a href={`${routes.editBase}/${group.id}/edit`} className="mc-btn sm dark">Edit</a>
                                <button type="button" className="mc-btn sm" onClick={() => router.patch(`${routes.toggleBase}/${group.id}/toggle`)}>{group.status ? 'Disable' : 'Enable'}</button>
                                <button type="button" className="mc-btn sm danger-ghost" onClick={() => { if (window.confirm('Delete this blood group?')) router.delete(`${routes.deleteBase}/${group.id}`); }}>Delete</button>
                            </div></td>
                        </tr>
                    )) : <tr><td colSpan="7"><div className="mc-empty"><b>No blood groups</b>Add the canonical types to get started.</div></td></tr>}</tbody>
                </table></div>
            </section>
        </AdminLayout>
    );
}
